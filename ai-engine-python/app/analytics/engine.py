import time
from typing import List, Dict, Any
import pandas as pd
import numpy as np

from .tokenomics import compute_file_tokenomics
from .nlp_analytics import extract_top_topics, analyze_extension_distribution

class AnalyticsEngine:
    """
    Mesin agregasi analitik data operasional (Pandas & NumPy) untuk WarnAI.
    """
    def __init__(self):
        self.files_history: List[Dict[str, Any]] = []
        self.query_logs: List[Dict[str, Any]] = []
        self.ingestion_start_time = time.time()

    def record_ingestion(self, files: List[Dict[str, Any]], elapsed_ms: float):
        """Mencatat berkas yang telah diproses untuk pelacakan tokenomics."""
        for f in files:
            self.files_history.append({
                'path': f['path'],
                'extension': f.get('extension', '.txt'),
                'doc_type': f.get('doc_type', 'code'),
                'raw_size_bytes': f.get('raw_size_bytes', 0),
                'clean_size_bytes': f.get('clean_size_bytes', 0),
                'estimated_tokens': f.get('estimated_tokens', 0),
                'reduction_pct': f.get('reduction_pct', 0.0),
                'timestamp': time.time()
            })

    def record_query(self, query: str, results_count: int, top_score: float, latency_ms: float):
        """Mencatat kueri pencarian dan retrieval latency."""
        self.query_logs.append({
            'query': query,
            'results_count': results_count,
            'top_score': top_score,
            'latency_ms': latency_ms,
            'timestamp': time.time()
        })

    def get_tokenomics_summary(self) -> Dict[str, Any]:
        """
        Menghitung ringkasan efisiensi tokenomics repositori menggunakan Pandas.
        """
        if not self.files_history:
            return {
                'total_files': 0,
                'raw_bytes': 0,
                'clean_bytes': 0,
                'size_reduction_pct': 0.0,
                'estimated_tokens': 0,
                'tokens_saved_estimate': 0,
                'token_savings_pct': 0.0
            }

        df = pd.DataFrame(self.files_history)
        raw_bytes = int(df['raw_size_bytes'].sum())
        clean_bytes = int(df['clean_size_bytes'].sum())
        est_tokens = int(df['estimated_tokens'].sum())

        bytes_saved = max(0, raw_bytes - clean_bytes)
        size_reduction_pct = round((bytes_saved / max(raw_bytes, 1)) * 100, 2)

        # Estimasi token mentah (sebelum pemangkasan boilerplate) berkorelasi dengan rasio ukuran
        raw_tokens_est = int(est_tokens * (1.0 + (size_reduction_pct / 100.0)))
        tokens_saved = max(0, raw_tokens_est - est_tokens)
        token_savings_pct = round((tokens_saved / max(raw_tokens_est, 1)) * 100, 2)

        return {
            'total_files': len(df),
            'raw_bytes': raw_bytes,
            'clean_bytes': clean_bytes,
            'bytes_saved': bytes_saved,
            'size_reduction_pct': size_reduction_pct,
            'estimated_tokens': est_tokens,
            'raw_tokens_estimate': raw_tokens_est,
            'tokens_saved_estimate': tokens_saved,
            'token_savings_pct': token_savings_pct
        }

    def get_latency_metrics(self) -> Dict[str, Any]:
        """
        Menghitung profil latensi menggunakan NumPy (p50, p95, rata-rata).
        """
        if not self.query_logs:
            return {
                'total_queries': 0,
                'avg_latency_ms': 0.0,
                'p50_latency_ms': 0.0,
                'p95_latency_ms': 0.0
            }

        latencies = np.array([log['latency_ms'] for log in self.query_logs], dtype=np.float32)
        return {
            'total_queries': len(latencies),
            'avg_latency_ms': round(float(np.mean(latencies)), 2),
            'p50_latency_ms': round(float(np.percentile(latencies, 50)), 2),
            'p95_latency_ms': round(float(np.percentile(latencies, 95)), 2),
            'min_latency_ms': round(float(np.min(latencies)), 2),
            'max_latency_ms': round(float(np.max(latencies)), 2)
        }

    def get_full_dashboard_analytics(
        self,
        hybrid_stats: Dict[str, Any],
        corpus_samples: List[str]
    ) -> Dict[str, Any]:
        """Menyajikan agregasi lengkap untuk visualisasi dashboard."""
        tokenomics = self.get_tokenomics_summary()
        latency = self.get_latency_metrics()
        extension_dist = analyze_extension_distribution(self.files_history)
        top_topics = extract_top_topics(corpus_samples, top_n=8)

        return {
            'documents': tokenomics['total_files'],
            'total_chunks': hybrid_stats.get('total_chunks', 0),
            'queries': latency['total_queries'],
            'estimated_tokens': tokenomics['estimated_tokens'],
            'tokenomics': tokenomics,
            'latency': latency,
            'extension_distribution': extension_dist,
            'top_topics': top_topics,
            'deep_learning_embeddings': True,
            'retrieval_mode': hybrid_stats.get('retrieval_mode', 'hybrid-bm25-semantic'),
            'embedding_engine': hybrid_stats.get('embedding_engine', 'local-vector'),
            'avg_chunk_tokens': hybrid_stats.get('avg_chunk_tokens', 0)
        }
