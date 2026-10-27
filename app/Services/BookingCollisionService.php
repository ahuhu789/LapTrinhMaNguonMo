<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\StreamerProfile;
use App\Models\User;
use Carbon\CarbonInterface;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Quy tắc lịch (Collision Detection), khoảng thời gian dạng nửa mở [start, end):
 *   trùng  <=>  start < other_end  AND  end > other_start   (chạm mép KHÔNG tính là trùng)
 *
 *  1. Cùng talent: không được có 2 lịch chồng nhau - xét các lịch đã khóa (schedules)
 *     và các booking còn hiệu lực (pending / reviewing / approved) của talent.
 *  2. Cùng client: không được có 2 booking còn hiệu lực chồng nhau (dù cùng hay khác talent).
 *  3. Booking mới vi phạm (1) hoặc (2) vẫn được lưu nhưng ở trạng thái 'rejected' kèm lý do.
 *     Khi duyệt một booking, các booking pending/reviewing chồng giờ của cùng talent bị từ chối tự động.
 *
 * Lý do từ chối không bao giờ chứa thông tin booking của client khác (chỉ khung giờ bận của talent).
 */
class BookingCollisionService
{
    public const AUTO_REJECT_PREFIX = 'Tự động từ chối: ';

    /**
     * Kiểm tra xung đột cho một khung giờ (dùng cho AJAX check-collision, cảnh báo, lịch cá nhân streamer).
     *
     * @param int|null $ignoreBookingId Booking bỏ qua (chính booking đang xét)
     * @param int|null $clientId        Nếu truyền: áp dụng thêm quy tắc 2 cho client này
     * @return array{has_conflict: bool, message: string}
     */
    public function checkCollision(int $streamerId, $startTime, $endTime, ?int $ignoreBookingId = null, ?int $clientId = null): array
    {
        $start = $this->toAppTime($startTime);
        $end = $this->toAppTime($endTime);

        $reason = $this->conflictReason($streamerId, $clientId, $start, $end, $ignoreBookingId, Booking::ACTIVE_STATUSES);

        return [
            'has_conflict' => $reason !== null,
            'message' => $reason ?? 'Khung giờ hoàn toàn khả dụng (trống lịch).',
        ];
    }

    /**
     * Xung đột sẽ chặn việc PHÊ DUYỆT booking: lịch đã khóa của talent, booking đã duyệt của talent
     * và booking đã duyệt khác của chính client. (Booking pending/reviewing chồng giờ không chặn -
     * chúng sẽ bị từ chối tự động khi duyệt.)
     */
    public function checkApprovalConflict(Booking $booking): array
    {
        $reason = $this->conflictReason(
            $booking->streamer_id,
            $booking->client_id,
            $booking->start_time,
            $booking->end_time,
            $booking->id,
            ['approved']
        );

        return [
            'has_conflict' => $reason !== null,
            'message' => $reason ?? 'Khung giờ hoàn toàn khả dụng (trống lịch).',
        ];
    }

    /**
     * Client tạo booking mới. Nếu vi phạm quy tắc 1/2 booking vẫn được lưu với status 'rejected' + lý do.
     */
    public function createBooking(StreamerProfile $streamer, User $client, array $data): Booking
    {
        return DB::transaction(function () use ($streamer, $client, $data) {
            $this->lockParties($client->id, $streamer->id);

            $start = $this->toAppTime($data['start_time']);
            $end = $this->toAppTime($data['end_time']);

            $reason = $this->conflictReason($streamer->id, $client->id, $start, $end, null, Booking::ACTIVE_STATUSES);

            return Booking::create([
                'client_id' => $client->id,
                'streamer_id' => $streamer->id,
                'manager_id' => $streamer->manager_id,
                'start_time' => $start,
                'end_time' => $end,
                'job_description' => $data['job_description'],
                'budget' => $data['budget'],
                'commission_rate' => config('mcn.commission_rate'),
                'status' => $reason ? 'rejected' : 'pending',
                'reject_reason' => $reason ? self::AUTO_REJECT_PREFIX . $reason : null,
            ]);
        });
    }

    /**
     * Manager/Admin phê duyệt booking và khóa lịch (toàn bộ trong 1 transaction có khóa hàng).
     *
     * @return array{success: bool, message: string, auto_rejected?: bool, cascaded?: int}
     */
    public function approveAndLockSchedule(Booking $booking, int $managerId): array
    {
        try {
            $result = DB::transaction(function () use ($booking, $managerId) {
                // 1. Khóa client + streamer (cùng thứ tự với createBooking để tránh deadlock), rồi khóa booking
                $this->lockParties($booking->client_id, $booking->streamer_id);
                $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

                if (!$locked->isApprovable()) {
                    return [
                        'success' => false,
                        'message' => 'Booking này đã được xử lý từ trước (' . $locked->status_label . ').',
                    ];
                }

                // 2. Race: khung giờ đã bị chiếm bởi lịch đã khóa/duyệt -> tự động từ chối booking này
                $conflict = $this->checkApprovalConflict($locked);
                if ($conflict['has_conflict']) {
                    $locked->update([
                        'status' => 'rejected',
                        'manager_id' => $managerId,
                        'reject_reason' => self::AUTO_REJECT_PREFIX . $conflict['message'],
                    ]);

                    return [
                        'success' => false,
                        'auto_rejected' => true,
                        'message' => 'Không thể phê duyệt! ' . $conflict['message'] . ' Booking đã được tự động từ chối.',
                    ];
                }

                // 3. Duyệt & khóa lịch
                $locked->update([
                    'status' => 'approved',
                    'manager_id' => $managerId,
                ]);

                Schedule::create([
                    'streamer_id'  => $locked->streamer_id,
                    'event_type'   => 'booking',
                    'reference_id' => $locked->id,
                    'start_time'   => $locked->start_time,
                    'end_time'     => $locked->end_time,
                    'title'        => 'Booking: ' . mb_substr($locked->job_description, 0, 80),
                ]);

                // 4. Tự động từ chối các booking pending/reviewing chồng giờ
                $cascaded = $this->cascadeReject($locked);

                $message = 'Đã phê duyệt booking và tự động khóa lịch trình thành công.';
                if ($cascaded > 0) {
                    $message .= " Đã tự động từ chối $cascaded booking khác bị trùng lịch.";
                }

                return ['success' => true, 'message' => $message, 'cascaded' => $cascaded];
            });

            $booking->refresh();

            return $result;
        } catch (Exception $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Lỗi hệ thống khi phê duyệt và khóa lịch. Vui lòng thử lại sau.',
            ];
        }
    }

    /**
     * Manager từ chối thủ công (booking 'pending' hoặc 'reviewing').
     */
    public function rejectBooking(Booking $booking, int $managerId, string $rejectReason): array
    {
        try {
            $result = DB::transaction(function () use ($booking, $managerId, $rejectReason) {
                // Đọc lại booking dưới khóa để không từ chối nhầm booking vừa được duyệt song song
                $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

                if (!$locked->isRejectable()) {
                    return [
                        'success' => false,
                        'message' => 'Không thể từ chối! Chỉ booking đang chờ duyệt hoặc đang xem xét mới được từ chối (trạng thái hiện tại: ' . $locked->status_label . ').',
                    ];
                }

                $locked->update([
                    'status' => 'rejected',
                    'manager_id' => $managerId,
                    'reject_reason' => $rejectReason,
                ]);

                // Phòng hờ: giải phóng lịch nếu có bản ghi schedule trỏ tới booking này
                Schedule::where('reference_id', $locked->id)->delete();

                return [
                    'success' => true,
                    'message' => 'Đã từ chối booking và thông báo lý do đến khách hàng.',
                ];
            });

            $booking->refresh();

            return $result;
        } catch (Exception $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'Lỗi hệ thống khi từ chối booking. Vui lòng thử lại sau.',
            ];
        }
    }

    // ------------------------------------------------------------------
    // Nội bộ
    // ------------------------------------------------------------------

    /**
     * Trả về lý do xung đột (tiếng Việt) hoặc null nếu khung giờ hợp lệ.
     * Ưu tiên báo trùng với booking của chính client (có số booking), sau đó tới lịch bận của talent.
     */
    protected function conflictReason(
        int $streamerId,
        ?int $clientId,
        CarbonInterface $start,
        CarbonInterface $end,
        ?int $ignoreBookingId,
        array $bookingStatuses
    ): ?string {
        if ($end->lessThanOrEqualTo($start)) {
            return 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.';
        }

        // Quy tắc 2: booking còn hiệu lực của chính client (mọi talent)
        if ($clientId !== null) {
            $own = $this->overlapping(Booking::query(), $start, $end)
                ->where('client_id', $clientId)
                ->whereIn('status', $bookingStatuses)
                ->when($ignoreBookingId, fn ($q) => $q->whereKeyNot($ignoreBookingId))
                ->orderBy('start_time')
                ->first();

            if ($own) {
                return sprintf('Trùng với booking #%d của bạn từ %s.', $own->id, $this->range($own->start_time, $own->end_time, ' đến '));
            }
        }

        // Quy tắc 1: lịch đã khóa + booking còn hiệu lực của talent (không lộ thông tin booking khác)
        $schedule = $this->overlapping(Schedule::query(), $start, $end)
            ->where('streamer_id', $streamerId)
            ->when($ignoreBookingId, fn ($q) => $q->where(fn ($q) => $q
                ->whereNull('reference_id')
                ->orWhere('reference_id', '!=', $ignoreBookingId)))
            ->orderBy('start_time')
            ->first();

        $talentBookings = fn (array $statuses) => $this->overlapping(Booking::query(), $start, $end)
            ->where('streamer_id', $streamerId)
            ->whereIn('status', $statuses)
            ->when($ignoreBookingId, fn ($q) => $q->whereKeyNot($ignoreBookingId))
            ->orderBy('start_time')
            ->first();

        // 1a. Lịch bận chắc chắn: lịch đã khóa hoặc booking đã duyệt
        $busy = collect([
            $schedule,
            in_array('approved', $bookingStatuses, true) ? $talentBookings(['approved']) : null,
        ])->filter()->sortBy(fn ($m) => $m->start_time->getTimestamp())->first();

        if ($busy) {
            return sprintf('Trùng với lịch bận của talent từ %s.', $this->range($busy->start_time, $busy->end_time, ' đến '));
        }

        // 1b. Booking khác của talent đang chờ duyệt / đang xem xét
        $waitingStatuses = array_values(array_intersect($bookingStatuses, Booking::APPROVABLE_STATUSES));
        $waiting = $waitingStatuses ? $talentBookings($waitingStatuses) : null;

        if ($waiting) {
            return sprintf('Trùng với một booking khác đang chờ duyệt của talent từ %s.', $this->range($waiting->start_time, $waiting->end_time, ' đến '));
        }

        return null;
    }

    /**
     * Từ chối tự động các booking pending/reviewing chồng giờ với booking vừa duyệt:
     * cùng talent (quy tắc 1) và cùng client (quy tắc 2).
     */
    protected function cascadeReject(Booking $approved): int
    {
        $talentIds = $this->overlapping(Booking::query(), $approved->start_time, $approved->end_time)
            ->where('streamer_id', $approved->streamer_id)
            ->whereIn('status', Booking::APPROVABLE_STATUSES)
            ->whereKeyNot($approved->id)
            ->lockForUpdate()
            ->pluck('id');

        $count = $talentIds->isEmpty() ? 0 : Booking::whereIn('id', $talentIds)->update([
            'status' => 'rejected',
            'reject_reason' => self::AUTO_REJECT_PREFIX . sprintf(
                'Xung đột với lịch đã được duyệt (%s)',
                $this->range($approved->start_time, $approved->end_time, ' – ')
            ),
            'updated_at' => now(),
        ]);

        $count += $this->overlapping(Booking::query(), $approved->start_time, $approved->end_time)
            ->where('client_id', $approved->client_id)
            ->whereIn('status', Booking::APPROVABLE_STATUSES)
            ->whereKeyNot($approved->id)
            ->update([
                'status' => 'rejected',
                'reject_reason' => self::AUTO_REJECT_PREFIX . sprintf('Xung đột với booking #%d của bạn đã được duyệt.', $approved->id),
                'updated_at' => now(),
            ]);

        return $count;
    }

    /**
     * Điều kiện chồng giờ nửa mở. Giá trị Carbon được bind theo định dạng 'Y-m-d H:i:s'
     * (cùng định dạng Eloquent lưu) nên so sánh chuỗi trên SQLite vẫn đúng.
     */
    protected function overlapping($query, CarbonInterface $start, CarbonInterface $end)
    {
        return $query->where('start_time', '<', $end)->where('end_time', '>', $start);
    }

    /**
     * Khóa hàng client & streamer theo thứ tự cố định để tuần tự hóa các thao tác đặt/duyệt lịch.
     */
    protected function lockParties(int $clientId, int $streamerId): void
    {
        User::whereKey($clientId)->lockForUpdate()->first();
        StreamerProfile::whereKey($streamerId)->lockForUpdate()->first();
    }

    /**
     * Chuẩn hóa về múi giờ ứng dụng (Asia/Ho_Chi_Minh). Chuỗi datetime-local ("2026-10-29T14:00")
     * được hiểu là giờ địa phương; chuỗi có offset/Z được quy đổi về giờ địa phương.
     */
    protected function toAppTime($value): Carbon
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'));
    }

    protected function range(CarbonInterface $from, CarbonInterface $to, string $separator): string
    {
        return $this->fmt($from) . $separator . $this->fmt($to);
    }

    protected function fmt(CarbonInterface $time): string
    {
        return $time->format('H:i d/m/Y');
    }
}
