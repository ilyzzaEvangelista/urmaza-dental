<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::get('appointments/availability', [AppointmentController::class, 'availability']);
Route::get('appointments/analytics/patients', [AppointmentController::class, 'patientAnalytics']);
Route::get('appointments/week', [AppointmentController::class, 'week']);
Route::get('appointments/calendar-month', [AppointmentController::class, 'calendarMonth']);
Route::get('appointments/patients', [AppointmentController::class, 'patients']);
Route::apiResource('appointments', AppointmentController::class);

Route::get('services', [ServiceController::class, 'index']);
Route::get('services/{service}', [ServiceController::class, 'show']);
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('services', [ServiceController::class, 'store']);
    Route::put('services/{service}', [ServiceController::class, 'update']);
    Route::patch('services/{service}', [ServiceController::class, 'update']);
    Route::delete('services/{service}', [ServiceController::class, 'destroy']);
});
