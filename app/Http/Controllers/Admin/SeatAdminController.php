<?php

namespace App\Http\Controllers\Admin;

use App\Models\Seat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SeatAdminController extends Controller
{
    // ✅ API lấy danh sách ghế, lọc theo rạp nếu có
    public function index(Request $request)
    {
        $cinemaId = $request->query('cinema_id'); // lấy param truyền vào

        $query = Seat::query();

        if ($cinemaId) {
            $query->where('cinema_id', $cinemaId);
        }

        // ⚠️ Đổi từ paginate sang get để load toàn bộ sơ đồ ghế
        $seats = $query->orderBy('row_number')
                       ->orderBy('column_number')
                       ->get();

        return response()->json($seats);
    }

    public function show($id)
    {
        $seat = Seat::findOrFail($id);
        return response()->json($seat);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'cinema_id' => 'required|exists:cinemas,id',
            'row_number' => 'required|string',
            'column_number' => 'required|integer',
            'seat_number' => 'required|string',
            'seat_type' => 'required|string',
        ]);

        // ✅ Kiểm tra ghế đã tồn tại chưa trong cùng rạp
        $exists = Seat::where('cinema_id', $validatedData['cinema_id'])
            ->where('row_number', $validatedData['row_number'])
            ->where('column_number', $validatedData['column_number'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Ghế đã tồn tại trong rạp này!',
                'errors' => ['duplicate' => ['Vị trí hàng/cột đã có trong rạp.']]
            ], 422);
        }

        $seat = Seat::create($validatedData);

        return response()->json([
            'message' => 'Seat created successfully!',
            'data' => $seat
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $seat = Seat::findOrFail($id);

        $validatedData = $request->validate([
            'cinema_id' => 'required|exists:cinemas,id',
            'row_number' => 'required|string',
            'column_number' => 'required|integer',
            'seat_number' => 'required|string',
            'seat_type' => 'required|string',
        ]);

        // ✅ Kiểm tra nếu trùng vị trí với ghế khác
        $exists = Seat::where('cinema_id', $validatedData['cinema_id'])
            ->where('row_number', $validatedData['row_number'])
            ->where('column_number', $validatedData['column_number'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Ghế đã tồn tại trong rạp này!',
                'errors' => ['duplicate' => ['Vị trí hàng/cột đã có trong rạp.']]
            ], 422);
        }

        $seat->update($validatedData);

        return response()->json([
            'message' => 'Seat updated successfully!',
            'data' => $seat
        ]);
    }

    public function destroy($id)
    {
        $seat = Seat::findOrFail($id);
        $seat->delete();

        return response()->json([
            'message' => 'Seat deleted successfully!'
        ]);
    }
}
