<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Showtime extends Model
{
    use HasFactory;

    protected $table = 'showtimes'; // Đảm bảo Laravel lấy đúng bảng

    protected $fillable = [
        'movie_id',
        'cinema_id',
        'show_date',
        'show_time',
        'ticket_price',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function cinema()
    {
        return $this->belongsTo(Cinema::class);
    }
}
