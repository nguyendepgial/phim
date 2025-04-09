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
                // Nếu user chưa tồn tại thì tạo mới
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => bcrypt(uniqid()), // Mật khẩu ngẫu nhiên
                ]);
            }

            // Tạo token JWT
            $token = JWTAuth::fromUser($user);

            // Redirect về frontend kèm token
            $frontendUrl = config('app.frontend_url') ?? 'http://localhost:3000';
            return redirect("$frontendUrl/auth/callback?token=$token&user_id={$user->id}");


            return response()->json([
                'user' => $user,
                'access_token' => $token,
            ], 200);
        } catch (\Exception $e) {
            $frontendUrl = config('app.frontend_url') ?? 'http://localhost:3000';
            return redirect("$frontendUrl/login?error=google_auth_failed");
            return response()->json(['error' => 'Google authentication failed', 'message' => $e->getMessage()], 500);
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
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'Không tìm thấy người dùng'], 404);
            }
            return response()->json(['user' => $user], 200);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token đã hết hạn'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token không hợp lệ'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Không có token'], 401);
        }
    }
}
