<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessRepositoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;
    public int $tries = 3;

    protected string $filePath;
    protected string $originalFileName;

    public function __construct(string $filePath, string $originalFileName)
    {
        $this->filePath = $filePath;
        $this->originalFileName = $originalFileName;
        $this->onQueue('parsing');
    }

    public function handle(): void
    {
        Log::info("Starting background repository ingestion for: {$this->originalFileName}");

        if (!file_exists($this->filePath)) {
            Log::error("File not found at: {$this->filePath}");
            return;
        }

        $aiEngineUrl = config('services.ai_engine.url', env('AI_ENGINE_URL', 'http://ai-engine:8001'));

        try {
            $response = Http::timeout(600)
                ->attach('file', fopen($this->filePath, 'r'), $this->originalFileName)
                ->post("{$aiEngineUrl}/normalize");

            if ($response->successful()) {
                $data = $response->json();
                Log::info("Repository normalized successfully: {$data['files_count']} files, {$data['total_chunks']} chunks in {$data['elapsed_ms']} ms.");
            } else {
                Log::error("Failed to normalize repository in background job. Status: {$response->status()}");
            }
        } catch (\Throwable $e) {
            Log::error("Exception processing repository job: " . $e->getMessage());
            throw $e;
        } finally {
            if (file_exists($this->filePath)) {
                @unlink($this->filePath);
            }
        }
    }
}
