<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Payment, Booking, BookingExtra};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * 🎟 Tạo yêu cầu thanh toán (Momo/VNPay)
     */
    public function createPayment(Request $request)
    {
        try {
            $user = Auth::user();

            // Kiểm tra booking có tồn tại và chưa thanh toán
            $booking = Booking::where('id', $request->booking_id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$booking) {
                return response()->json(['error' => 'Đơn hàng không hợp lệ hoặc đã thanh toán'], 400);
            }

            // Tính tổng tiền bao gồm cả combo bắp nước
            $extrasTotal = BookingExtra::where('booking_id', $booking->id)
                ->sum(DB::raw('quantity * price'));

            $finalTotal = $booking->total_price + $extrasTotal;

            // 🔹 Giả lập tạo URL thanh toán Momo/VNPay
            $paymentUrl = "https://sandbox.vnpayment.vn/payment?amount={$finalTotal}&booking_id={$booking->id}&user_id={$user->id}";

            return response()->json([
                'status' => 'success',
                'payment_url' => $paymentUrl
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi tạo yêu cầu thanh toán',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔄 Xử lý callback thanh toán từ Momo/VNPay
     */
    public function handlePaymentCallback(Request $request)
    {
        try {
            DB::beginTransaction();

            // Kiểm tra xem booking có tồn tại không
            $booking = Booking::find($request->booking_id);

            if (!$booking) {
                return response()->json(['error' => 'Đơn hàng không tồn tại'], 404);
            }

            // Kiểm tra tổng tiền đã thanh toán có khớp không
            $extrasTotal = BookingExtra::where('booking_id', $booking->id)
                ->sum(DB::raw('quantity * price'));

            $expectedTotal = $booking->total_price + $extrasTotal;

            if ($request->amount != $expectedTotal) {
                return response()->json(['error' => 'Số tiền thanh toán không hợp lệ'], 400);
            }

            // Xử lý theo trạng thái thanh toán
            if ($request->status == 'success') {
                $booking->update(['status' => 'paid']);

                Payment::create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'amount' => $expectedTotal,
                    'payment_method' => $request->payment_method,
                    'transaction_id' => $request->transaction_id,
                    'status' => 'success'
                ]);

                DB::commit();

                return response()->json(['message' => 'Thanh toán thành công'], 200);
            } else {
                $booking->update(['status' => 'cancelled']);
                DB::commit();

                return response()->json(['message' => 'Thanh toán thất bại, đơn hàng đã bị hủy'], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Lỗi khi xử lý thanh toán',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📜 Lấy lịch sử thanh toán của người dùng
     */
    public function getUserPayments()
    {
        try {
            $user = Auth::user();

            $payments = Payment::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'payments' => $payments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi lấy lịch sử thanh toán',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔍 Kiểm tra trạng thái thanh toán của một đơn hàng
     */
    public function checkPaymentStatus($booking_id)
    {
        try {
            $user = Auth::user();

            $payment = Payment::where('booking_id', $booking_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$payment) {
                return response()->json(['error' => 'Không tìm thấy thanh toán cho đơn hàng này'], 404);
            }

            return response()->json([
                'status' => 'success',
                'payment' => $payment
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi kiểm tra trạng thái thanh toán',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
