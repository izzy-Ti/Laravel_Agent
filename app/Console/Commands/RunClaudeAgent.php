<?php

namespace App\Console\Commands;

use App\Services\Agent\ClaudeAgentService;
use Illuminate\Console\Command;

class RunClaudeAgent extends Command
{
    protected $signature = 'agent:run 
                            {prompt? : The prompt or task for the Logistics CEO Agent} 
                            {--company=1 : Multi-tenant company ID} 
                            {--max-steps=10 : Maximum tool execution iterations}';

    protected $description = 'Execute an autonomous tool-calling task with the Claude Logistics CEO Agent';

    public function handle(ClaudeAgentService $agentService): int
    {
        $prompt = $this->argument('prompt');
        $companyId = (int)$this->option('company');
        $maxSteps = (int)$this->option('max-steps');

        if (empty($prompt)) {
            $prompt = $this->ask('Enter your instruction for the Titan CEO Agent');
        }

        if (empty($prompt)) {
            $this->error('Prompt cannot be empty.');
            return 1;
        }

        $this->info("🤖 Titan CEO Agent Initializing (Company ID: {$companyId})...");
        $this->line("Prompt: <comment>{$prompt}</comment>\n");

        $onStepCallback = function (array $stepInfo) {
            $tool = $stepInfo['tool'];
            $input = json_encode($stepInfo['input']);
            $this->line("<fg=cyan;options=bold>⚡ [Step {$stepInfo['step']}] Executing Tool:</> <fg=yellow>{$tool}</> with args: {$input}");
        };

        try {
            $startTime = microtime(true);
            $result = $agentService->run(
                prompt: $prompt,
                conversationHistory: [],
                companyId: $companyId,
                maxSteps: $maxSteps,
                onStep: $onStepCallback
            );
            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info("=================== 👔 TITAN CEO AGENT RESPONSE ===================");
            $this->line($result['response']);
            $this->info("===================================================================");
            $this->newLine();

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Tools Executed', $result['tool_calls_count']],
                    ['Steps Taken', $result['steps_taken']],
                    ['Input Tokens', $result['usage']['input_tokens'] ?? 0],
                    ['Output Tokens', $result['usage']['output_tokens'] ?? 0],
                    ['Execution Time', "{$duration}s"],
                    ['Model', $result['model']],
                ]
            );

            return 0;
        } catch (\Throwable $e) {
            $this->error("Agent execution failed: " . $e->getMessage());
            return 1;
        }
    }
}
