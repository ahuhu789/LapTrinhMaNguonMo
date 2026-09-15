<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamMetric extends Model
{
    use HasFactory;

    protected $table = 'stream_metrics';

    protected $fillable = [
        'streamer_id',
        'platform',
        'stream_date',
        'duration_hours',
        'avg_viewers',
        'peak_viewers',
        'followers_gained',
        'source_type',
    ];

    protected $casts = [
        'stream_date' => 'date',
        'duration_hours' => 'decimal:2',
        'avg_viewers' => 'integer',
        'peak_viewers' => 'integer',
        'followers_gained' => 'integer',
    ];

    public function streamer()
    {
        return $this->belongsTo(StreamerProfile::class, 'streamer_id');
    }

    public function getPlatformIconAttribute(): string
    {
        return match ($this->platform) {
            'youtube' => 'fab fa-youtube text-red-600',
            'twitch' => 'fab fa-twitch text-purple-600',
            'tiktok' => 'fab fa-tiktok text-slate-900',
            'facebook' => 'fab fa-facebook text-blue-600',
            default => 'fas fa-video text-gray-600',
        };
    }
}
