<?php

use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DeviceWebhookController;
use App\Http\Controllers\Api\V1\DietController;
use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\MembershipController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\WorkoutController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Authentication
    Route::post('/auth/login', [AuthController::class, 'login']);

    // IoT & Device Webhook (External device ingestion)
    Route::post('/devices/events', [DeviceWebhookController::class, 'handleEvent']);

    // Authenticated Routes
    Route::middleware(['auth:sanctum', 'tenant', 'subscription.active'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::get('/profile', [AuthController::class, 'me']);

        // Member Management
        Route::get('/members', [MemberController::class, 'index']);
        Route::get('/members/{id}', [MemberController::class, 'show']);
        Route::post('/members', [MemberController::class, 'store']);

        // Memberships & Billing
        Route::get('/memberships', [MembershipController::class, 'index']);
        Route::get('/memberships/{id}', [MembershipController::class, 'show']);
        Route::get('/payments', [PaymentController::class, 'index']);

        // Attendance
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::post('/attendance/qr-scan', [AttendanceController::class, 'qrScan']);
        Route::get('/attendance/summary', [AttendanceController::class, 'todaySummary']);

        // Workouts & Diets
        Route::get('/workouts', [WorkoutController::class, 'index']);
        Route::get('/workouts/{id}', [WorkoutController::class, 'show']);
        Route::get('/diets', [DietController::class, 'index']);
        Route::get('/diets/{id}', [DietController::class, 'show']);
    });
});
