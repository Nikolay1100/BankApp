<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('v1')->group(function () {
    // --- 1. Authentication ---
    Route::post('/register', [AuthController::class , 'register']);
    Route::post('/login', [AuthController::class , 'login']);

    // --- Protected Routes ---
    Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class , 'logout']);

            // --- 2. User Profile ---
            Route::patch('/users/{user}', [UserController::class , 'update']);

            // --- 3. Financial Operations ---
            Route::post('/users/{user}/deposit', [TransactionController::class , 'deposit']);
            Route::post('/transfers', [TransactionController::class , 'transfer']);
        }
        );    });
