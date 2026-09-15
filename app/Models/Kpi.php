<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    use HasFactory;

    protected $table = 'kpis';

    protected $fillable = [
        'streamer_id',
        'month_year',
        'target_hours',
        'target_revenue',
        'target_avg_viewers',
        'achieved_hours',
        'achieved_revenue',
        'status',
    ];

    protected $casts = [
        'target_hours' => 'decimal:2',
        'target_revenue' => 'decimal:2',
        'achieved_hours' => 'decimal:2',
        'achieved_revenue' => 'decimal:2',
        'target_avg_viewers' => 'integer',
    ];

    public function streamer()
    {
        return $this->belongsTo(StreamerProfile::class, 'streamer_id');
    }

    public function getHoursProgressPercentageAttribute(): int
    {
        if ($this->target_hours <= 0) return 0;
        $pct = ($this->achieved_hours / $this->target_hours) * 100;
        return (int) min(100, round($pct));
    }

    public function getRevenueProgressPercentageAttribute(): int
    {
        if ($this->target_revenue <= 0) return 0;
        $pct = ($this->achieved_revenue / $this->target_revenue) * 100;
        return (int) min(100, round($pct));
    }

    public function getFormattedTargetRevenueAttribute(): string
    {
        return number_format($this->target_revenue, 0, ',', '.') . ' đ';
    }

    public function getFormattedAchievedRevenueAttribute(): string
    {
        return number_format($this->achieved_revenue, 0, ',', '.') . ' đ';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'Đang thực hiện',
            'achieved' => 'Đã đạt chỉ tiêu',
            'failed' => 'Chưa đạt',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'bg-blue-100 text-blue-800 border-blue-300',
            'achieved' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'failed' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
