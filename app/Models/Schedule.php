<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedules';

    protected $fillable = [
        'streamer_id',
        'event_type',
        'reference_id',
        'start_time',
        'end_time',
        'title',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function streamer()
    {
        return $this->belongsTo(StreamerProfile::class, 'streamer_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'reference_id');
    }

    public function getEventTypeLabelAttribute(): string
    {
        return match ($this->event_type) {
            'stream' => 'Lịch Live Stream',
            'booking' => 'Lịch Booking Nhãn Hàng',
            'training' => 'Lịch Training / Họp',
            'personal' => 'Lịch Cá Nhân',
            default => $this->event_type,
        };
    }

    public function getEventBadgeClassAttribute(): string
    {
        return match ($this->event_type) {
            'stream' => 'bg-purple-100 text-purple-800 border-purple-300',
            'booking' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'training' => 'bg-blue-100 text-blue-800 border-blue-300',
            'personal' => 'bg-amber-100 text-amber-800 border-amber-300',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
