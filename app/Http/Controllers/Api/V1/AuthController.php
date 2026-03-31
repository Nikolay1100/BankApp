<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Http\Responses\V1\Auth\RegisterResponse;
use App\Http\Responses\V1\Auth\LoginResponse;
use App\Http\Responses\V1\Auth\LogoutResponse;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Registers a new user.
     */
    public function register(RegisterRequest $request): RegisterResponse
    {
        [$user, $token] = $this->authService->register($request->validated());

        return new RegisterResponse($user, $token);
    }

    /**
     * Authenticates a user.
     */
    public function login(LoginRequest $request): LoginResponse
    {
        [$user, $token] = $this->authService->login(
            $request->email,
            $request->password
        );

        return new LoginResponse($user, $token);
    }

    /**
     * Logs out the current user.
     */
    public function logout(Request $request): LogoutResponse
    {
        $this->authService->logout($request->user());

        return new LogoutResponse();
    }
}