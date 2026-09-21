<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StreamerProfile extends Model
{
    use HasFactory;

    protected $table = 'streamer_profiles';

    protected $fillable = [
        'user_id',
        'manager_id',
        'stage_name',
        'category',
        'bio',
        'rate_per_hour',
        'avatar_url',
        'banner_url',
        'status',
    ];

    protected $casts = [
        'rate_per_hour' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'streamer_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'streamer_id');
    }

    public function metrics()
    {
        return $this->hasMany(StreamMetric::class, 'streamer_id');
    }

    public function kpis()
    {
        return $this->hasMany(Kpi::class, 'streamer_id');
    }

    /**
     * KPI của tháng hiện tại
     */
    public function currentKpi()
    {
        $currentMonth = Carbon::now()->format('Y-m');
        return $this->hasOne(Kpi::class, 'streamer_id')->where('month_year', $currentMonth);
    }

    /**
     * Tính trung bình số người xem gần đây từ bảng stream_metrics
     */
    public function getAvgViewersAttribute(): int
    {
        $avg = $this->metrics()->avg('avg_viewers');
        return (int) round($avg ?? 0);
    }

    /**
     * Tính kỷ lục peak viewer
     */
    public function getPeakViewersAttribute(): int
    {
        return (int) ($this->metrics()->max('peak_viewers') ?? 0);
    }

    /**
     * Tổng số giờ stream trong tháng hiện tại
     */
    public function getMonthlyStreamHoursAttribute(): float
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        return (float) ($this->metrics()
            ->whereBetween('stream_date', [$startOfMonth, $endOfMonth])
            ->sum('duration_hours') ?? 0);
    }

    /**
     * Định dạng giá booking tiền Việt (VNĐ)
     */
    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate_per_hour, 0, ',', '.') . ' đ/giờ';
    }
}
