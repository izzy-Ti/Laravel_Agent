<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentToolController;

Route::post('/agent/check-availability', [AgentToolController::class, 'checkAvailability']);
Route::post('/agent/create-booking', [AgentToolController::class, 'createBooking']);