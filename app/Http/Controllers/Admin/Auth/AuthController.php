<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct()
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $this->validate($request, User::$rules);
        $credentials = request(['username', 'password']);
        $credentials['type'] = ADMIN_TYPE;
        $token = auth()->attempt($credentials);
        if (!$token) {
            return $this->sendUnauthorized('Tên đăng nhập hoặc mật khẩu không đúng!');
        }
        $user = auth('api')->user();
        $response = [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 6000000,
            'user' => $user->toArray(),
        ];
        return $this->sendSuccess('Đăng nhập thành công!', $response);
    }
    //

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return $this->sendSuccess('Đăng xuất thành công', []);
    }

    public function me()
    {
        return $this->sendSuccess('Lấy data thành công',  auth('api')->user());
    }

}
