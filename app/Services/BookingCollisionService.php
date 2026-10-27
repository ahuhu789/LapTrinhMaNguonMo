<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\StreamerProfile;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class BookingCollisionService
{
    /**
     * Kiểm tra xem một streamer có bị trùng lịch trong khoảng thời gian xác định hay không.
     * Thuật toán kiểm tra xung đột thời gian (Collision Detection):
     * (new_start < existing_end) AND (new_end > existing_start)
     *
     * @param int $streamerId
     * @param string|Carbon $startTime
     * @param string|Carbon $endTime
     * @param int|null $ignoreBookingId ID booking bỏ qua (khi cập nhật hoặc xét chính nó)
     * @return array ['has_conflict' => bool, 'conflicting_schedule' => Schedule|null]
     */
    public function checkCollision(int $streamerId, $startTime, $endTime, ?int $ignoreBookingId = null): array
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        if ($end->lessThanOrEqualTo($start)) {
            return [
                'has_conflict' => true,
                'message' => 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.',
                'conflicting_schedule' => null,
            ];
        }

        $query = Schedule::where('streamer_id', $streamerId)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);

        if ($ignoreBookingId) {
            $query->where(function ($q) use ($ignoreBookingId) {
                $q->whereNull('reference_id')
                  ->orWhere('reference_id', '!=', $ignoreBookingId);
            });
        }

        $conflict = $query->first();

        if ($conflict) {
            return [
                'has_conflict' => true,
                'message' => sprintf(
                    'Streamer đã có lịch "%s" từ %s đến %s.',
                    $conflict->title,
                    $conflict->start_time->format('H:i d/m/Y'),
                    $conflict->end_time->format('H:i d/m/Y')
                ),
                'conflicting_schedule' => $conflict,
            ];
        }

        return [
            'has_conflict' => false,
            'message' => 'Khung giờ hoàn toàn khả dụng (trống lịch).',
            'conflicting_schedule' => null,
        ];
    }

    /**
     * Manager phê duyệt booking và tự động khóa lịch trong Database Transaction
     *
     * @param Booking $booking
     * @param int $managerId
     * @return array ['success' => bool, 'message' => string]
     */
    public function approveAndLockSchedule(Booking $booking, int $managerId): array
    {
        try {
            $result = DB::transaction(function () use ($booking, $managerId) {
                // 1. Khóa hàng streamer để tuần tự hóa mọi thao tác duyệt lịch của cùng streamer
                //    (chống race condition hai Manager duyệt 2 booking trùng giờ cùng lúc)
                StreamerProfile::whereKey($booking->streamer_id)->lockForUpdate()->first();

                // 2. Đọc lại booking dưới khóa để có trạng thái mới nhất
                $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

                if (!in_array($locked->status, ['pending', 'reviewing'])) {
                    return [
                        'success' => false,
                        'message' => 'Booking này đã được xử lý từ trước (' . $locked->status_label . ').',
                    ];
                }

                // 3. Kiểm tra xung đột thời gian bên trong transaction
                $collisionCheck = $this->checkCollision(
                    $locked->streamer_id,
                    $locked->start_time,
                    $locked->end_time,
                    $locked->id
                );

                if ($collisionCheck['has_conflict']) {
                    return [
                        'success' => false,
                        'message' => 'Không thể phê duyệt! ' . $collisionCheck['message'],
                    ];
                }

                // 4. Cập nhật trạng thái & khóa lịch
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

                return [
                    'success' => true,
                    'message' => 'Đã phê duyệt booking và tự động khóa lịch trình thành công.',
                ];
            });

            $booking->refresh();

            return $result;
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống khi khóa lịch: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Manager từ chối booking kèm lý do và giải phóng lịch (nếu có)
     *
     * @param Booking $booking
     * @param int $managerId
     * @param string $rejectReason
     * @return array ['success' => bool, 'message' => string]
     */
    public function rejectBooking(Booking $booking, int $managerId, string $rejectReason): array
    {
        try {
            DB::transaction(function () use ($booking, $managerId, $rejectReason) {
                $booking->update([
                    'status' => 'rejected',
                    'manager_id' => $managerId,
                    'reject_reason' => $rejectReason,
                ]);

                // Xóa lịch schedule tương ứng nếu đã tồn tại
                Schedule::where('reference_id', $booking->id)->delete();
            });

            return [
                'success' => true,
                'message' => 'Đã từ chối booking và thông báo lý do đến khách hàng.',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi từ chối booking: ' . $e->getMessage(),
            ];
        }
    }
}
