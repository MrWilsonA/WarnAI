import math
import re
from typing import List, Dict, Any

class PurePythonBM25:
    """
    Implementasi BM25Okapi deterministik dengan k1=1.5 dan b=0.75,
    berjalan cepat tanpa dependensi eksternal jika package rank-bm25 belum terpasang.
    """
    def __init__(self, corpus: List[List[str]], k1: float = 1.5, b: float = 0.75):
        self.k1 = k1
        self.b = b
        self.corpus_size = len(corpus)
        self.avgdl = sum(len(doc) for doc in corpus) / max(self.corpus_size, 1)
        self.doc_freqs: List[Dict[str, int]] = []
        self.idf: Dict[str, float] = {}
        self.doc_len = [len(doc) for doc in corpus]

        df: Dict[str, int] = {}
        for document in corpus:
            frequencies: Dict[str, int] = {}
            for word in document:
                frequencies[word] = frequencies.get(word, 0) + 1
            self.doc_freqs.append(frequencies)
            for word in frequencies.keys():
                df[word] = df.get(word, 0) + 1

        for word, freq in df.items():
            self.idf[word] = math.log((self.corpus_size - freq + 0.5) / (freq + 0.5) + 1.0)

    def get_scores(self, query: List[str]) -> List[float]:
        scores = [0.0] * self.corpus_size
        for term in query:
            if term not in self.idf:
                continue
            term_idf = self.idf[term]
            for idx, doc_freq in enumerate(self.doc_freqs):
                freq = doc_freq.get(term, 0)
                if freq == 0:
                    continue
                num = freq * (self.k1 + 1)
                denom = freq + self.k1 * (1 - self.b + self.b * (self.doc_len[idx] / max(self.avgdl, 1e-9)))
                scores[idx] += term_idf * (num / denom)
        return scores

def build_bm25_index(tokenized_corpus: List[List[str]]):
    """Factory pembuat indeks BM25: mencoba rank_bm25 dulu, lalu PurePythonBM25."""
    try:
        from rank_bm25 import BM25Okapi  # type: ignore
        return BM25Okapi(tokenized_corpus)
    except Exception:
        return PurePythonBM25(tokenized_corpus)
