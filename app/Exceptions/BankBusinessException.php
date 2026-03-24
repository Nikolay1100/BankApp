<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

abstract class BankBusinessException extends Exception
{
      /**
       * Render the exception into an HTTP response.
       * 
       * @param \Illuminate\Http\Request $request
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
