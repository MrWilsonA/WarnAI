import os
import time
import tempfile
import zipfile
from pathlib import Path
from typing import Optional, List

from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import HTMLResponse, PlainTextResponse
from pydantic import BaseModel

from .normalizer.core import normalize_path
from .normalizer.skeleton import generate_skeleton
from .search.engine import HybridIndex
from .analytics.engine import AnalyticsEngine
from .inference.gateway import generate_inference, build_grounded_prompt

app = FastAPI(
    title="WarnAI Engine",
    version="2.0.0",
    description="Workspace-Aware Repository Normalization & Adaptive Inference AI"
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Inisialisasi state service
hybrid_index = HybridIndex(alpha=0.45, min_score_threshold=0.08)
analytics_engine = AnalyticsEngine()
cached_skeleton = ""
cached_files_summary: List[dict] = []

class SearchQuery(BaseModel):
    query: str
    limit: int = 5
    token_budget: Optional[int] = None

class InferQuery(BaseModel):
    query: str
    token_budget: int = 2048
    model: Optional[str] = "qwen2.5-coder"

class ContextAssembleRequest(BaseModel):
    query: str
    token_budget: int = 2048
    limit: int = 6

@app.get("/health")
def health():
    stats = hybrid_index.get_stats()
    return {
        "status": "ok",
        "service": "warnai-ai-engine",
        "version": "2.0.0",
        "embedding_engine": stats["embedding_engine"],
        "retrieval_mode": stats["retrieval_mode"],
        "chunks_indexed": stats["total_chunks"],
        "analytics": analytics_engine.get_tokenomics_summary()
    }

@app.get("/", response_class=HTMLResponse)
def dashboard_status():
    p = Path(__file__).parent / "dashboard.html"
    if p.exists():
        return p.read_text(encoding="utf-8")
    return "<h1>WarnAI AI Engine Active</h1>"

@app.post("/normalize")
async def normalize(file: UploadFile = File(...)):
    global cached_skeleton, cached_files_summary

    started = time.perf_counter()
    data = await file.read()
    if len(data) > 100 * 1024 * 1024:
        raise HTTPException(413, "File exceeds 100 MB limit")

    processed_files = []
    with tempfile.TemporaryDirectory() as td:
        target_path = Path(td) / (file.filename or "upload")
        target_path.write_bytes(data)

        if zipfile.is_zipfile(target_path):
            extract_dir = Path(td) / "extracted"
            extract_dir.mkdir(parents=True, exist_ok=True)
            with zipfile.ZipFile(target_path) as z:
                z.extractall(extract_dir)
            files_to_process = [p for p in extract_dir.rglob("*") if p.is_file()]
            relative_base = extract_dir
        else:
            files_to_process = [target_path]
            relative_base = Path(td)

        for f in files_to_process:
            # Lewatkan direktori internal git, vendor besar, atau binary tak didukung
            parts = f.relative_to(relative_base).parts
            if any(p in {'.git', 'node_modules', 'vendor', '__pycache__', '.idea', '.vscode'} for p in parts):
                continue

            doc_info = normalize_path(f, relative_to=relative_base)
            if doc_info:
                processed_files.append(doc_info)
                hybrid_index.add_document(doc_info)

    if not processed_files:
        raise HTTPException(400, "No valid or supported source/document files found in upload.")

    # Rebuild indeks BM25 dan representasi embedding
    hybrid_index.rebuild_indices()

    # Generate project_skeleton.md
    cached_skeleton = generate_skeleton(processed_files)
    cached_files_summary = [
        {
            'path': f['path'],
            'raw_size': f['raw_size_bytes'],
            'clean_size': f['clean_size_bytes'],
            'reduction_pct': f['reduction_pct'],
            'chunks': f['total_chunks'],
            'tokens': f['estimated_tokens']
        }
        for f in processed_files
    ]

    elapsed_ms = round((time.perf_counter() - started) * 1000.0, 2)
    analytics_engine.record_ingestion(processed_files, elapsed_ms)

    tokenomics = analytics_engine.get_tokenomics_summary()

    return {
        "files_count": len(processed_files),
        "total_chunks": len(hybrid_index.chunks),
        "elapsed_ms": elapsed_ms,
        "tokenomics": tokenomics,
        "skeleton_preview": cached_skeleton[:600] + ("\n…" if len(cached_skeleton) > 600 else ""),
        "files": cached_files_summary[:20]
    }

@app.post("/search")
def search(payload: SearchQuery):
    started = time.perf_counter()
    limit = max(1, min(payload.limit, 25))
    results = hybrid_index.search(
        query=payload.query,
        limit=limit,
        token_budget=payload.token_budget
    )
    elapsed_ms = round((time.perf_counter() - started) * 1000.0, 2)
    top_score = results[0]['score'] if results else 0.0

    analytics_engine.record_query(
        query=payload.query,
        results_count=len(results),
        top_score=top_score,
        latency_ms=elapsed_ms
    )

    return {
        "query": payload.query,
        "results": results,
        "count": len(results),
        "elapsed_ms": elapsed_ms,
        "retrieval_stats": hybrid_index.get_stats()
    }

@app.post("/context/assemble")
def assemble_context(payload: ContextAssembleRequest):
    """
    Merakit prompt konteks terpadu yang memadukan skeleton repositori dan top chunks
    secara deterministik dalam batas alokasi token budget.
    """
    results = hybrid_index.search(
        query=payload.query,
        limit=payload.limit,
        token_budget=int(payload.token_budget * 0.75)
    )
    prompt = build_grounded_prompt(
        query=payload.query,
        skeleton=cached_skeleton,
        chunks=results,
        token_budget=payload.token_budget
    )
    estimated_tokens = int(len(prompt.split()) * 1.33)

    return {
        "query": payload.query,
        "token_budget": payload.token_budget,
        "assembled_prompt_tokens": estimated_tokens,
        "budget_utilization_pct": round((estimated_tokens / max(payload.token_budget, 1)) * 100, 1),
        "chunks_included": len(results),
        "prompt": prompt,
        "sources": [{"path": r["path"], "score": r["score"]} for r in results]
    }

@app.post("/infer")
async def infer(payload: InferQuery):
    """
    Menjalankan inferensi LLM lokal terpadu dengan konteks yang telah dipangkas.
    """
    results = hybrid_index.search(
        query=payload.query,
        limit=6,
        token_budget=int(payload.token_budget * 0.7)
    )
    res = await generate_inference(
        query=payload.query,
        skeleton=cached_skeleton,
        chunks=results,
        token_budget=payload.token_budget,
        model=payload.model or "qwen2.5-coder"
    )
    return res

@app.get("/analytics")
def get_analytics():
    corpus_samples = [c['text'] for c in hybrid_index.chunks[:100]]
    stats = hybrid_index.get_stats()
    return analytics_engine.get_full_dashboard_analytics(stats, corpus_samples)

@app.get("/skeleton", response_class=PlainTextResponse)
def get_skeleton():
    if not cached_skeleton:
        return "# No repository skeleton generated yet. Ingest a repository first."
    return cached_skeleton
