<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentToolController;
use App\Http\Controllers\Api\ClaudeAgentController;
use App\Http\Controllers\Api\McpController;
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
*/

// =========================================================================
// 1. Autonomous Claude Agent Loop & Tool Registration
// =========================================================================
Route::prefix('agent')->group(function () {
    // Claude Agent Run / Chat Multi-Turn Loop
    Route::post('/run', [ClaudeAgentController::class, 'run']);
    Route::post('/chat', [ClaudeAgentController::class, 'run']);
    Route::get('/tools-schema', [ClaudeAgentController::class, 'listTools']);

    // 1.1 Executive Intelligence Endpoints
    Route::get('/ceo-kpis', [AgentToolController::class, 'getExecutiveKpis']);
    Route::get('/kpis', [AgentToolController::class, 'getExecutiveKpis']);
    Route::get('/critical-exceptions', [AgentToolController::class, 'flagCriticalExceptions']);

    // 1.2 Read Logistics Data Tools
    Route::get('/fleet-status', [AgentToolController::class, 'queryFleetStatus']);
    Route::get('/track', [AgentToolController::class, 'trackShipmentOrDelivery']);
    Route::get('/warehouse-capacity', [AgentToolController::class, 'inspectWarehouseCapacity']);
    Route::get('/customer-financials', [AgentToolController::class, 'getCustomerFinancials']);
    Route::get('/shipments', [AgentToolController::class, 'getShipments']);
    Route::get('/deliveries', [AgentToolController::class, 'getDeliveries']);
    Route::get('/drivers', [AgentToolController::class, 'getDrivers']);
    Route::get('/vehicles', [AgentToolController::class, 'getVehicles']);

    // 1.3 Action Logistics Data Tools
    Route::post('/dispatch', [AgentToolController::class, 'optimizeOrAssignDispatch']);
    Route::post('/optimize-dispatch', [AgentToolController::class, 'optimizeOrAssignDispatch']);
    Route::post('/update-shipment-status', [AgentToolController::class, 'updateShipmentStatus']);
    Route::post('/cancel-shipment', [AgentToolController::class, 'cancelShipment']);
    Route::post('/update-delivery-status', [AgentToolController::class, 'updateDeliveryStatus']);
    Route::post('/cancel-delivery', [AgentToolController::class, 'cancelDelivery']);
});

// =========================================================================
// 2. Model Context Protocol (MCP) Endpoints
// =========================================================================
Route::get('/mcp/sse', [McpController::class, 'sse']);
Route::post('/mcp/messages', [McpController::class, 'handleMessage']);

// =========================================================================
// 3. Core Logistics Domain Resources (Internal Application CRUD)
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
// 4. OpenAPI 3.0.3 Specification Route
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