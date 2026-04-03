<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class AppException extends Exception
{
      /**
       * Render the exception into an HTTP response.
       *
       * @param Request $request
       * @return JsonResponse
       */
      public function render($request): JsonResponse
      {
            return response()->json([
                  'error' => $this->getMessage(),
                  'code' => class_basename($this)
            ], 422);
      }
}
