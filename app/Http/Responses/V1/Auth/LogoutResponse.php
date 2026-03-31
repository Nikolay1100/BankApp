<?php

namespace App\Http\Responses\V1\Auth;

use Illuminate\Contracts\Support\Responsable;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;

class LogoutResponse implements Responsable
{
    public function toResponse($request): JsonResponse
    {
        return app(ResponseService::class)->success([], 'Logged out successfully');
    }
}