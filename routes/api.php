<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentToolController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Controllers\Api\DeliveryController;

/*
|--------------------------------------------------------------------------
| LOGISTICS CEO AGENT - SOURCE OF TRUTH REST API & TOOL ENDPOINTS
|--------------------------------------------------------------------------
| This API acts as the single source of truth for the Logistics CEO Agent,
| backing all 10 core domain resources, Postgres/Supabase telemetry,
| tool calling discovery, and autonomous agent reasoning.
|
*/

// =========================================================================
// 1. Core Logistics Domain Resources (The 10 Required Entities)
// =========================================================================
Route::apiResource('companies', CompanyController::class);
Route::apiResource('users', UserController::class);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('warehouses', WarehouseController::class);
Route::apiResource('drivers', DriverController::class);
Route::apiResource('vehicles', VehicleController::class);
Route::apiResource('orders', OrderController::class);
Route::apiResource('routes', RouteController::class);
Route::apiResource('shipments', ShipmentController::class);
Route::apiResource('deliveries', DeliveryController::class);

// =========================================================================
// 2. Executive CEO Agent Intelligence & Tool Suite
// =========================================================================
Route::prefix('agent')->group(function () {
    // Executive CEO High-Level KPI Summary (Source of Truth Dashboard)
    Route::get('/ceo-kpis', [AgentToolController::class, 'getExecutiveKpis']);

    // Direct Tool Endpoints
    Route::match(['get', 'post'], '/fleet-status', [AgentToolController::class, 'queryFleetStatus']);
    Route::match(['get', 'post'], '/track', [AgentToolController::class, 'trackShipmentOrDelivery']);
    Route::match(['get', 'post'], '/warehouse-capacity', [AgentToolController::class, 'inspectWarehouseCapacity']);
    Route::post('/optimize-dispatch', [AgentToolController::class, 'optimizeOrAssignDispatch']);
    Route::match(['get', 'post'], '/critical-exceptions', [AgentToolController::class, 'flagCriticalExceptions']);
    Route::match(['get', 'post'], '/customer-financials', [AgentToolController::class, 'getCustomerFinancials']);

    // Universal AI Tool Calling Schema (OpenAI, Gemini, Anthropic Function Calling)
    Route::get('/tools', [AgentToolController::class, 'getToolsSchema']);

    // Universal Tool Dispatcher (Execute any registered tool by name)
    Route::post('/execute', [AgentToolController::class, 'executeTool']);

    // CEO Agent Reasoning & Autonomous Chat Simulator
    Route::post('/chat', [AgentToolController::class, 'simulateAgentChat']);
    Route::post('/ask-ceo', [AgentToolController::class, 'simulateAgentChat']);
});

// =========================================================================
// 3. OpenAPI 3.1 Specification Route
// =========================================================================
Route::get('/openapi.json', function () {
    $path = public_path('openapi.json');
    if (!file_exists($path)) {
        return response()->json(['error' => 'openapi.json not found'], 404);
    }
    $content = json_decode(file_get_contents($path), true);
    $content['servers'] = [
        ['url' => url('/'), 'description' => 'Current Active Server']
    ];
    return response()->json($content);
});