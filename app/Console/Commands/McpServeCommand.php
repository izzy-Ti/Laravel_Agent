<?php

namespace App\Console\Commands;

use App\Services\Agent\ToolRegistry;
use Illuminate\Console\Command;

class McpServeCommand extends Command
{
    protected $signature = 'mcp:serve {--company=1 : Default multi-tenant company ID}';

    protected $description = 'Start Model Context Protocol (MCP) Stdio server for Claude Desktop';

    public function handle(ToolRegistry $toolRegistry): int
    {
        $companyId = (int)$this->option('company');
        
        // Discard any previous buffer output so stdio JSON-RPC remains pure
        if (ob_get_level()) {
            ob_end_clean();
        }

        $stdin = fopen('php://stdin', 'r');
        $stdout = fopen('php://stdout', 'w');

        while ($line = fgets($stdin)) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $request = json_decode($line, true);
            if (!is_array($request) || !isset($request['jsonrpc'])) {
                continue;
            }

            $method = $request['method'] ?? '';
            $id = $request['id'] ?? null;
            $params = $request['params'] ?? [];

            $response = null;

            switch ($method) {
                case 'initialize':
                    $response = [
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
                    ];
                    break;

                case 'notifications/initialized':
                    // Acknowledge notification
                    break;

                case 'ping':
                    $response = [
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'result' => (object)[],
                    ];
                    break;

                case 'tools/list':
                    $anthropicTools = $toolRegistry->getAnthropicTools();
                    $mcpTools = array_map(function ($tool) {
                        return [
                            'name' => $tool['name'],
                            'description' => $tool['description'],
                            'inputSchema' => $tool['input_schema'],
                        ];
                    }, $anthropicTools);

                    $response = [
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'result' => [
                            'tools' => $mcpTools,
                        ],
                    ];
                    break;

                case 'tools/call':
                    $toolName = $params['name'] ?? '';
                    $toolArguments = $params['arguments'] ?? [];

                    $result = $toolRegistry->executeTool($toolName, $toolArguments, $companyId);

                    $response = [
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
                    ];
                    break;

                default:
                    if ($id !== null) {
                        $response = [
                            'jsonrpc' => '2.0',
                            'id' => $id,
                            'error' => [
                                'code' => -32601,
                                'message' => "Method '{$method}' not found.",
                            ],
                        ];
                    }
                    break;
            }

            if ($response !== null) {
                fwrite($stdout, json_encode($response, JSON_UNESCAPED_SLASHES) . "\n");
                fflush($stdout);
            }
        }

        fclose($stdin);
        fclose($stdout);

        return 0;
    }
}
