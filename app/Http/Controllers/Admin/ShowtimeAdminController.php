<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Showtime;
use Illuminate\Http\Request;

class ShowtimeAdminController extends Controller
{
    // Hiển thị danh sách showtimes
    public function index()
    {
        $showtimes = \App\Models\Showtime::orderBy('show_date', 'asc')->paginate(20); // 10 dòng / trang
        return response()->json($showtimes);
    }

    // Thêm showtime mới
    public function store(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|integer|exists:movies,id',
            'cinema_id' => 'required|integer|exists:cinemas,id',
            'show_date' => 'required|date',
            'show_time' => 'required|string',
            'ticket_price' => 'required|numeric',
        ]);

        $showtime = Showtime::create([
            'movie_id' => $request->movie_id,
            'cinema_id' => $request->cinema_id,
            'show_date' => $request->show_date,
            'show_time' => $request->show_time,
            'ticket_price' => $request->ticket_price,
        ]);

        return response()->json($showtime, 201);
    }

    // Cập nhật showtime
    public function update(Request $request, $id)
    {
        $request->validate([
            'movie_id' => 'required|integer|exists:movies,id',
            'cinema_id' => 'required|integer|exists:cinemas,id',
            'show_date' => 'required|date',
            'show_time' => 'required|string',
            'ticket_price' => 'required|numeric',
        ]);

        $showtime = Showtime::find($id);

        if (!$showtime) {
            return response()->json(['message' => 'Showtime not found'], 404);
        }

        $showtime->update([
            'movie_id' => $request->movie_id,
            'cinema_id' => $request->cinema_id,
            'show_date' => $request->show_date,
            'show_time' => $request->show_time,
            'ticket_price' => $request->ticket_price,
        ]);

        return response()->json($showtime);
    }
    public function show($id)
    {
        $showtime = Showtime::find($id);
        if (!$showtime) {
            return response()->json(['message' => 'Showtime not found'], 404);
        }
        return response()->json($showtime);
    }


    // Xóa showtime
    public function destroy($id)
    {
        $showtime = Showtime::find($id);

        if (!$showtime) {
            return response()->json(['message' => 'Showtime not found'], 404);
        }

        $showtime->delete();
        return response()->json(['message' => 'Showtime deleted successfully']);
    }
}
