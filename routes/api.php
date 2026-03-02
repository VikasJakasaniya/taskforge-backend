<?php

// ============================================
// routes/api.php
// ============================================
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Tasks\TaskController;
use App\Http\Controllers\Import\ImportController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/request-otp', [AuthController::class, 'requestOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Tasks
    Route::apiResource('tasks', TaskController::class);

    // Imports
    Route::apiResource('imports', ImportController::class)->except(['update']);
});
