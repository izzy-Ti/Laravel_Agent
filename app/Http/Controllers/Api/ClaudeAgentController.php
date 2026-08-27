<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Agent\ClaudeAgentService;
use App\Services\Agent\ToolRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClaudeAgentController extends Controller
{
    protected ClaudeAgentService $agentService;
    protected ToolRegistry $toolRegistry;

    public function __construct(ClaudeAgentService $agentService, ToolRegistry $toolRegistry)
    {
        $this->agentService = $agentService;
        $this->toolRegistry = $toolRegistry;
    }

    /**
     * Resolve Multi-Tenant Company ID
     */
    protected function resolveCompanyId(Request $request): int
    {
        return $request->user()?->company_id 
            ?? (int)($request->header('X-Company-ID') ?: $request->input('company_id') ?: env('CEO_AGENT_DEFAULT_COMPANY_ID', 1));
    }

    /**
     * Run Claude Agent with prompt and tool execution loop
     */
    public function run(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string',
            'messages' => 'nullable|array',
            'max_steps' => 'nullable|integer|min:1|max:20',
        ]);

        $prompt = $request->input('prompt');
        $history = $request->input('messages', []);
        $maxSteps = (int)$request->input('max_steps', 10);
        $companyId = $this->resolveCompanyId($request);

        try {
            $result = $this->agentService->run(
                prompt: $prompt,
                conversationHistory: $history,
                companyId: $companyId,
                maxSteps: $maxSteps
            );

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all registered tool schemas
     */
    public function listTools(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'tools' => $this->toolRegistry->getAnthropicTools(),
        ]);
    }
}
