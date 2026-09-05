import re
from typing import List, Dict, Any
import pandas as pd
import numpy as np

STOP_WORDS = {
    'the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'in', 'to', 'for',
    'of', 'with', 'by', 'from', 'up', 'about', 'into', 'over', 'after', 'this',
    'that', 'it', 'as', 'be', 'are', 'was', 'were', 'not', 'have', 'has', 'had',
    'do', 'does', 'did', 'but', 'if', 'then', 'else', 'when', 'where', 'why',
    'how', 'all', 'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some',
    'such', 'no', 'nor', 'too', 'very', 'can', 'will', 'just', 'should', 'now',
    'return', 'function', 'class', 'import', 'export', 'const', 'let', 'var', 'def'
}

def extract_top_topics(corpus: List[str], top_n: int = 10) -> List[Dict[str, Any]]:
    """
    Ekstraksi topik & istilah dominan dalam repositori menggunakan TF-IDF (Scikit-Learn & Pandas).
    """
    if not corpus:
        return []

    try:
        from sklearn.feature_extraction.text import TfidfVectorizer  # type: ignore

        vectorizer = TfidfVectorizer(
            max_features=100,
            stop_words=list(STOP_WORDS),
            token_pattern=r'(?u)\b[A-Za-z_][A-Za-z0-9_]{2,}\b'
        )
        tfidf_matrix = vectorizer.fit_transform(corpus)
        feature_names = vectorizer.get_feature_names_out()

        # Agregasi rata-rata skor TF-IDF per istilah menggunakan Pandas
        scores = np.asarray(tfidf_matrix.mean(axis=0)).ravel()
        df = pd.DataFrame({'term': feature_names, 'score': scores})
        df = df.sort_values(by='score', ascending=False).head(top_n)

        return [
            {'term': row['term'], 'weight': round(float(row['score']), 4)}
            for _, row in df.iterrows()
        ]
    except Exception:
        # Fallback penghitungan frekuensi term sederhana
        word_counts: Dict[str, int] = {}
        for text in corpus:
            words = re.findall(r'[A-Za-z_][A-Za-z0-9_]{2,}', text.lower())
            for w in words:
                if w not in STOP_WORDS:
                    word_counts[w] = word_counts.get(w, 0) + 1

        df = pd.DataFrame(list(word_counts.items()), columns=['term', 'count'])
        if df.empty:
            return []
        df = df.sort_values(by='count', ascending=False).head(top_n)
        max_count = max(df['count'].max(), 1)
        return [
            {'term': row['term'], 'weight': round(float(row['count'] / max_count), 4)}
            for _, row in df.iterrows()
        ]

def analyze_extension_distribution(files: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
    """
    Menghitung distribusi ekstensi berkas dalam repositori menggunakan Pandas.
    """
    if not files:
        return []

    df = pd.DataFrame(files)
    if 'extension' not in df.columns:
        return []

    grouped = df.groupby('extension').agg(
        count=('path', 'count'),
        total_bytes=('clean_size_bytes', 'sum'),
        total_tokens=('estimated_tokens', 'sum')
    ).reset_index()

    grouped = grouped.sort_values(by='count', ascending=False)
    return grouped.to_dict(orient='records')
