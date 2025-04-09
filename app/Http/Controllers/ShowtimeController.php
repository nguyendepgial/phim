<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Showtime;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class ShowtimeController extends Controller
{
    /**
     * 📽 Lấy danh sách suất chiếu theo phim (chỉ lấy suất chiếu chưa kết thúc)
     */
    public function getShowtimesByMovie($movie_id)
    {
        $showtimes = Showtime::where('movie_id', $movie_id)
            ->whereDate('show_date', '>=', now()->toDateString()) // Chỉ lấy suất chiếu từ hôm nay trở đi
            ->with(['cinema', 'movie'])
            ->get();

        if ($showtimes->isEmpty()) {
            return response()->json(['error' => 'Không có suất chiếu cho phim này'], 404);
        }

        return response()->json(['showtimes' => $showtimes], 200);
    }

    /**
     * 🎥 Lấy danh sách suất chiếu theo rạp
     */
    public function getShowtimesByCinema($cinema_id)
    {
        $showtimes = Showtime::where('cinema_id', $cinema_id)
            ->whereDate('show_date', '>=', now()->toDateString()) // Chỉ lấy suất chiếu từ hôm nay trở đi
            ->with(['cinema', 'movie'])
            ->get();

        if ($showtimes->isEmpty()) {
            return response()->json(['error' => 'Không có suất chiếu nào tại rạp này'], 404);
        }

        return response()->json(['showtimes' => $showtimes], 200);
    }

    /**
     * 📅 Lấy danh sách suất chiếu theo ngày
     */
    public function getShowtimesByDate($date)
    {
        $showtimes = Showtime::whereDate('show_date', $date)
            ->with(['cinema', 'movie'])
            ->get();

        if ($showtimes->isEmpty()) {
            return response()->json(['error' => 'Không có suất chiếu nào trong ngày này'], 404);
        }

        return response()->json(['showtimes' => $showtimes], 200);
    }

    /**
     * 🔍 Lấy chi tiết một suất chiếu
     */
    public function getShowtimeDetail($id)
    {
        $showtime = Showtime::with(['cinema', 'movie'])->find($id);

        if (!$showtime) {
            return response()->json(['error' => 'Suất chiếu không tồn tại'], 404);
        }

        return response()->json(['showtime' => $showtime], 200);
    }

    /**
     * ➕ Thêm suất chiếu mới
     */
    public function createShowtime(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'cinema_id' => 'required|exists:cinemas,id',
            'show_date' => 'required|date',
            'show_time' => 'required|date_format:H:i', // Giữ format chuẩn H:i
            'ticket_price' => 'required|numeric|min:0'
        ]);

        $showtime = Showtime::create($request->all());

        return response()->json([
            'message' => 'Thêm suất chiếu thành công',
            'showtime' => $showtime
        ], 201);
    }

    /**
     * ✏️ Cập nhật suất chiếu (Không thể cập nhật suất chiếu có vé đặt trước)
     */
    public function updateShowtime(Request $request, $id)
    {
        $showtime = Showtime::find($id);

        if (!$showtime) {
            return response()->json(['error' => 'Suất chiếu không tồn tại'], 404);
        }

        // Kiểm tra xem suất chiếu có vé đặt chưa
        $hasBookings = Booking::where('showtime_id', $id)->exists();
        if ($hasBookings) {
            return response()->json(['error' => 'Không thể sửa suất chiếu đã có vé đặt trước'], 400);
        }

        $request->validate([
            'movie_id' => 'sometimes|exists:movies,id',
            'cinema_id' => 'sometimes|exists:cinemas,id',
            'show_date' => 'sometimes|date',
            'show_time' => 'sometimes|date_format:H:i',
            'ticket_price' => 'sometimes|numeric|min:0'
        ]);

        $showtime->update($request->all());

        return response()->json([
            'message' => 'Cập nhật suất chiếu thành công',
            'showtime' => $showtime
        ], 200);
    }

    /**
     * 🗑 Xóa suất chiếu (Không thể xóa suất chiếu đã có vé đặt trước)
     */
    public function deleteShowtime($id)
    {
        $showtime = Showtime::find($id);

        if (!$showtime) {
            return response()->json(['error' => 'Suất chiếu không tồn tại'], 404);
        }

        // Kiểm tra xem suất chiếu có vé đặt chưa
        $hasBookings = Booking::where('showtime_id', $id)->exists();
        if ($hasBookings) {
            return response()->json(['error' => 'Không thể xóa suất chiếu đã có vé đặt trước'], 400);
        }

        $showtime->delete();

        return response()->json([
            'message' => 'Xóa suất chiếu thành công'
        ], 200);
    }
}
