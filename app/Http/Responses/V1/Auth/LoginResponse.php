<?php

namespace App\Http\Responses\V1\Auth;

use Illuminate\Contracts\Support\Responsable;
use App\Http\Resources\V1\UserResource;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;

class LoginResponse implements Responsable
{
    public function __construct(
        private $user,
        private string $token
    ) {}

    public function toResponse($request): JsonResponse
    {
        return app(ResponseService::class)->success([
            'access_token' => $this->token,
            'token_type' => 'Bearer',
            'user' => new UserResource($this->user->load('accounts')),
        ], 'Login successful');
    }
}