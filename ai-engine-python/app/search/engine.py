import re
import time
from typing import List, Dict, Any, Optional
import numpy as np

from .bm25 import build_bm25_index
from .embeddings import LocalEmbeddingEngine

class HybridIndex:
    """
    Mesin Hybrid Search (BM25 Leksikal + Dense Vector Semantic) dengan
    pemangkasan konteks deterministik (*context pruning*) dan pengukuran performa.
    """
    def __init__(self, alpha: float = 0.45, min_score_threshold: float = 0.10):
        self.alpha = alpha  # Bobot skor leksikal vs semantik (alpha * BM25 + (1-alpha) * Semantic)
        self.min_score_threshold = min_score_threshold
        self.chunks: List[Dict[str, Any]] = []
        self.tokenized_corpus: List[List[str]] = []
        self.bm25 = None
        self.vectors: Optional[np.ndarray] = None
        self.queries_count = 0
        self.total_retrieval_ms = 0.0

        # Inisialisasi engine embedding lokal
        self.embedding_engine = LocalEmbeddingEngine()

    def add_document(self, doc_data: Dict[str, Any]):
        """Menambahkan dokumen dan chunk-chunknya ke dalam indeks."""
        file_path = doc_data['path']
        chunks = doc_data.get('chunks', [])

        if not chunks:
            # Jika belum di-chunk, gunakan seluruh markdown sebagai 1 chunk
            raw_text = doc_data.get('markdown', '')
            chunks = [{
                'chunk_id': f"{file_path}#all",
                'file_path': file_path,
                'start_line': 1,
                'end_line': max(1, len(raw_text.splitlines())),
                'text': raw_text,
                'word_count': len(raw_text.split()),
                'estimated_tokens': int(len(raw_text.split()) * 1.33)
            }]

        for c in chunks:
            self.chunks.append(c)
            # Tokenisasi leksikal untuk BM25
            terms = re.findall(r'[A-Za-z0-9_]+', c['text'].lower())
            self.tokenized_corpus.append(terms)

    def rebuild_indices(self):
        """Memperbarui indeks BM25 dan representasi vektor semua chunk."""
        if not self.chunks:
            return

        # 1. Rebuild BM25
        self.bm25 = build_bm25_index(self.tokenized_corpus)

        # 2. Rebuild Vector Embeddings
        texts = [c['text'] for c in self.chunks]
        self.vectors = self.embedding_engine.encode(texts, normalize=True)

    def search(
        self,
        query: str,
        limit: int = 5,
        token_budget: Optional[int] = None
    ) -> List[Dict[str, Any]]:
        """
        Melakukan pencarian hybrid (Leksikal + Semantik) dengan context pruning.
        """
        started = time.perf_counter()
        self.queries_count += 1

        if not self.chunks or self.bm25 is None or self.vectors is None:
            return []

        q_terms = re.findall(r'[A-Za-z0-9_]+', query.lower())
        if not q_terms:
            return []

        # 1. Skor Leksikal BM25
        raw_lex_scores = np.array(self.bm25.get_scores(q_terms), dtype=np.float32)
        max_lex = float(np.max(raw_lex_scores)) if len(raw_lex_scores) > 0 else 0.0
        norm_lex = raw_lex_scores / max_lex if max_lex > 1e-9 else raw_lex_scores

        # 2. Skor Semantik Cosine Similarity
        q_vec = self.embedding_engine.encode(query, normalize=True)
        # Cosine similarity karena kedua vektor ternormalisasi L2 (dot product)
        semantic_scores = np.dot(self.vectors, q_vec)
        # Shift skor kosinus dari [-1, 1] ke rentang non-negatif [0, 1]
        norm_semantic = np.clip((semantic_scores + 1.0) / 2.0, 0.0, 1.0)

        # 3. Skor Gabungan (Hybrid Score)
        combined_scores = (self.alpha * norm_lex) + ((1.0 - self.alpha) * norm_semantic)

        # 4. Urutkan berdasarkan skor tertinggi
        ranked_indices = np.argsort(combined_scores)[::-1]

        results = []
        accumulated_tokens = 0

        for idx in ranked_indices:
            score = float(combined_scores[idx])
            lex_score = float(norm_lex[idx])
            sem_score = float(norm_semantic[idx])

            # Context Pruning: abaikan jika di bawah batas relevansi minimal
            if score < self.min_score_threshold and len(results) >= 1:
                continue

            chunk = self.chunks[idx]
            chunk_tokens = chunk.get('estimated_tokens', int(chunk.get('word_count', 0) * 1.33))

            # Pruning berdasarkan batas budget token jika ditentukan
            if token_budget is not None and (accumulated_tokens + chunk_tokens > token_budget):
                if len(results) >= 1:
                    break

            results.append({
                'chunk_id': chunk['chunk_id'],
                'path': chunk['file_path'],
                'start_line': chunk.get('start_line', 1),
                'end_line': chunk.get('end_line', 1),
                'markdown': chunk['text'],
                'tokens': chunk_tokens,
                'score': round(score, 4),
                'lexical_score': round(lex_score, 4),
                'semantic_score': round(sem_score, 4),
            })

            accumulated_tokens += chunk_tokens
            if len(results) >= limit:
                break

        elapsed_ms = (time.perf_counter() - started) * 1000.0
        self.total_retrieval_ms += elapsed_ms

        return results

    def get_stats(self) -> Dict[str, Any]:
        """Statistik operasional pencarian & retrieval."""
        avg_retrieval_ms = round(self.total_retrieval_ms / max(self.queries_count, 1), 2)
        total_tokens = sum(c.get('estimated_tokens', 0) for c in self.chunks)
        return {
            'total_chunks': len(self.chunks),
            'queries_served': self.queries_count,
            'total_tokens_indexed': total_tokens,
            'avg_chunk_tokens': round(total_tokens / max(len(self.chunks), 1), 1),
            'avg_retrieval_latency_ms': avg_retrieval_ms,
            'retrieval_mode': 'hybrid-bm25-semantic',
            'embedding_engine': self.embedding_engine.engine_type,
            'embedding_dimensions': self.embedding_engine.encode("test").shape[-1]
        }
