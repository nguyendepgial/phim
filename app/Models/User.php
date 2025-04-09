<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'users'; // Đảm bảo Laravel lấy đúng bảng

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'google_id',
        'phone',
        'avatar',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Định nghĩa quan hệ với bảng bookings (Người dùng có thể đặt nhiều vé)
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Định nghĩa quan hệ với bảng payments (Người dùng có thể thực hiện nhiều thanh toán)
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Lấy định danh của user cho JWT (ID của user)
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Trả về các claims bổ sung cho JWT
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
