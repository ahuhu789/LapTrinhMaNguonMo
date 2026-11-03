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

    /**
     * % hoàn thành THỰC (không chặn trên, có thể > 100). Làm tròn XUỐNG để không bao giờ hiển thị
     * "100%" khi thực tế mới 99,x% (status chỉ 'achieved' khi >= 100%). Chỉ tiêu <= 0 coi như đạt (100).
     */
    public function getHoursProgressRawAttribute(): int
    {
        return self::progress($this->achieved_hours, $this->target_hours);
    }

    public function getRevenueProgressRawAttribute(): int
    {
        return self::progress($this->achieved_revenue, $this->target_revenue);
    }

    /** % dùng cho độ rộng thanh tiến độ: như *_progress_raw nhưng chặn trong [0, 100] (tương thích view cũ). */
    public function getHoursProgressPercentageAttribute(): int
    {
        return min(100, $this->hours_progress_raw);
    }

    public function getRevenueProgressPercentageAttribute(): int
    {
        return min(100, $this->revenue_progress_raw);
    }

    private static function progress($actual, $target): int
    {
        $target = (float) $target;

        if ($target <= 0) {
            return 100;
        }

        // +1e-9: tránh 99.99999 do sai số float khi actual == target
        return max(0, (int) floor(((float) $actual / $target) * 100 + 1e-9));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'Đang thực hiện',
            'achieved' => 'Hoàn thành',
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
