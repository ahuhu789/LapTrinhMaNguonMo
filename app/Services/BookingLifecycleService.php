<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Các bước chuyển trạng thái booking sau khi tạo (ngoài duyệt / từ chối ở BookingCollisionService):
 *
 *   pending  --review-->   reviewing                 (Manager/Admin)
 *   approved --complete--> completed                 (Manager/Admin, khi end_time đã qua)
 *   pending|reviewing|approved(chưa bắt đầu) --cancel--> cancelled   (Client chủ đơn)
 *   approved(chưa bắt đầu)                   --cancel--> cancelled   (Manager/Admin, bắt buộc lý do)
 *
 * Mỗi bước đọc lại booking dưới lockForUpdate trong transaction để không ghi đè thao tác song song.
 * KPI được BookingObserver tính lại tự động khi booking vào/ra trạng thái 'completed'.
 *
 * @phpstan-type Result array{success: bool, message: string}
 */
class BookingLifecycleService
{
    /** @return Result */
    public function review(Booking $booking, User $actor): array
    {
        return $this->transition($booking, function (Booking $locked) use ($actor) {
            if (!$locked->isReviewable()) {
                return $this->fail('Chỉ booking đang chờ duyệt mới chuyển sang "Đang xem xét" được (trạng thái hiện tại: ' . $locked->status_label . ').');
            }

            $locked->update(['status' => 'reviewing', 'manager_id' => $actor->id]);

            return $this->ok('Đã chuyển booking sang trạng thái "Đang xem xét".');
        });
    }

    /** @return Result */
    public function complete(Booking $booking, User $actor): array
    {
        return $this->transition($booking, function (Booking $locked) use ($actor) {
            if ($locked->status !== 'approved') {
                return $this->fail('Chỉ booking đã duyệt mới được đánh dấu hoàn thành (trạng thái hiện tại: ' . $locked->status_label . ').');
            }

            if (!$locked->hasEnded()) {
                return $this->fail('Chưa thể đánh dấu hoàn thành: buổi live kết thúc lúc '
                    . $locked->end_time->format('H:i d/m/Y') . '. Vui lòng xác nhận sau thời điểm này.');
            }

            // Observer sẽ tính lại KPI của talent trong tháng của booking
            $locked->update([
                'status' => 'completed',
                'manager_id' => $locked->manager_id ?? $actor->id,
                'completed_at' => now(),
            ]);

            return $this->ok('Đã xác nhận booking hoàn thành. Doanh thu và KPI của talent đã được cập nhật.');
        });
    }

    /** @return Result */
    public function cancel(Booking $booking, User $actor, ?string $reason): array
    {
        return $this->transition($booking, function (Booking $locked) use ($actor, $reason) {
            if ($actor->isClient()) {
                if (!$locked->isCancellableByClient()) {
                    return $this->fail($locked->status === 'approved'
                        ? 'Không thể hủy: buổi live đã bắt đầu hoặc đã diễn ra.'
                        : 'Không thể hủy booking ở trạng thái "' . $locked->status_label . '".');
                }
            } elseif (!$locked->isCancellableByManager()) {
                return $this->fail(match (true) {
                    $locked->isApprovable() => 'Booking chưa được duyệt: vui lòng dùng chức năng "Từ chối" kèm lý do.',
                    $locked->status === 'approved' => 'Không thể hủy: buổi live đã bắt đầu hoặc đã diễn ra.',
                    default => 'Không thể hủy booking ở trạng thái "' . $locked->status_label . '".',
                });
            }

            $locked->update([
                'status' => 'cancelled',
                'cancel_reason' => $reason,
                'cancelled_by' => $actor->id,
                'cancelled_at' => now(),
            ]);

            // Giải phóng khung giờ đã khóa trên lịch của talent
            Schedule::where('reference_id', $locked->id)->delete();

            return $this->ok('Đã hủy booking. Khung giờ trên lịch của talent đã được giải phóng.');
        });
    }

    /**
     * @param callable(Booking): array $callback
     * @return Result
     */
    private function transition(Booking $booking, callable $callback): array
    {
        $result = DB::transaction(function () use ($booking, $callback) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            return $callback($locked);
        });

        $booking->refresh();

        return $result;
    }

    private function ok(string $message): array
    {
        return ['success' => true, 'message' => $message];
    }

    private function fail(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}
