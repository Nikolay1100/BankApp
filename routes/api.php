<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\TransactionController;

Route::prefix('v1')->group(function () {
    // User Management
    Route::patch('/users/{user}', [UserController::class , 'update']);

    // Financial Transactions
    Route::post('/users/{user}/deposit', [TransactionController::class , 'deposit']);
    Route::post('/transfers', [TransactionController::class , 'transfer']);
});
