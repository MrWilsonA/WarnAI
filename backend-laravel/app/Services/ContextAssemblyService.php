<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContextAssemblyService
{
    protected string $aiEngineUrl;

    public function __construct()
    {
        $this->aiEngineUrl = config('services.ai_engine.url', env('AI_ENGINE_URL', 'http://ai-engine:8001'));
    }

    /**
     * Merakit konteks prompt terpadu dengan alokasi token budget ketat:
     * Menghubungkan project skeleton dan cuplikan chunk semantik teratas.
     */
    public function assembleContext(string $query, int $tokenBudget = 2048, int $limit = 6): array
    {
        try {
            $response = Http::timeout(20)->post("{$this->aiEngineUrl}/context/assemble", [
                'query' => $query,
                'token_budget' => $tokenBudget,
                'limit' => $limit,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("AI Engine context assembly returned status {$response->status()}");
        } catch (\Throwable $e) {
            Log::error("Context assembly error: " . $e->getMessage());
        }

        return [
            'query' => $query,
            'token_budget' => $tokenBudget,
            'assembled_prompt_tokens' => 0,
            'budget_utilization_pct' => 0,
            'chunks_included' => 0,
            'prompt' => "Prompt assembly unavailable (AI Engine unreachable).",
            'sources' => [],
        ];
    }

    /**
     * Meneruskan kueri ke Local LLM Inference Gateway.
     */
    public function infer(string $query, int $tokenBudget = 2048, string $model = 'qwen2.5-coder'): array
    {
        try {
            $response = Http::timeout(60)->post("{$this->aiEngineUrl}/infer", [
                'query' => $query,
                'token_budget' => $tokenBudget,
                'model' => $model,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("AI Engine inference returned status {$response->status()}");
        } catch (\Throwable $e) {
            Log::error("Inference gateway error: " . $e->getMessage());
        }

        return [
            'answer' => 'Local inference engine is currently unavailable. Please verify that the AI Engine service is active.',
            'model_used' => $model,
            'engine' => 'offline-fallback',
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
            'context_budget' => $tokenBudget,
            'latency_ms' => 0,
            'sources' => [],
        ];
    }
}
