<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookingDetail;
use App\Models\Booking;
use App\Models\ShowtimeSeat;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;

class BookingDetailController extends Controller
{
    /**
     * 📜 Lấy danh sách chi tiết đặt vé theo booking_id
     */
    public function index($booking_id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $booking = Booking::where('id', $booking_id)
                ->where('user_id', $user->id)
                ->with(['bookingDetails.seat', 'showtime.movie', 'showtime.cinema'])
                ->first();

            if (!$booking) {
                return response()->json(['error' => 'Bạn không có quyền xem vé này'], 403);
            }

            return response()->json([
                'status' => 'success',
                'booking' => $booking
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi lấy chi tiết đặt vé',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🎟 Thêm chi tiết đặt vé khi khách đặt ghế thành công
     */
    public function store(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'seats' => 'required|array|min:1',
                'seats.*.seat_id' => 'required|exists:seats,id',
                'seats.*.price' => 'required|numeric|min:0'
            ]);

            DB::beginTransaction();

            $booking = Booking::where('id', $request->booking_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            foreach ($request->seats as $seat) {
                $isSeatBooked = BookingDetail::whereHas('booking', function ($query) use ($booking) {
                    $query->where('showtime_id', $booking->showtime_id);
                })->where('seat_id', $seat['seat_id'])->exists();

                if ($isSeatBooked) {
                    return response()->json(['error' => "Ghế ID {$seat['seat_id']} đã được đặt"], 400);
                }

                // Cập nhật trạng thái ghế thành `booked`
                ShowtimeSeat::where('showtime_id', $booking->showtime_id)
                    ->where('seat_id', $seat['seat_id'])
                    ->update(['status' => 'booked']);

                BookingDetail::create([
                    'booking_id' => $request->booking_id,
                    'seat_id' => $seat['seat_id'],
                    'price' => $seat['price']
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Đặt ghế thành công!'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Lỗi khi thêm chi tiết đặt vé',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ❌ Hủy một ghế trong booking
     */
    public function cancelBookingDetail($id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $bookingDetail = BookingDetail::findOrFail($id);
            $booking = Booking::find($bookingDetail->booking_id);

            if ($booking->user_id !== $user->id) {
                return response()->json(['error' => 'Bạn không thể hủy vé này'], 403);
            }

            if ($booking->status === 'paid') {
                return response()->json(['error' => 'Vé đã thanh toán, không thể hủy'], 400);
            }

            DB::beginTransaction();

            // Cập nhật trạng thái ghế về `available`
            ShowtimeSeat::where('showtime_id', $booking->showtime_id)
                ->where('seat_id', $bookingDetail->seat_id)
                ->update(['status' => 'available']);

            $bookingDetail->delete();

            // Nếu booking không còn ghế nào sau khi xóa, thì tự động hủy booking
            if ($booking->bookingDetails()->count() === 0) {
                $booking->update(['status' => 'cancelled']);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Hủy ghế thành công!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Lỗi khi hủy ghế',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
