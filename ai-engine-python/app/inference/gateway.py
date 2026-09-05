import os
import time
from typing import List, Dict, Any, Optional
import httpx

OLLAMA_ENDPOINT = os.getenv("OLLAMA_URL", "http://localhost:11434/api/generate")
DEFAULT_MODEL = os.getenv("LOCAL_LLM_MODEL", "qwen2.5-coder")

def build_grounded_prompt(
    query: str,
    skeleton: str,
    chunks: List[Dict[str, Any]],
    token_budget: int = 2048
) -> str:
    """
    Menyusun prompt kontekstual terpadu dengan alokasi budget token yang ketat:
    1. Skeleton repositori (struktur arsitektur)
    2. Potongan konteks terseleksi hasil pruning
    3. Instruksi dan kueri pengguna
    """
    prompt_parts = [
        "You are WarnAI, an expert repository reasoning assistant running locally with zero external APIs.",
        "Your answers must be strictly grounded on the provided normalized workspace context and project skeleton.",
        "",
        "### REPOSITORY ARCHITECTURE SKELETON (`project_skeleton.md`):",
        skeleton[:1500] if skeleton else "No skeleton provided.",
        "",
        "### PRUNED REPOSITORY CONTEXT CHUNKS:"
    ]

    current_len = sum(len(p.split()) for p in prompt_parts)
    budget_words = int(token_budget * 0.75)  # Konversi estimasi token ke words

    for i, c in enumerate(chunks, 1):
        chunk_repr = f"\n--- [Source #{i}: {c['path']} (score: {c.get('score', 0)})] ---\n{c['markdown']}\n"
        words_in_chunk = len(chunk_repr.split())
        if current_len + words_in_chunk > budget_words:
            break
        prompt_parts.append(chunk_repr)
        current_len += words_in_chunk

    prompt_parts.extend([
        "",
        "### USER QUERY:",
        query,
        "",
        "### INSTRUCTIONS:",
        "1. Answer the query directly, accurately, and concisely based ONLY on the repository context above.",
        "2. Cite the exact file names and components when explaining the implementation.",
        "3. Provide code snippets if relevant."
    ])

    return "\n".join(prompt_parts)

async def generate_inference(
    query: str,
    skeleton: str,
    chunks: List[Dict[str, Any]],
    token_budget: int = 2048,
    model: str = DEFAULT_MODEL
) -> Dict[str, Any]:
    """
    Menjalankan inferensi ke server LLM lokal (Ollama / llama.cpp),
    dengan fallback respons deterministik grounded jika server belum aktif.
    """
    started = time.perf_counter()
    prompt = build_grounded_prompt(query, skeleton, chunks, token_budget)
    prompt_tokens = int(len(prompt.split()) * 1.33)

    # 1. Coba hubungi Ollama / Local LLM Server internal
    try:
        async with httpx.AsyncClient(timeout=15.0) as client:
            resp = await client.post(
                OLLAMA_ENDPOINT,
                json={
                    "model": model,
                    "prompt": prompt,
                    "stream": False,
                    "options": {"num_ctx": token_budget}
                }
            )
            if resp.status_code == 200:
                data = resp.json()
                elapsed_ms = round((time.perf_counter() - started) * 1000, 2)
                return {
                    "answer": data.get("response", ""),
                    "model_used": model,
                    "engine": "local-ollama",
                    "prompt_tokens": prompt_tokens,
                    "completion_tokens": data.get("eval_count", 0),
                    "total_tokens": prompt_tokens + data.get("eval_count", 0),
                    "context_budget": token_budget,
                    "latency_ms": elapsed_ms,
                    "sources": [{"path": c['path'], "score": c.get('score', 0)} for c in chunks]
                }
    except Exception:
        pass

    # 2. Fallback cerdas jika server LLM lokal belum dinyalakan:
    # Membangun sintesis kontekstual ekstraktif berkecepatan tinggi dari top chunks
    elapsed_ms = round((time.perf_counter() - started) * 1000, 2)

    citations = [c['path'] for c in chunks]
    unique_citations = list(dict.fromkeys(citations))

    answer_lines = [
        f"**Local Grounded Inference:** Based on `{len(chunks)}` semantically pruned chunks across `{len(unique_citations)}` files:\n"
    ]

    for i, c in enumerate(chunks[:3], 1):
        clean_snip = c['markdown'].replace("```", "").strip().splitlines()
        preview = " ".join(clean_snip[:4])[:200]
        answer_lines.append(f"{i}. **{c['path']}** (relevance score `{c.get('score', 0)}`):")
        answer_lines.append(f"   > *{preview}…*\n")

    answer_lines.append(
        f"**Context Budget Status:** Input assembled into `{prompt_tokens}` prompt tokens (budget limit: `{token_budget}`). "
        f"To enable generative LLM answers, ensure local Ollama (`{model}`) is listening at `{OLLAMA_ENDPOINT}`."
    )

    full_answer = "\n".join(answer_lines)
    completion_tokens = int(len(full_answer.split()) * 1.33)

    return {
        "answer": full_answer,
        "model_used": f"{model} (adaptive-local-synthesis)",
        "engine": "warnai-local-grounded-synthesis",
        "prompt_tokens": prompt_tokens,
        "completion_tokens": completion_tokens,
        "total_tokens": prompt_tokens + completion_tokens,
        "context_budget": token_budget,
        "latency_ms": elapsed_ms,
        "sources": [{"path": c['path'], "score": c.get('score', 0)} for c in chunks]
    }
