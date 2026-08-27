<?php

use App\Http\Controllers\Api\ClaudeAgentController;
use App\Http\Controllers\Api\McpController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name', 'Logistics CEO Agent API'),
        'version' => '1.0.0',
        'status' => 'operational',
        'mcp_endpoint' => url('/mcp/sse'),
        'agent_endpoint' => url('/api/agent/run'),
    ]);
});

// Model Context Protocol (MCP) Endpoints
Route::get('/mcp/sse', [McpController::class, 'sse']);
Route::post('/mcp/messages', [McpController::class, 'handleMessage']);

Route::get('/openapi.json', function () {
    $path = public_path('openapi.json');
    if (!file_exists($path)) {
        abort(404, 'openapi.json not found');
    }
    $content = json_decode(file_get_contents($path), true);
    $content['servers'] = [
        ['url' => url('/'), 'description' => 'Current Server']
    ];
    return response()->json($content);
});
