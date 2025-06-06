<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CinemaController, MovieController, ShowtimeController, SeatController, 
    BookingController, BookingDetailController, PaymentController, AuthController, ProductController, GenreController
};
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', function () {
    return view('welcome');
});
Route::get('/movies', [MovieController::class, 'index']);
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);



