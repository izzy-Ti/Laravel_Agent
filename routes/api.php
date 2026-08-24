<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentToolController;

/*
|--------------------------------------------------------------------------
| AI Agent Tool Endpoints (REST & Function Calling)
|--------------------------------------------------------------------------
*/

// Tool 1: Check Room Availability
Route::post('/agent/check-availability', [AgentToolController::class, 'checkAvailability']);

// Tool 2: Create a Hotel Booking
Route::post('/agent/create-booking', [AgentToolController::class, 'createBooking']);

// Tool 3: Get Booking Details (by ID or Email)
Route::match(['get', 'post'], '/agent/get-booking-details', [AgentToolController::class, 'getBookingDetails']);
Route::get('/agent/bookings/{id}', function ($id, \Illuminate\Http\Request $request, AgentToolController $controller) {
    $request->merge(['booking_id' => $id]);
    return $controller->getBookingDetails($request);
});

// Tool 4: Cancel an Existing Booking
Route::post('/agent/cancel-booking', [AgentToolController::class, 'cancelBooking']);

// Agent Tool Discovery (OpenAI/Anthropic/Gemini JSON Schema)
Route::get('/agent/tools', [AgentToolController::class, 'getToolsSchema']);

// Universal Tool Dispatcher (Execute any tool by name)
Route::post('/agent/execute', [AgentToolController::class, 'executeTool']);

// Agent Reasoning & Chat Simulator
Route::post('/agent/chat', [AgentToolController::class, 'simulateAgentChat']);

// Dashboard Helper: Live Bookings Database Inspector
Route::get('/agent/live-bookings', [AgentToolController::class, 'listAllBookings']);