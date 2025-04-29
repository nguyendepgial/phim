<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use Illuminate\Http\Request;

class CinemaAdminController extends Controller
{
    // Hiển thị danh sách tất cả các cinema
    public function index()
    {
        $cinemas = Cinema::all(); // Lấy tất cả cinema
        return response()->json($cinemas); // Trả về dữ liệu dưới dạng JSON
    }
    public function show($id)
    {
        $cinema = \App\Models\Cinema::findOrFail($id);
        return response()->json($cinema);
    }

    // Thêm mới một cinema
    public function store(Request $request)
    {
        // Xác thực dữ liệu nếu cần
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
        ]);

        // Thêm mới cinema vào cơ sở dữ liệu
        $cinema = Cinema::create([
            'name' => $request->input('name'),
            'address' => $request->input('address', null), // Đảm bảo giá trị mặc định là null nếu không có
            'phone' => $request->input('phone', null), // Đảm bảo giá trị mặc định là null nếu không có
        ]);

        // Trả về cinema mới được tạo
        return response()->json($cinema, 201);
    }

    // Sửa thông tin cinema
    public function update(Request $request, $id)
    {
        $cinema = Cinema::find($id); // Tìm cinema theo ID
        if (!$cinema) {
            return response()->json(['message' => 'Cinema not found'], 404);
        }

        // Xác thực dữ liệu nếu cần
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
        ]);

        // Cập nhật thông tin cinema
        $cinema->update([
            'name' => $request->input('name'),
            'address' => $request->input('address', $cinema->address), // Giữ nguyên nếu không có thay đổi
            'phone' => $request->input('phone', $cinema->phone), // Giữ nguyên nếu không có thay đổi
        ]);

        // Trả về cinema đã được cập nhật
        return response()->json($cinema);
    }

    // Xóa một cinema
    public function destroy($id)
    {
        $cinema = Cinema::find($id); // Tìm cinema theo ID
        if (!$cinema) {
            return response()->json(['message' => 'Cinema not found'], 404);
        }

        // Xóa cinema
        $cinema->delete();

        // Trả về thông báo thành công
        return response()->json(['message' => 'Cinema deleted successfully']);
    }
}
