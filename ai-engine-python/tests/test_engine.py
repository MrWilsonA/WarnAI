import sys
import io

# Ensure utf-8 output on Windows console
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

import time
from pathlib import Path
from fastapi.testclient import TestClient

from app.normalizer.core import normalize_path, prune_boilerplate, chunk_markdown
from app.normalizer.skeleton import generate_skeleton, extract_interfaces
from app.search.engine import HybridIndex
from app.analytics.tokenomics import count_tokens, compute_file_tokenomics
from app.analytics.nlp_analytics import extract_top_topics, analyze_extension_distribution
from app.analytics.engine import AnalyticsEngine
from app.inference.gateway import build_grounded_prompt
from app.main import app

def test_complete_warnai_pipeline():
    # 1. Test Tokenomics Token Counter
    sample_text = "The quick brown fox jumps over the lazy dog. def authenticate_user(username: str, token: str) -> bool: return True"
    tokens = count_tokens(sample_text)
    assert tokens > 0, "Token count should be greater than 0"

    # 2. Test Normalization & Boilerplate Pruning
    dirty_code = """
    // TODO: clean up this later
    <!-- HTML comment -->
    # FIXME: implement this
    def calculate_metrics(data):
        # NOTE: crucial logic
        return len(data)



    """
    clean = prune_boilerplate(dirty_code, '.py')
    assert "calculate_metrics" in clean
    assert "TODO" not in clean

    # 3. Test Normalizer Core & Chunker
    test_file = Path("app/normalizer/core.py")
    doc = normalize_path(test_file)
    assert doc is not None, "Failed to normalize core.py"
    assert doc['total_chunks'] >= 1
    assert doc['raw_size_bytes'] > 0

    # 4. Test Skeleton Generator
    skel = generate_skeleton([doc])
    assert "project_skeleton.md" in skel
    assert "Directory Tree" in skel

    # 5. Test Hybrid Search Engine (BM25 + Dense Vectors)
    index = HybridIndex(alpha=0.45)
    index.add_document(doc)
    index.rebuild_indices()

    search_results = index.search("extract text from pdf", limit=3)
    assert len(search_results) > 0, "Hybrid search should return results"
    top = search_results[0]
    assert top['score'] > 0

    # 6. Test Context Budget Pruning
    pruned_results = index.search("extract text from pdf", limit=10, token_budget=600)
    total_pruned_tokens = sum(r['tokens'] for r in pruned_results)
    assert total_pruned_tokens <= 650

    # 7. Test NLP Topic Analytics (TF-IDF & Pandas)
    corpus = [doc['markdown']]
    topics = extract_top_topics(corpus, top_n=5)
    assert len(topics) > 0

    # 8. Test Prompt Assembly
    prompt = build_grounded_prompt("How does extraction work?", skel, search_results, token_budget=2048)
    assert "project_skeleton.md" in prompt
    assert "USER QUERY:" in prompt

    # 9. Test FastAPI Client
    client = TestClient(app)
    h_resp = client.get("/health")
    assert h_resp.status_code == 200
    assert h_resp.json()['status'] == 'ok'

    analytics_resp = client.get("/analytics")
    assert analytics_resp.status_code == 200

    infer_resp = client.post("/infer", json={"query": "Explain document normalization", "token_budget": 1024})
    assert infer_resp.status_code == 200
    assert "answer" in infer_resp.json()

if __name__ == "__main__":
    test_complete_warnai_pipeline()
    print("=== WARNAI PIPELINE TEST PASSED ===")
