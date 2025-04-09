<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShowtimeSeat extends Model
{
    use HasFactory;

    protected $table = 'showtime_seats';

    protected $fillable = [
        'showtime_id',
        'seat_id',
        'status',
        'seat_price'
    ];

    public function showtime()
    {
        return $this->belongsTo(Showtime::class);
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }
}
