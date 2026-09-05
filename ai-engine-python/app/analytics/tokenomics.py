import re
from typing import Dict, Any, List

def count_tokens(text: str) -> int:
    """
    Menghitung jumlah token secara presisi menggunakan tiktoken (cl100k_base)
    atau estimasi subword regex jika tiktoken tidak tersedia.
    """
    if not text:
        return 0

    try:
        import tiktoken  # type: ignore
        enc = tiktoken.get_encoding("cl100k_base")
        return len(enc.encode(text))
    except Exception:
        # Fallback kalibrasi subword BPE: rata-rata 1 kata = 1.33 token, ditambah simbol
        words = len(re.findall(r'\w+', text))
        symbols = len(re.findall(r'[^\w\s]', text))
        return max(1, int(words * 1.25 + symbols * 0.4))

def compute_file_tokenomics(
    raw_size_bytes: int,
    clean_size_bytes: int,
    raw_text: str,
    normalized_markdown: str,
    retrieved_chunks_tokens: int = 0
) -> Dict[str, Any]:
    """
    Menghitung metrik efisiensi tokenomics untuk suatu berkas atau kumpulan berkas.
    """
    raw_tokens = count_tokens(raw_text)
    normalized_tokens = count_tokens(normalized_markdown)

    size_savings_bytes = max(0, raw_size_bytes - clean_size_bytes)
    size_reduction_pct = round((size_savings_bytes / max(raw_size_bytes, 1)) * 100, 2)

    token_savings = max(0, raw_tokens - normalized_tokens)
    token_savings_pct = round((token_savings / max(raw_tokens, 1)) * 100, 2)

    # Efisiensi context pruning: seberapa banyak token yang berhasil dihemat
    # saat menyajikan konteks terseleksi dibandingkan mentah
    pruned_savings = max(0, raw_tokens - retrieved_chunks_tokens) if retrieved_chunks_tokens > 0 else token_savings
    pruned_savings_pct = round((pruned_savings / max(raw_tokens, 1)) * 100, 2) if raw_tokens > 0 else 0.0

    return {
        'raw_size_bytes': raw_size_bytes,
        'clean_size_bytes': clean_size_bytes,
        'size_savings_bytes': size_savings_bytes,
        'size_reduction_pct': size_reduction_pct,
        'raw_tokens': raw_tokens,
        'normalized_tokens': normalized_tokens,
        'token_savings': token_savings,
        'token_savings_pct': token_savings_pct,
        'pruned_savings_pct': pruned_savings_pct
    }
