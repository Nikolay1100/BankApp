<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class ResponseService
{
    private function responseParams(bool $status, string $message = null, mixed $errors = [], mixed $data = []): array
    {
        return [
            'status' => $status,
            'message' => $message,
            'errors' => (object)$errors,
            'data' => (object)$data,
        ];
    }

    public function sendJsonResponse(bool $status, int $code = 200, string $message = null, mixed $errors = [], mixed $data = []): JsonResponse
    {
        return response()->json(
            $this->responseParams($status, $message, $errors, $data),
            $code
        );
    }

    public function success(mixed $data = [], string $message = 'Success'): JsonResponse
    {
        return $this->sendJsonResponse(true, 200, $message, [], $data);
    }

    public function created(mixed $data = [], string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->sendJsonResponse(true, 201, $message, [], $data);
    }

    public function badRequest(string $message = 'Bad Request', mixed $errors = []): JsonResponse
    {
        return $this->sendJsonResponse(false, 400, $message, $errors, []);
    }

    public function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->sendJsonResponse(false, 401, $message, [], []);
    }

    public function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->sendJsonResponse(false, 403, $message, [], []);
    }

    public function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->sendJsonResponse(false, 404, $message, [], []);
    }
}