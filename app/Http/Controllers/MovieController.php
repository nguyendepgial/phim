<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    // Lấy danh sách tất cả phim đang chiếu hoặc sắp chiếu
    public function getMovies()
    {
        // Lấy danh sách phim với thể loại
        $movies = Movie::with('genres')->where('status', '!=', 'ended')->get();

        return response()->json(['movies' => $movies], 200);
    }

    // Lấy chi tiết một phim theo ID
    public function getMovieDetail($id)
    {
        // Lấy phim theo ID với thể loại liên kết
        $movie = Movie::with('genres', 'showtimes')->find($id);

        if (!$movie) {
            return response()->json(['error' => 'Phim không tồn tại'], 404);
        }

        return response()->json(['movie' => $movie], 200);
    }
}
