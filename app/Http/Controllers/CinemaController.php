<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cinema;

class CinemaController extends Controller
{
    // Lấy danh sách tất cả rạp chiếu
    public function getCinemas()
    {
        $cinemas = Cinema::all();
        return response()->json(['cinemas' => $cinemas], 200);
    }

    // Lấy chi tiết một rạp theo ID
    public function getCinemaDetail($id)
    {
        $cinema = Cinema::with(['showtimes', 'seats' => function ($query) {
            $query->orderBy('row_number')->orderBy('column_number'); // Sắp xếp ghế theo hàng & cột
        }])->find($id);
    
        if (!$cinema) {
            return response()->json(['error' => 'Rạp không tồn tại'], 404);
        }
    
        return response()->json(['cinema' => $cinema], 200);
    }
    
}
