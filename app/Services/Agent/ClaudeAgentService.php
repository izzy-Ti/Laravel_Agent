<?php

namespace App\Services\Agent;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeAgentService
{
    protected ToolRegistry $toolRegistry;
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $maxTokens;
    protected string $systemPrompt;

    public function __construct(ToolRegistry $toolRegistry)
    {
        $this->toolRegistry = $toolRegistry;
        $this->apiKey = config('services.anthropic.key') ?? env('ANTHROPIC_API_KEY', '');
        $this->baseUrl = rtrim(config('services.anthropic.base_url') ?? env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'), '/');
        $this->model = env('ANTHROPIC_MODEL', 'claude-3-7-sonnet-20250219');
        $this->maxTokens = (int)env('ANTHROPIC_MAX_TOKENS', 4096);
        $this->systemPrompt = $this->loadSystemPrompt();
    }

    /**
     * Load system prompt from markdown file with fallback
     */
    protected function loadSystemPrompt(): string
    {
        $promptPath = base_path('prompts/ceo_agent_system_prompt.md');
        if (File::exists($promptPath)) {
            return File::get($promptPath);
        }

        $rootPromptPath = base_path('system_prompt.md');
        if (File::exists($rootPromptPath)) {
            return File::get($rootPromptPath);
        }

        return "You are Titan CEO, an autonomous AI Chief Executive Officer Agent for enterprise freight logistics. Use your tools to analyze KPIs, detect bottlenecks, track consignments, and optimize dispatches.";
    }

    /**
     * Run the Claude Agent Tool-Use Loop
     *
     * @param string $prompt User prompt/command
     * @param array $conversationHistory Existing message history (list of role/content arrays)
     * @param int $companyId Multi-tenant company context
     * @param int $maxSteps Maximum tool loop steps
     * @param callable|null $onStep Callback for step events
     * @return array Result containing final response, messages transcript, tool traces, and usage
     */
    public function run(
        string $prompt,
        array $conversationHistory = [],
        int $companyId = 1,
        int $maxSteps = 10,
        ?callable $onStep = null
    ): array {
        if (empty($this->apiKey)) {
            throw new Exception("ANTHROPIC_API_KEY is not configured in environment or config.");
        }

        // Initialize messages
        $messages = $conversationHistory;
        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        $tools = $this->toolRegistry->getAnthropicTools();
        $toolCallsMade = [];
        $totalUsage = ['input_tokens' => 0, 'output_tokens' => 0];
        $currentStep = 0;
        $finalTextResponse = '';

        while ($currentStep < $maxSteps) {
            $currentStep++;

            // Call Claude API (Anthropic Messages API)
            $responsePayload = $this->callAnthropicMessagesApi($messages, $tools);

            if (isset($responsePayload['usage'])) {
                $totalUsage['input_tokens'] += $responsePayload['usage']['input_tokens'] ?? 0;
                $totalUsage['output_tokens'] += $responsePayload['usage']['output_tokens'] ?? 0;
            }

            $contentBlocks = $responsePayload['content'] ?? [];
            $stopReason = $responsePayload['stop_reason'] ?? 'end_turn';

            // Extract text and tool_use blocks
            $textParts = [];
            $toolUseBlocks = [];

            foreach ($contentBlocks as $block) {
                if (($block['type'] ?? '') === 'text') {
                    $textParts[] = $block['text'] ?? '';
                } elseif (($block['type'] ?? '') === 'tool_use') {
                    $toolUseBlocks[] = $block;
                }
            }

            if (!empty($textParts)) {
                $finalTextResponse = implode("\n", $textParts);
            }

            // Append assistant turn to messages
            $messages[] = [
                'role' => 'assistant',
                'content' => $contentBlocks,
            ];

            // If no tools were invoked or turn is ended, terminate loop
            if (empty($toolUseBlocks) || $stopReason === 'end_turn' || $stopReason === 'stop_sequence') {
                break;
            }

            // Execute all requested tools and accumulate tool_result blocks
            $toolResultBlocks = [];

            foreach ($toolUseBlocks as $toolUse) {
                $toolUseId = $toolUse['id'] ?? ('toolu_' . uniqid());
                $toolName = $toolUse['name'] ?? '';
                $toolInput = $toolUse['input'] ?? [];

                Log::info("Claude Agent Tool Invocation [Step {$currentStep}]: {$toolName}", [
                    'tool_id' => $toolUseId,
                    'input' => $toolInput,
                    'company_id' => $companyId,
                ]);

                // Execute via Tool Registry
                $toolOutput = $this->toolRegistry->executeTool($toolName, $toolInput, $companyId);

                $toolCallsMade[] = [
                    'step' => $currentStep,
                    'id' => $toolUseId,
                    'name' => $toolName,
                    'input' => $toolInput,
                    'output' => $toolOutput,
                ];

                if (is_callable($onStep)) {
                    $onStep([
                        'step' => $currentStep,
                        'type' => 'tool_executed',
                        'tool' => $toolName,
                        'input' => $toolInput,
                        'output' => $toolOutput,
                    ]);
                }

                $toolResultBlocks[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $toolUseId,
                    'content' => json_encode($toolOutput, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            // Append tool results as user turn
            $messages[] = [
                'role' => 'user',
                'content' => $toolResultBlocks,
            ];
        }

        return [
            'success' => true,
            'response' => $finalTextResponse,
            'tool_calls_count' => count($toolCallsMade),
            'tool_calls' => $toolCallsMade,
            'steps_taken' => $currentStep,
            'messages' => $messages,
            'usage' => $totalUsage,
            'company_id' => $companyId,
            'model' => $this->model,
        ];
    }

    /**
     * Call Anthropic Messages API endpoint via HTTP
     */
    protected function callAnthropicMessagesApi(array $messages, array $tools): array
    {
        $endpoint = $this->baseUrl;
        if (!str_ends_with($endpoint, '/messages') && !str_ends_with($endpoint, '/v1/messages')) {
            $endpoint .= str_ends_with($endpoint, '/v1') ? '/messages' : '/v1/messages';
        }

        $payload = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'system' => $this->systemPrompt,
            'messages' => $messages,
            'tools' => $tools,
        ];

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
            'accept' => 'application/json',
        ])
        ->timeout(120)
        ->post($endpoint, $payload);

        if (!$response->successful()) {
            $errorBody = $response->body();
            Log::error("Anthropic API Error ({$response->status()}): " . $errorBody);
            throw new Exception("Anthropic API call failed with status {$response->status()}: {$errorBody}");
        }

        return $response->json();
    }
}
