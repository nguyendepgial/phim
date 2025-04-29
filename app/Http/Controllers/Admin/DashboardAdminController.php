<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
    // Hiển thị thông tin tổng quan trên dashboard
    public function index()
    {
        $data = [
            'totalBookings' => \App\Models\Booking::count(),
            'totalCinemas' => \App\Models\Cinema::count(),
            'totalMovies' => \App\Models\Movie::count(),
            'totalUsers' => \App\Models\User::count(),
        ];

        return response()->json($data);
    }
}
