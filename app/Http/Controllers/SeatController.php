<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seat;
use App\Models\BookingDetail;
use App\Models\ShowtimeSeat;
use Illuminate\Support\Facades\DB;

class SeatController extends Controller
{
    /**
     * 🏢 Lấy danh sách ghế theo rạp
     */
    public function getSeatsByCinema($cinema_id)
    {
        $seats = Seat::where('cinema_id', $cinema_id)->get();

        if ($seats->isEmpty()) {
            return response()->json(['error' => 'Không có ghế nào trong rạp này'], 404);
        }

        return response()->json(['seats' => $seats], 200);
    }

    /**
     * 🎟 Lấy danh sách ghế trống theo suất chiếu
     */
    public function getAvailableSeats($showtime_id)
    {
        // Lấy danh sách ghế có trạng thái 'available' trong showtime_seats
        $availableSeats = ShowtimeSeat::where('showtime_id', $showtime_id)
            ->where('status', 'available')
            ->join('seats', 'showtime_seats.seat_id', '=', 'seats.id')
            ->select('seats.*')
            ->get();

        return response()->json(['available_seats' => $availableSeats], 200);
    }

    /**
     * 🔍 Lấy danh sách toàn bộ ghế theo suất chiếu (cả ghế trống & đã đặt)
     */
    public function getAllSeatsForShowtime($showtime_id)
    {
        $seats = Seat::leftJoin('showtime_seats', function ($join) use ($showtime_id) {
                $join->on('seats.id', '=', 'showtime_seats.seat_id')
                     ->where('showtime_seats.showtime_id', $showtime_id);
            })
            ->select('seats.*', DB::raw("COALESCE(showtime_seats.status, 'unknown') AS seat_status"))
            ->get();

        return response()->json(['seats' => $seats], 200);
    }

    /**
     * 📌 Lấy danh sách ghế theo hàng & cột để frontend hiển thị dễ hơn
     */
    public function getSeatsByCinemaFormatted($cinema_id)
    {
        $seats = Seat::where('cinema_id', $cinema_id)
            ->orderBy('row_number')
            ->orderBy('column_number')
            ->get()
            ->groupBy('row_number');

        return response()->json(['seats' => $seats], 200);
    }

    /**
     * 🔎 Lấy chi tiết một ghế
     */
    public function getSeatDetail($id)
    {
        $seat = Seat::find($id);

        if (!$seat) {
            return response()->json(['error' => 'Ghế không tồn tại'], 404);
        }

        return response()->json(['seat' => $seat], 200);
    }

    /**
     * ➕ Thêm ghế mới vào rạp
     */
    public function createSeat(Request $request)
    {
        $request->validate([
            'cinema_id' => 'required|exists:cinemas,id',
            'row_number' => 'required|string|max:10',
            'column_number' => 'required|integer|min:1',
            'seat_number' => 'required|string|unique:seats,seat_number',
            'seat_type' => 'required|in:standard,vip,couple'
        ]);

        $seat = Seat::create($request->all());

        return response()->json([
            'message' => 'Thêm ghế thành công',
            'seat' => $seat
        ], 201);
    }

    /**
     * ✏️ Cập nhật thông tin ghế
     */
    public function updateSeat(Request $request, $id)
    {
        $seat = Seat::find($id);

        if (!$seat) {
            return response()->json(['error' => 'Ghế không tồn tại'], 404);
        }

        $request->validate([
            'cinema_id' => 'sometimes|exists:cinemas,id',
            'row_number' => 'sometimes|string|max:10',
            'column_number' => 'sometimes|integer|min:1',
            'seat_number' => 'sometimes|string|unique:seats,seat_number,' . $id,
            'seat_type' => 'sometimes|in:standard,vip,couple'
        ]);

        $seat->update($request->all());

        return response()->json([
            'message' => 'Cập nhật ghế thành công',
            'seat' => $seat
        ], 200);
    }

    /**
     * 🗑 Xóa ghế khỏi rạp (Không được xóa ghế đã đặt)
     */
    public function deleteSeat($id)
    {
        $seat = Seat::find($id);

        if (!$seat) {
            return response()->json(['error' => 'Ghế không tồn tại'], 404);
        }

        // Kiểm tra xem ghế có đang được đặt không
        $isBooked = BookingDetail::where('seat_id', $id)->exists();
        if ($isBooked) {
            return response()->json(['error' => 'Không thể xóa ghế đã được đặt'], 400);
        }

        // Kiểm tra xem ghế có trong bất kỳ suất chiếu nào không
        $isInShowtime = ShowtimeSeat::where('seat_id', $id)->exists();
        if ($isInShowtime) {
            return response()->json(['error' => 'Không thể xóa ghế đã được sử dụng trong suất chiếu'], 400);
        }

        $seat->delete();

        return response()->json([
            'message' => 'Xóa ghế thành công'
        ], 200);
    }
}
