<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Chuyển hướng đến Google để đăng nhập
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Xử lý callback từ Google OAuth
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Kiểm tra xem user đã tồn tại trong database hay chưa
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => bcrypt(uniqid()),
                ]);
            }

            $token = JWTAuth::fromUser($user);
            $frontendUrl = config('app.frontend_url') ?? 'http://localhost:3000';
            return redirect("$frontendUrl/auth/callback?token=$token&user_id={$user->id}");
        } catch (\Exception $e) {
            $frontendUrl = config('app.frontend_url') ?? 'http://localhost:3000';
            return redirect("$frontendUrl/login?error=google_auth_failed");
        }
    }

    /**
     * Đăng xuất, vô hiệu hóa token hiện tại
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Đăng xuất thành công'], 200);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token không hợp lệ'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Lỗi khi đăng xuất', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Lấy thông tin người dùng hiện tại
     */
    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json(['user' => $user], 200);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token đã hết hạn'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token không hợp lệ'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Không có token'], 401);
        }
    }

    /**
     * Cập nhật họ tên và số điện thoại
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user(); // Lấy user từ token
    
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
            ]);
    
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->save();
    
            return response()->json([
                'message' => 'Cập nhật thành công',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi cập nhật thông tin',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

}
