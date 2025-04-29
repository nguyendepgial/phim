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
Route::middleware('auth:api')->post('/update-profile', [AuthController::class, 'updateProfile']);


// 🎬 **API phim & suất chiếu**
Route::get('/movies', [MovieController::class, 'getMovies']);
Route::get('/movies/{id}', [MovieController::class, 'getMovieDetail']);
Route::get('/showtimes/movie/{movie_id}', [ShowtimeController::class, 'getShowtimesByMovie']);
Route::get('/showtimes/cinema/{cinema_id}', [ShowtimeController::class, 'getShowtimesByCinema']);
Route::get('/showtimes/date/{date}', [ShowtimeController::class, 'getShowtimesByDate']);

// 🍿 **API danh sách rạp**
Route::get('/cinemas', [CinemaController::class, 'getCinemas']);
Route::get('/cinemas/{id}', [CinemaController::class, 'getCinemaDetail']);
Route::get('/products/combos', [ProductController::class, 'getCombos']);

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
Route::get('/extras', [ProductController::class, 'getCombos']);

// 🎟 **API đặt vé & combo (Yêu cầu đăng nhập)**
Route::middleware(['auth:api'])->group(function () {
    Route::post('/bookings', [BookingController::class, 'bookTicket']); // ✅ Đặt vé + combo bắp nước
    Route::get('/bookings', [BookingController::class, 'getUserBookings']); // 📜 Danh sách đặt vé
    Route::delete('/bookings/{id}', [BookingController::class, 'cancelBooking']); // ❌ Hủy vé
    
    // 📜 **API chi tiết đặt vé**
    Route::get('/booking-details/{booking_id}', [BookingDetailController::class, 'index']); // Lấy chi tiết ghế đã đặt
    Route::post('/booking-details', [BookingDetailController::class, 'store']); // Thêm ghế vào booking
    Route::delete('/booking-details/{id}', [BookingDetailController::class, 'cancelBookingDetail']); // Hủy từng ghế
    Route::post('/book-ticket', [BookingController::class, 'bookTicket']);

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


use App\Http\Controllers\Admin\AuthAdminController;

Route::post('/admin/login', [AuthAdminController::class, 'login']);  // Đăng nhập admin
Route::middleware('auth:api')->post('/admin/logout', [AuthAdminController::class, 'logout']);  // Đăng xuất
Route::middleware('auth:api')->get('/admin/me', [AuthAdminController::class, 'me']);  // Lấy thông tin admin

use App\Http\Controllers\Admin\{
    
    BookingAdminController,
    CinemaAdminController,
    DashboardAdminController,
    GenreAdminController,
    MovieAdminController,
    ProductAdminController,
    SeatAdminController,
    ShowtimeAdminController,
    UserAdminController
};

Route::prefix('admin')->group(function () {
    Route::resource('movies', MovieAdminController::class);
    Route::get('/movies/{id}', [MovieAdminController::class, 'show']);
    Route::resource('showtimes', ShowtimeAdminController::class);
    Route::get('dashboard', [DashboardAdminController::class, 'index']);
});



Route::prefix('admin')->group(function () {
    Route::get('/seats', [SeatAdminController::class, 'index']);      // GET list
    Route::post('/seats', [SeatAdminController::class, 'store']);     // POST create
    Route::put('/seats/{id}', [SeatAdminController::class, 'update']); // PUT update
    Route::delete('/seats/{id}', [SeatAdminController::class, 'destroy']); // DELETE
    Route::get('/seats/{id}', [SeatAdminController::class, 'show']);   // GET detail (optional)
});



Route::prefix('admin/cinemas')->group(function () {
    Route::get('/', [CinemaAdminController::class, 'index']); // Danh sách
    Route::post('/store', [CinemaAdminController::class, 'store']); // Thêm
    Route::get('/{id}', [CinemaAdminController::class, 'show']); // ✅ GET chi tiết rạp
    Route::put('/{id}', [CinemaAdminController::class, 'update']); // Sửa
    Route::delete('/{id}', [CinemaAdminController::class, 'destroy']); // Xoá
});




Route::prefix('admin/genres')->group(function() {
    Route::get('/', [GenreAdminController::class, 'index']);
    Route::post('/', [GenreAdminController::class, 'store']);
    Route::put('/{id}', [GenreAdminController::class, 'update']);
    Route::delete('/{id}', [GenreAdminController::class, 'destroy']);
});




Route::prefix('admin')->group(function () {
    Route::get('/bookings', [BookingAdminController::class, 'index']);
    Route::get('/bookings/{id}', [BookingAdminController::class, 'show']);
    Route::put('/bookings/{id}', [BookingAdminController::class, 'update']);
    Route::delete('/bookings/{id}', [BookingAdminController::class, 'destroy']);
});




Route::prefix('admin/products')->group(function() {
    Route::get('/', [ProductAdminController::class, 'index']);
    Route::post('/', [ProductAdminController::class, 'store']);
    Route::put('/{id}', [ProductAdminController::class, 'update']);
    Route::delete('/{id}', [ProductAdminController::class, 'destroy']);
    Route::get('/{id}', [ProductAdminController::class, 'show']);

});

Route::prefix('admin/users')->group(function() {
    Route::get('/', [UserAdminController::class, 'index']);
    Route::post('/', [UserAdminController::class, 'store']);
    Route::put('/{id}', [UserAdminController::class, 'update']);
    Route::delete('/{id}', [UserAdminController::class, 'destroy']);
});

