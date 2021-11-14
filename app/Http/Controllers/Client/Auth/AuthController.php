<?php
namespace App\Http\Controllers\Client\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(Request $request)
    {
        $this->validate($request, $this->userRepository->loginRules());
        $token = $this->userRepository->login($request);
        if (!$token) {
            return $this->sendError(__('auth.login_fail'));
        }
        $response = [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth(GUARD_CUSTOMER)->factory()->getTTL() * 604800,
            'user'       => new UserResource(auth(GUARD_CUSTOMER)->user())
        ];
        return $this->sendSuccess(__('auth.login_success'), $response);
    }

    public function me()
    {
        return $this->sendSuccess(__('auth.get_information_success'), new UserResource(auth(GUARD_CUSTOMER)->user()));
    }
    /**
     * @return JsonResponse
     */
    public function logout()
    {
        auth(GUARD_CUSTOMER)->logout();

        return $this->sendSuccess(__('auth.logout_success'), []);
    }
}
