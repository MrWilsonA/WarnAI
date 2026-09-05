<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use App\Services\ContextAssemblyService;
use App\Jobs\ProcessRepositoryJob;

class WorkspaceController extends Controller
{
    protected ContextAssemblyService $contextService;
    protected string $aiEngineUrl;

    public function __construct(ContextAssemblyService $contextService)
    {
        $this->contextService = $contextService;
        $this->aiEngineUrl = config('services.ai_engine.url', env('AI_ENGINE_URL', 'http://ai-engine:8001'));
    }

    public function index(): View
    {
        return view('dashboard');
    }

    public function health(): JsonResponse
    {
        try {
            $response = Http::timeout(5)->get("{$this->aiEngineUrl}/health");
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Throwable $e) {
            // Service offline
        }

        return response()->json([
            'status' => 'offline',
            'service' => 'warnai-ai-engine',
            'error' => 'AI engine is unreachable at ' . $this->aiEngineUrl,
            'analytics' => [
                'total_files' => 0,
                'estimated_tokens' => 0,
                'size_reduction_pct' => 0,
                'token_savings_pct' => 0,
            ]
        ], 503);
    }

    public function analytics(): JsonResponse
    {
        try {
            $response = Http::timeout(8)->get("{$this->aiEngineUrl}/analytics");
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Throwable $e) {
            // Analytics fallback
        }

        return response()->json([
            'documents' => 0,
            'total_chunks' => 0,
            'queries' => 0,
            'estimated_tokens' => 0,
            'tokenomics' => [
                'total_files' => 0,
                'raw_bytes' => 0,
                'clean_bytes' => 0,
                'size_reduction_pct' => 0,
                'token_savings_pct' => 0,
            ],
            'latency' => [
                'avg_latency_ms' => 0,
                'p50_latency_ms' => 0,
                'p95_latency_ms' => 0,
            ],
            'top_topics' => [],
            'extension_distribution' => [],
        ]);
    }

    public function normalize(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100 MB max
            'async' => 'nullable|boolean',
        ]);

        $uploadedFile = $request->file('file');
        $fileName = $uploadedFile->getClientOriginalName();
        $realPath = $uploadedFile->getRealPath();

        // Jika mode asynchronous dipilih (untuk file repositori besar)
        if ($request->boolean('async')) {
            $tempDir = storage_path('app/shared/uploads');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0775, true);
            }
            $targetPath = $tempDir . '/' . uniqid('repo_', true) . '_' . $fileName;
            copy($realPath, $targetPath);

            ProcessRepositoryJob::dispatch($targetPath, $fileName);

            return response()->json([
                'status' => 'queued',
                'message' => "Repository '{$fileName}' queued for background normalization & indexing.",
                'file_name' => $fileName,
            ]);
        }

        $replace = $request->boolean('replace', false);

        // Mode sinkron
        try {
            $response = Http::timeout(300)
                ->attach('file', fopen($realPath, 'r'), $fileName)
                ->post("{$this->aiEngineUrl}/normalize?replace=" . ($replace ? 'true' : 'false'));

            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to reach AI engine for normalization: ' . $e->getMessage()
            ], 502);
        }
    }

    public function resetWorkspace(): JsonResponse
    {
        try {
            $response = Http::timeout(10)->post("{$this->aiEngineUrl}/workspace/reset");
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to reset workspace: ' . $e->getMessage()], 502);
        }
    }

    public function exportBundle()
    {
        try {
            $response = Http::timeout(60)->get("{$this->aiEngineUrl}/export/bundle");
            if ($response->successful()) {
                return response($response->body(), 200, [
                    'Content-Type' => 'text/markdown',
                    'Content-Disposition' => 'attachment; filename="warnai_context_bundle.md"'
                ]);
            }
            return response()->json(['message' => 'No files in workspace to export.'], 404);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to export bundle: ' . $e->getMessage()], 502);
        }
    }

    public function exportZip()
    {
        try {
            $response = Http::timeout(120)->get("{$this->aiEngineUrl}/export/zip");
            if ($response->successful()) {
                return response($response->body(), 200, [
                    'Content-Type' => 'application/zip',
                    'Content-Disposition' => 'attachment; filename="warnai_normalized_markdown.zip"'
                ]);
            }
            return response()->json(['message' => 'No files in workspace to export.'], 404);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to export zip: ' . $e->getMessage()], 502);
        }
    }

    public function fileMarkdown(Request $request): JsonResponse
    {
        $path = $request->query('path');
        if (!$path) {
            return response()->json(['message' => 'Path parameter is required'], 400);
        }

        try {
            $response = Http::timeout(10)->get("{$this->aiEngineUrl}/file/markdown", ['path' => $path]);
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to get file markdown: ' . $e->getMessage()], 502);
        }
    }

    public function search(Request $request): JsonResponse
    {
        $data = $request->validate([
            'query' => 'required|string|max:500',
            'limit' => 'nullable|integer|min:1|max:25',
            'token_budget' => 'nullable|integer|min:200|max:16384',
        ]);

        try {
            $response = Http::timeout(30)->post("{$this->aiEngineUrl}/search", $data);
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to execute hybrid search: ' . $e->getMessage(),
                'results' => []
            ], 502);
        }
    }

    public function assembleContext(Request $request): JsonResponse
    {
        $data = $request->validate([
            'query' => 'required|string|max:500',
            'token_budget' => 'nullable|integer|min:500|max:8192',
            'limit' => 'nullable|integer|min:1|max:15',
        ]);

        $result = $this->contextService->assembleContext(
            $data['query'],
            $data['token_budget'] ?? 2048,
            $data['limit'] ?? 6
        );

        return response()->json($result);
    }

    public function infer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'query' => 'required|string|max:1000',
            'token_budget' => 'nullable|integer|min:500|max:8192',
            'model' => 'nullable|string|max:50',
        ]);

        $result = $this->contextService->infer(
            $data['query'],
            $data['token_budget'] ?? 2048,
            $data['model'] ?? 'qwen2.5-coder'
        );

        return response()->json($result);
    }

    public function skeleton(): JsonResponse
    {
        try {
            $response = Http::timeout(5)->get("{$this->aiEngineUrl}/skeleton");
            return response()->json([
                'skeleton' => $response->body()
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'skeleton' => '# Project skeleton unavailable.'
            ]);
        }
    }
}
