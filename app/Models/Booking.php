<?php

namespace App\Models;

use App\Observers\BookingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(BookingObserver::class)]
class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    /** Trạng thái booking còn hiệu lực (chiếm lịch của talent & của client) */
    public const ACTIVE_STATUSES = ['pending', 'reviewing', 'approved'];

    /** Trạng thái cho phép phê duyệt */
    public const APPROVABLE_STATUSES = ['pending', 'reviewing'];

    /** Trạng thái cho phép Manager từ chối thủ công */
    public const REJECTABLE_STATUSES = ['pending', 'reviewing'];

    /** Trạng thái Client được xóa khỏi danh sách của mình (đơn đã kết thúc, không ảnh hưởng doanh thu/KPI) */
    public const DELETABLE_STATUSES = ['rejected', 'cancelled'];

    protected $fillable = [
        'client_id',
        'streamer_id',
        'manager_id',
        'start_time',
        'end_time',
        'job_description',
        'budget',
        'commission_rate',
        'status',
        'reject_reason',
        'cancel_reason',
        'cancelled_by',
        'cancelled_at',
        'completed_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'budget' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function streamer()
    {
        return $this->belongsTo(StreamerProfile::class, 'streamer_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function schedule()
    {
        return $this->hasOne(Schedule::class, 'reference_id');
    }

    public function isApprovable(): bool
    {
        return in_array($this->status, self::APPROVABLE_STATUSES, true);
    }

    /**
     * Alias đọc của cột reject_reason (lý do từ chối thủ công hoặc tự động).
     */
    public function getRejectionReasonAttribute(): ?string
    {
        return $this->reject_reason;
    }

    public function isRejectable(): bool
    {
        return in_array($this->status, self::REJECTABLE_STATUSES, true);
    }

    // ---- Vòng đời (dùng cho service khi đã khóa hàng, và cho view để ẩn/hiện nút) ----

    /** pending -> reviewing (Manager/Admin) */
    public function isReviewable(): bool
    {
        return $this->status === 'pending';
    }

    /** Đã tới giờ bắt đầu chưa */
    public function hasStarted(): bool
    {
        return $this->start_time !== null && !now()->lt($this->start_time);
    }

    /** Đã kết thúc chưa (end_time đã qua) */
    public function hasEnded(): bool
    {
        return $this->end_time !== null && !now()->lt($this->end_time);
    }

    /** approved -> completed: chỉ khi buổi live đã kết thúc */
    public function isCompletable(): bool
    {
        return $this->status === 'approved' && $this->hasEnded();
    }

    /** Client hủy: pending/reviewing, hoặc approved nhưng chưa tới giờ bắt đầu */
    public function isCancellableByClient(): bool
    {
        return in_array($this->status, self::APPROVABLE_STATUSES, true)
            || ($this->status === 'approved' && !$this->hasStarted());
    }

    /** Manager/Admin hủy: chỉ booking approved chưa diễn ra (pending/reviewing thì dùng "Từ chối") */
    public function isCancellableByManager(): bool
    {
        return $this->status === 'approved' && !$this->hasStarted();
    }

    /** Client xóa đơn của mình: chỉ đơn đã bị từ chối hoặc đã hủy */
    public function isDeletable(): bool
    {
        return in_array($this->status, self::DELETABLE_STATUSES, true);
    }

    public function getDurationHoursAttribute(): float
    {
        if ($this->start_time && $this->end_time) {
            return round($this->start_time->diffInMinutes($this->end_time) / 60, 2);
        }
        return 0;
    }

    public function getAgencyCommissionAttribute(): float
    {
        return round((float) $this->budget * ((float) $this->commission_rate / 100), 2);
    }

    public function getStreamerNetIncomeAttribute(): float
    {
        return round((float) $this->budget - $this->agency_commission, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ duyệt',
            'reviewing' => 'Đang xem xét',
            'approved' => 'Đã duyệt & Khóa lịch',
            'rejected' => 'Từ chối',
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
            'reviewing' => 'bg-blue-100 text-blue-800 border-blue-300',
            'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'rejected' => 'bg-rose-100 text-rose-800 border-rose-300',
            'completed' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            'cancelled' => 'bg-slate-100 text-slate-700 border-slate-300',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
