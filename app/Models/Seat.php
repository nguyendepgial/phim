<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;

    protected $table = 'seats'; // Đảm bảo Laravel lấy đúng bảng

    protected $fillable = [
        'cinema_id',
        'row_number', 
        'column_number', 
        'seat_number', 
        'seat_type'
    ];

    public function cinema()
    {
        return $this->belongsTo(Cinema::class);
    }

    public function bookingDetails()
    {
        return $this->hasMany(BookingDetail::class);
    }
}
