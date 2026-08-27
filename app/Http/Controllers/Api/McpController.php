<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Agent\ToolRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class McpController extends Controller
{
    protected ToolRegistry $toolRegistry;

    public function __construct(ToolRegistry $toolRegistry)
    {
        $this->toolRegistry = $toolRegistry;
    }

    /**
     * MCP SSE Transport Endpoint: GET /mcp/sse
     * Initiates Server-Sent Events stream and delivers the session message post endpoint.
     */
    public function sse(Request $request): StreamedResponse
    {
        $sessionId = (string)Str::uuid();
        $messageEndpoint = url("/mcp/messages?sessionId={$sessionId}");

        return response()->stream(function () use ($messageEndpoint) {
            echo "event: endpoint\n";
            echo "data: " . $messageEndpoint . "\n\n";
            ob_flush();
            flush();

            // Keep SSE alive with heartbeats
            for ($i = 0; $i < 30; $i++) {
                if (connection_aborted()) {
                    break;
                }
                echo ": ping\n\n";
                ob_flush();
                flush();
                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * MCP JSON-RPC Message Handling Endpoint: POST /mcp/messages
     */
    public function handleMessage(Request $request): JsonResponse
    {
        $body = $request->json()->all();

        if (empty($body) || !isset($body['jsonrpc'])) {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $body['id'] ?? null,
                'error' => [
                    'code' => -32600,
                    'message' => 'Invalid JSON-RPC request.',
                ],
            ], 400);
        }

        $method = $body['method'] ?? '';
        $id = $body['id'] ?? null;
        $params = $body['params'] ?? [];
        $companyId = (int)($request->header('X-Company-ID') ?: env('CEO_AGENT_DEFAULT_COMPANY_ID', 1));

        switch ($method) {
            case 'initialize':
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'protocolVersion' => '2024-11-05',
                        'capabilities' => [
                            'tools' => [
                                'listChanged' => false,
                            ],
                        ],
                        'serverInfo' => [
                            'name' => 'laravel_backend',
                            'version' => '1.0.0',
                        ],
                    ],
                ]);

            case 'notifications/initialized':
                return response()->json(['jsonrpc' => '2.0']);

            case 'ping':
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => (object)[],
                ]);

            case 'tools/list':
                $anthropicTools = $this->toolRegistry->getAnthropicTools();
                $mcpTools = array_map(function ($tool) {
                    return [
                        'name' => $tool['name'],
                        'description' => $tool['description'],
                        'inputSchema' => $tool['input_schema'],
                    ];
                }, $anthropicTools);

                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'tools' => $mcpTools,
                    ],
                ]);

            case 'tools/call':
                $toolName = $params['name'] ?? '';
                $toolArguments = $params['arguments'] ?? [];

                $result = $this->toolRegistry->executeTool($toolName, $toolArguments, $companyId);

                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                            ],
                        ],
                        'isError' => !($result['success'] ?? true),
                    ],
                ]);

            default:
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'error' => [
                        'code' => -32601,
                        'message' => "Method '{$method}' not found.",
                    ],
                ]);
        }
    }
}
