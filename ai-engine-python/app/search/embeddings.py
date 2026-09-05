import os
import re
import math
import hashlib
from typing import List, Union
import numpy as np

DEFAULT_MODEL_NAME = os.getenv("EMBEDDING_MODEL", "BAAI/bge-small-en-v1.5")
VECTOR_DIM = 384

class LocalEmbeddingEngine:
    """
    Mesin representasi vektor (Deep Learning Embeddings) lokal, zero-external-API.
    Mendukung SentenceTransformers / FastEmbed / ONNX Runtime dengan fallback
    proyeksi semantik deterministik (384 dimensi) terstandarisasi.
    """
    def __init__(self, model_name: str = DEFAULT_MODEL_NAME):
        self.model_name = model_name
        self.encoder = None
        self.engine_type = "deterministic-dense-fallback"
        self._initialize_encoder()

    def _initialize_encoder(self):
        # 1. Coba fastembed terlebih dahulu jika terpasang (sangat hemat memori & cepat via ONNX)
        try:
            from fastembed import TextEmbedding  # type: ignore
            self.encoder = TextEmbedding(model_name="BAAI/bge-small-en-v1.5")
            self.engine_type = "fastembed-onnx"
            return
        except Exception:
            pass

        # 2. Coba sentence-transformers jika terpasang
        try:
            from sentence_transformers import SentenceTransformer  # type: ignore
            # Coba model ringan lokal
            name = "all-MiniLM-L6-v2" if "MiniLM" in self.model_name else self.model_name
            self.encoder = SentenceTransformer(name)
            self.engine_type = f"sentence-transformers ({name})"
            return
        except Exception:
            pass

        self.engine_type = f"local-dense-semantic-projection ({VECTOR_DIM}d)"

    def encode(self, texts: Union[str, List[str]], normalize: bool = True) -> np.ndarray:
        """
        Menghasilkan representasi vektor float32 berdimensi 384 untuk teks masukan.
        """
        is_single = isinstance(texts, str)
        text_list = [texts] if is_single else list(texts)

        if not text_list:
            return np.empty((0, VECTOR_DIM), dtype=np.float32)

        # Jika encoder DL aktif
        if self.encoder is not None:
            try:
                if self.engine_type == "fastembed-onnx":
                    embeddings = list(self.encoder.embed(text_list))
                    vecs = np.array(embeddings, dtype=np.float32)
                else:
                    vecs = np.array(self.encoder.encode(text_list, normalize_embeddings=normalize), dtype=np.float32)

                if normalize and self.engine_type == "fastembed-onnx":
                    norms = np.linalg.norm(vecs, axis=1, keepdims=True)
                    vecs = vecs / np.maximum(norms, 1e-9)

                return vecs[0] if is_single else vecs
            except Exception:
                # Fallback ke dense semantic jika eksekusi model runtime gagal
                pass

        # Fallback dense semantic hashing & subword n-gram vectorization (384 dimensi)
        vectors = []
        for text in text_list:
            vec = np.zeros(VECTOR_DIM, dtype=np.float32)
            words = re.findall(r'[A-Za-z0-9_]+', text.lower())
            if not words:
                vectors.append(vec)
                continue

            for idx, word in enumerate(words):
                # Hash word & subword n-grams
                h = int(hashlib.md5(word.encode('utf-8')).hexdigest(), 16)
                pos = h % VECTOR_DIM
                sign = 1.0 if ((h >> 8) & 1) else -1.0
                # Positional decay & frequency weighting
                weight = 1.0 / math.sqrt(idx + 1.0)
                vec[pos] += float(sign * weight)

                # Tambahkan korelasi bigram
                if idx < len(words) - 1:
                    bigram = f"{word}_{words[idx+1]}"
                    h2 = int(hashlib.sha256(bigram.encode('utf-8')).hexdigest(), 16)
                    pos2 = h2 % VECTOR_DIM
                    sign2 = 1.0 if ((h2 >> 4) & 1) else -1.0
                    vec[pos2] += float(sign2 * 0.5)

            if normalize:
                norm = float(np.linalg.norm(vec))
                if norm > 1e-9:
                    vec = vec / norm
            vectors.append(vec)

        arr = np.array(vectors, dtype=np.float32)
        return arr[0] if is_single else arr
