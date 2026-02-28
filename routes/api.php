<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Core\CondominiumController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware(['auth:api', 'super_admin'])->group(function () {
    Route::get('/condominiums', [CondominiumController::class, 'index']);
    Route::post('/condominiums', [CondominiumController::class, 'store']);
    Route::put('/condominiums/{id}', [CondominiumController::class, 'update']);
    Route::patch('/condominiums/{id}/toggle', [CondominiumController::class, 'toggle']);
});
