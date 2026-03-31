<?php

namespace App\Http\Responses\V1\User;

use Illuminate\Contracts\Support\Responsable;
use App\Http\Resources\V1\UserResource;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;

class UpdateUserResponse implements Responsable
{
    public function __construct(
        private $user
    ) {}

    public function toResponse($request): JsonResponse
    {
        return app(ResponseService::class)->success(
            new UserResource($this->user->load('accounts')),
            'Profile updated successfully'
        );
    }
}