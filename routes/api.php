<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CinemaController, MovieController, ShowtimeController, SeatController, 
    BookingController, BookingDetailController, PaymentController, AuthController, ProductController, GenreController
};

// 🏷️ **Xác thực Google OAuth + JWTAuth**
Route::post('/auth/google', [AuthController::class, 'loginWithGoogle']);
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::middleware(['auth:api'])->get('/auth/me', [AuthController::class, 'me']);

// 🎬 **API phim & suất chiếu**
Route::get('/movies', [MovieController::class, 'getMovies']);
Route::get('/movies/{id}', [MovieController::class, 'getMovieDetail']);
Route::get('/showtimes/movie/{movie_id}', [ShowtimeController::class, 'getShowtimesByMovie']);
Route::get('/showtimes/cinema/{cinema_id}', [ShowtimeController::class, 'getShowtimesByCinema']);
Route::get('/showtimes/date/{date}', [ShowtimeController::class, 'getShowtimesByDate']);

// 🍿 **API danh sách rạp**
Route::get('/cinemas', [CinemaController::class, 'getCinemas']);
Route::get('/cinemas/{id}', [CinemaController::class, 'getCinemaDetail']);

// 💺 **API ghế ngồi**
Route::get('/seats/cinema/{cinema_id}', [SeatController::class, 'getSeatsByCinema']);
Route::get('/seats/cinema/{cinema_id}/formatted', [SeatController::class, 'getSeatsByCinemaFormatted']);
Route::get('/seats/showtime/{showtime_id}', [SeatController::class, 'getAvailableSeats']);
Route::get('/seats/showtime/{showtime_id}/all', [SeatController::class, 'getAllSeatsForShowtime']);
Route::get('/seats/{seat_id}', [SeatController::class, 'getSeatDetail']);
Route::post('/seats', [SeatController::class, 'createSeat']);
Route::put('/seats/{seat_id}', [SeatController::class, 'updateSeat']);
Route::delete('/seats/{seat_id}', [SeatController::class, 'deleteSeat']);

// 📦 **API sản phẩm (Combo bắp nước)**
Route::get('/products', [ProductController::class, 'getProducts']);
Route::get('/products/{id}', [ProductController::class, 'getProductDetail']);
Route::get('/products/combos', [ProductController::class, 'getCombos']); // 🆕 API lấy danh sách combo bắp nước

// 🎟 **API đặt vé & combo (Yêu cầu đăng nhập)**
Route::middleware(['auth:api'])->group(function () {
    Route::post('/bookings', [BookingController::class, 'bookTicket']); // ✅ Đặt vé + combo bắp nước
    Route::get('/bookings', [BookingController::class, 'getUserBookings']); // 📜 Danh sách đặt vé
    Route::delete('/bookings/{id}', [BookingController::class, 'cancelBooking']); // ❌ Hủy vé
    
    // 📜 **API chi tiết đặt vé**
    Route::get('/booking-details/{booking_id}', [BookingDetailController::class, 'index']); // Lấy chi tiết ghế đã đặt
    Route::post('/booking-details', [BookingDetailController::class, 'store']); // Thêm ghế vào booking
    Route::delete('/booking-details/{id}', [BookingDetailController::class, 'cancelBookingDetail']); // Hủy từng ghế
});

// 💳 **API thanh toán (Yêu cầu đăng nhập)**
Route::middleware(['auth:api'])->group(function () {
    Route::post('/payments/create', [PaymentController::class, 'createPayment']); // ✅ Tạo yêu cầu thanh toán
    Route::get('/payments/callback', [PaymentController::class, 'handlePaymentCallback']); // 🔄 Xử lý callback thanh toán
    Route::get('/payments', [PaymentController::class, 'getUserPayments']); // 📜 Lịch sử thanh toán
    Route::get('/payments/status/{booking_id}', [PaymentController::class, 'checkPaymentStatus']); // 🔍 Kiểm tra thanh toán
});

// 🎟 **API dành cho Admin (Thêm, sửa, xóa suất chiếu)**
Route::middleware(['auth:api'])->group(function () {
    Route::post('/showtimes', [ShowtimeController::class, 'createShowtime']); // ✅ Thêm suất chiếu
    Route::put('/showtimes/{id}', [ShowtimeController::class, 'updateShowtime']); // ✅ Cập nhật suất chiếu
    Route::delete('/showtimes/{id}', [ShowtimeController::class, 'deleteShowtime']); // ✅ Xóa suất chiếu
});

//api cho the loai phim
Route::get('genres', [GenreController::class, 'index']);  // Lấy tất cả thể loại
Route::get('genres/{id}', [GenreController::class, 'show']);  // Lấy thể loại theo ID
Route::middleware(['auth:api'])->group(function () {
    Route::post('genres', [GenreController::class, 'store']);  // Tạo thể loại mới
    Route::put('genres/{id}', [GenreController::class, 'update']);  // Cập nhật thể loại
    Route::delete('genres/{id}', [GenreController::class, 'destroy']);  // Xóa thể loại
});
