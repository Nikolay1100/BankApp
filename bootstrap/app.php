<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Global exception handling for API
        $exceptions->render(function (Throwable $e, Request $request) {

            // Handle only requests going through the /api/* path
            if ($request->is('api/*')) {
                $response = app(ResponseService::class);

                // 1. Validation errors
                if ($e instanceof ValidationException) {
                    return $response->badRequest($e->getMessage(), $e->errors());
                }

                // 2. Access denied (403)
                if ($e instanceof AccessDeniedHttpException) {
                    return $response->forbidden('This action is unauthorized.');
                }

                // 3. Absence of authorization (401)
                if ($e instanceof AuthenticationException) {
                    return $response->unauthorized('Unauthenticated.');
                }

                // 4. Resource not found (404)
                if ($e instanceof NotFoundHttpException) {
                    return $response->notFound('Resource not found.');
                }

                // 5. All other HTTP exceptions
                $code = $e instanceof HttpException ? $e->getStatusCode() : 500;
                $message = $e->getMessage() ?: 'Internal Server Error.';

                return $response->sendJsonResponse(false, $code, $message);
            }
        });

    })->create();
