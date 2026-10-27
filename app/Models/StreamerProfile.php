<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
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
        // Cột tổng hợp sinh bởi scope withViewerStats() (withAvg/withMax)
        'avg_viewers_calc' => 'float',
        'peak_viewers_calc' => 'integer',
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
     * Nạp sẵn chỉ số người xem bằng subquery SQL (tránh N+1):
     *  - avg_viewers_calc  = AVG(stream_metrics.avg_viewers)
     *  - peak_viewers_calc = MAX(stream_metrics.peak_viewers)
     * Giá trị là NULL nếu streamer chưa có metrics.
     */
    public function scopeWithViewerStats(Builder $query): Builder
    {
        return $query
            ->withAvg('metrics as avg_viewers_calc', 'avg_viewers')
            ->withMax('metrics as peak_viewers_calc', 'peak_viewers');
    }

    /**
     * Trung bình số người xem. Ưu tiên giá trị đã nạp bởi withViewerStats(),
     * sau đó tới quan hệ metrics đã eager-load; chỉ query khi không có sẵn.
     */
    public function getAvgViewersAttribute(): int
    {
        if (array_key_exists('avg_viewers_calc', $this->attributes)) {
            $avg = $this->avg_viewers_calc;
        } elseif ($this->relationLoaded('metrics')) {
            $avg = $this->metrics->avg('avg_viewers');
        } else {
            $avg = $this->metrics()->avg('avg_viewers');
        }

        return (int) round($avg ?? 0);
    }

    /**
     * Kỷ lục peak viewer (cùng thứ tự ưu tiên như avg_viewers).
     */
    public function getPeakViewersAttribute(): int
    {
        if (array_key_exists('peak_viewers_calc', $this->attributes)) {
            $peak = $this->peak_viewers_calc;
        } elseif ($this->relationLoaded('metrics')) {
            $peak = $this->metrics->max('peak_viewers');
        } else {
            $peak = $this->metrics()->max('peak_viewers');
        }

        return (int) ($peak ?? 0);
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
        return number_format((float) $this->rate_per_hour, 0, ',', '.') . ' đ/giờ';
    }
}
