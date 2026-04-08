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
Route::apiResource('appointments', AppointmentController::class);
Route::apiResource('services', ServiceController::class);
