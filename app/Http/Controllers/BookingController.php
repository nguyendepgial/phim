<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Booking, BookingDetail, ShowtimeSeat, Seat, BookingExtra};
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


class BookingController extends Controller
{
    /**
     * Đặt vé cho người dùng (bao gồm cả combo nếu có)
     */
    public function bookTicket(Request $request)
    {
        Log::info('✅ Payload nhận từ frontend:', $request->all());

        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'Không xác thực được người dùng'], 401);
            }

            $request->validate([
                'showtime_id' => 'required|exists:showtimes,id',
                'seats' => 'required|array|min:1',
                'seats.*.seat_id' => 'required|exists:seats,id',
                'seats.*.price' => 'required|numeric|min:0',
                'extras' => 'nullable|array',
                'extras.*.product_id' => 'required|exists:products,id',
                'extras.*.quantity' => 'required|integer|min:1',
                'extras.*.price' => 'required|numeric|min:0',
                'total_price' => 'required|numeric|min:0'
            ]);

            $seatTotal = array_sum(array_column($request->seats, 'price'));
            $extraTotal = $request->filled('extras') ? array_sum(array_map(function ($extra) {
                return $extra['price'] * $extra['quantity'];
            }, $request->extras)) : 0;

            $calculatedTotal = $seatTotal + $extraTotal;

            if ($calculatedTotal != $request->total_price) {
                return response()->json(['error' => 'Tổng tiền không khớp'], 400);
            }

            DB::beginTransaction();

            foreach ($request->seats as $seat) {
                $validSeat = ShowtimeSeat::where('showtime_id', $request->showtime_id)
                    ->where('seat_id', $seat['seat_id'])
                    ->where('status', 'available')
                    ->first();

                if (!$validSeat) {
                    DB::rollBack();
                    return response()->json(['error' => "Ghế ID {$seat['seat_id']} không khả dụng hoặc đã được đặt"], 400);
                }
            }

            $booking = Booking::create([
                'user_id' => $user->id,
                'showtime_id' => $request->showtime_id,
                'total_price' => $request->total_price,
                'status' => 'pending'
            ]);

            foreach ($request->seats as $seat) {
                BookingDetail::create([
                    'booking_id' => $booking->id,   
                    'seat_id' => $seat['seat_id'],
                    'price' => $seat['price']
                ]);

                ShowtimeSeat::where('showtime_id', $request->showtime_id)
                    ->where('seat_id', $seat['seat_id'])
                    ->update(['status' => 'booked']);
            }

            if ($request->filled('extras')) {
                foreach ($request->extras as $extra) {
                    BookingExtra::create([
                        'booking_id' => $booking->id,
                        'product_id' => $extra['product_id'],
                        'quantity' => $extra['quantity'],
                        'price' => $extra['price']
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Đặt vé thành công!', 'booking' => $booking], 201);
        } 
        catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 400);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Lỗi khi đặt vé', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Lấy danh sách đặt vé của người dùng
     */
    public function getUserBookings()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'Không xác thực được người dùng'], 401);
            }
    
            $bookings = Booking::where('user_id', $user->id)
                ->with([
                    'bookingDetails.seat', 
                    'extras.product', 
                    'showtime.movie', 
                    'showtime.cinema'
                ])
                ->orderBy('created_at', 'desc')
                ->get();
    
            return response()->json(['bookings' => $bookings], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi khi lấy danh sách đặt vé', 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Hủy vé đã đặt
     */
    public function cancelBooking($id)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'Không xác thực được người dùng'], 401);
            }

            $booking = Booking::where('id', $id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$booking) {
                return response()->json(['error' => 'Vé không hợp lệ hoặc đã thanh toán'], 400);
            }

            DB::beginTransaction();

            $booking->update(['status' => 'cancelled']);

            $bookedSeats = BookingDetail::where('booking_id', $id)->get();
            foreach ($bookedSeats as $seatDetail) {
                ShowtimeSeat::where('showtime_id', $booking->showtime_id)
                    ->where('seat_id', $seatDetail->seat_id)
                    ->update(['status' => 'available']);
            }

            DB::commit();

            return response()->json(['message' => 'Hủy vé thành công'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Lỗi khi hủy vé', 'message' => $e->getMessage()], 500);
        }
    }
}
