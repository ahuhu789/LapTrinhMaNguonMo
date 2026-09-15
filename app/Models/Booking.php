<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

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
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'budget' => 'decimal:2',
        'commission_rate' => 'decimal:2',
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

    public function schedule()
    {
        return $this->hasOne(Schedule::class, 'reference_id');
    }

    public function getDurationHoursAttribute(): float
    {
        if ($this->start_time && $this->end_time) {
            return round($this->start_time->diffInMinutes($this->end_time) / 60, 2);
        }
        return 0;
    }

    public function getFormattedBudgetAttribute(): string
    {
        return number_format($this->budget, 0, ',', '.') . ' đ';
    }

    public function getAgencyCommissionAttribute(): float
    {
        return round($this->budget * ($this->commission_rate / 100), 2);
    }

    public function getStreamerNetIncomeAttribute(): float
    {
        return $this->budget - $this->agency_commission;
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
