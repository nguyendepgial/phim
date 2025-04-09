<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings'; // Đảm bảo tên bảng đúng

    protected $fillable = [
        'user_id',
        'showtime_id',
        'total_price',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function showtime()
    {
        return $this->belongsTo(Showtime::class);
    }

    public function details()
    {
        return $this->hasMany(BookingDetail::class);
    }

    public function extras()
    {
        return $this->hasMany(BookingExtra::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    public function bookingDetails()
    {
        return $this->hasMany(BookingDetail::class, 'booking_id', 'id');
    }
}
