<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $table = 'movies'; // Đảm bảo Laravel lấy đúng bảng

    protected $fillable = [
        'title',
        'description',
        'duration',
        'release_date',
        'poster_url',
        'status',
    ];

    // Mối quan hệ với bảng showtimes (suất chiếu)
    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }

    // Mối quan hệ nhiều-nhiều với bảng genres (thể loại)
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'movie_genre', 'movie_id', 'genre_id');
    }
}
