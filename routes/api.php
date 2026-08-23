<?php


use App\Http\Controllers\Api\AgentToolController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/agent/check-availability', [AgentToolController::class, 'checkAvailability']);
    Route::post('/agent/create-booking', [AgentToolController::class, 'createBooking']);
});