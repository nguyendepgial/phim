<?php

namespace App\Http\Controllers\Admin; // Đảm bảo namespace đúng
 use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

class AuthAdminController extends Controller
{
   

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
    
        // Kiểm tra xem người dùng có tồn tại không
        $user = User::where('email', $credentials['email'])->first();
    
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Sai email hoặc mật khẩu'], 401);
        }
    
        // Kiểm tra quyền admin
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Không có quyền truy cập admin'], 403);
        }
    
        // Tạo và trả về token JWT
        $token = JWTAuth::fromUser($user);
    
        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }
    

    public function logout(Request $request)
    {
        try {
            // Vô hiệu hóa token hiện tại
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Đăng xuất thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Có lỗi khi đăng xuất', 'message' => $e->getMessage()], 500);
        }
    }
    

    public function me()
    {
        try {
            $user = auth()->user();
            return response()->json(['user' => $user], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không tìm thấy người dùng'], 404);
        }
    }
}
