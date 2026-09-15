<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStreamer(): bool
    {
        return $this->role === 'streamer';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Hồ sơ streamer cá nhân nếu vai trò là streamer
     */
    public function streamerProfile()
    {
        return $this->hasOne(StreamerProfile::class, 'user_id');
    }

    /**
     * Danh sách Streamers do Manager này quản lý
     */
    public function managedStreamers()
    {
        return $this->hasMany(StreamerProfile::class, 'manager_id');
    }

    /**
     * Danh sách booking do Client tạo
     */
    public function clientBookings()
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    /**
     * Danh sách booking do Manager duyệt
     */
    public function managedBookings()
    {
        return $this->hasMany(Booking::class, 'manager_id');
    }
}
