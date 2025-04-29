<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingAdminController extends Controller
{
    // Lấy danh sách bookings
    public function index()
    {
        $bookings = Booking::with('user', 'showtime.movie', 'showtime.cinema')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($bookings);
    }

    // Lấy chi tiết 1 booking
    public function show($id)
    {
        $booking = Booking::with('user', 'showtime.movie', 'showtime.cinema', 'bookingDetails.seat')
            ->findOrFail($id);

        return response()->json($booking);
    }

    // Cập nhật trạng thái booking
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,canceled',
        ]);

        $booking = Booking::findOrFail($id);
        $booking->status = $request->status;
        $booking->save();

        return response()->json(['message' => 'Cập nhật trạng thái thành công']);
    }

    // Xoá booking
    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return response()->json(['message' => 'Xoá đơn hàng thành công']);
    }
}
