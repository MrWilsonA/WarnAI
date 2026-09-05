import re
import os
import zipfile
import xml.etree.ElementTree as ET
from pathlib import Path
from typing import List, Dict, Any, Optional

TEXT_EXTENSIONS = {
    '.py', '.php', '.js', '.ts', '.jsx', '.tsx', '.java', '.go', '.rs', '.rb',
    '.md', '.txt', '.yaml', '.yml', '.json', '.toml', '.ini', '.sh', '.bash',
    '.sql', '.html', '.css', '.scss', '.c', '.cpp', '.h', '.hpp', '.cs', '.vue',
    '.xml', '.env.example', '.dockerfile'
}

DOC_EXTENSIONS = {'.pdf', '.docx', '.doc'}

EXT_LANG_MAP = {
    '.py': 'python',
    '.php': 'php',
    '.js': 'javascript',
    '.ts': 'typescript',
    '.jsx': 'jsx',
    '.tsx': 'tsx',
    '.java': 'java',
    '.go': 'go',
    '.rs': 'rust',
    '.rb': 'ruby',
    '.md': 'markdown',
    '.json': 'json',
    '.yaml': 'yaml',
    '.yml': 'yaml',
    '.sql': 'sql',
    '.sh': 'bash',
    '.html': 'html',
    '.css': 'css',
    '.c': 'c',
    '.cpp': 'cpp',
    '.cs': 'csharp',
}

def extract_text_from_pdf(path: Path) -> str:
    """Ekstraksi teks dari berkas PDF menggunakan PyMuPDF atau pypdf fallback."""
    text_content = []
    try:
        import pymupdf  # type: ignore
        doc = pymupdf.open(str(path))
        for page_num in range(len(doc)):
            page = doc[page_num]
            t = page.get_text()
            if t.strip():
                text_content.append(f"### Page {page_num + 1}\n{t.strip()}")
        doc.close()
        if text_content:
            return "\n\n".join(text_content)
    except Exception:
        pass

    # Fallback to pypdf if PyMuPDF not available or fails
    try:
        import pypdf  # type: ignore
        reader = pypdf.PdfReader(str(path))
        for idx, page in enumerate(reader.pages):
            t = page.extract_text() or ""
            if t.strip():
                text_content.append(f"### Page {idx + 1}\n{t.strip()}")
        if text_content:
            return "\n\n".join(text_content)
    except Exception:
        pass

    return ""

def extract_text_from_docx(path: Path) -> str:
    """Ekstraksi teks dari berkas DOCX via python-docx atau native ZIP XML fallback."""
    try:
        import docx  # type: ignore
        doc = docx.Document(str(path))
        paragraphs = [p.text for p in doc.paragraphs if p.text.strip()]
        return "\n\n".join(paragraphs)
    except Exception:
        pass

    # Native fallback extracting word/document.xml from docx zip
    try:
        with zipfile.ZipFile(path) as z:
            if 'word/document.xml' in z.namelist():
                xml_content = z.read('word/document.xml')
                tree = ET.fromstring(xml_content)
                namespaces = {'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'}
                texts = [node.text for node in tree.iter() if node.tag.endswith('t') and node.text]
                return "\n".join(texts)
    except Exception:
        pass

    return ""

def prune_boilerplate(text: str, ext: str) -> str:
    """
    Menghapus sintaks redundan, komentar tak bermakna, tag visual/bawaan,
    dan memadatkan whitespace untuk efisiensi token.
    """
    # 1. Hapus komentar HTML
    text = re.sub(r'<!--[\s\S]*?-->', '', text)

    # 2. Hapus tag script styling inline pada dokumen HTML/SVG
    if ext in {'.html', '.xml', '.svg'}:
        text = re.sub(r'<style[\s\S]*?</style>', '', text, flags=re.IGNORECASE)
        text = re.sub(r'style="[^"]*"', '', text, flags=re.IGNORECASE)

    # 3. Hapus baris komentar TODO/FIXME/HACK/XXX/NOTE trivial
    text = re.sub(r'^[ \t]*(?://|#|/\*|-)\s*(?:TODO|FIXME|HACK|XXX|NOTE)[\s:].*?(?:\*/)?$', '', text, flags=re.MULTILINE | re.IGNORECASE)

    # 4. Hapus komentar kosong
    text = re.sub(r'^[ \t]*(?://|#)[ \t]*$', '', text, flags=re.MULTILINE)

    # 5. Normalisasi indentasi & kompresi baris kosong berlebih
    text = re.sub(r'[ \t]+$', '', text, flags=re.MULTILINE)  # trailing whitespace
    text = re.sub(r'\n{3,}', '\n\n', text)  # maksimal 2 baris baru berurutan

    return text.strip()

def chunk_markdown(
    file_path: str,
    markdown_content: str,
    max_chunk_words: int = 350,
    overlap_words: int = 50
) -> List[Dict[str, Any]]:
    """
    Memecah konten dokumen/kode menjadi semantik chunks dengan metadata baris & token.
    """
    lines = markdown_content.splitlines()
    if not lines:
        return []

    chunks = []
    current_lines: List[str] = []
    current_word_count = 0
    start_line = 1

    for line_idx, line in enumerate(lines, start=1):
        words = line.split()
        current_lines.append(line)
        current_word_count += len(words)

        if current_word_count >= max_chunk_words:
            chunk_text = "\n".join(current_lines).strip()
            if chunk_text:
                chunks.append({
                    'chunk_id': f"{file_path}#L{start_line}-L{line_idx}",
                    'file_path': file_path,
                    'start_line': start_line,
                    'end_line': line_idx,
                    'text': chunk_text,
                    'word_count': len(chunk_text.split()),
                    'estimated_tokens': int(len(chunk_text.split()) * 1.33)
                })

            # Handle overlap
            overlap_lines: List[str] = []
            overlap_count = 0
            for prev_line in reversed(current_lines):
                p_words = len(prev_line.split())
                if overlap_count + p_words <= overlap_words:
                    overlap_lines.insert(0, prev_line)
                    overlap_count += p_words
                else:
                    break

            current_lines = overlap_lines
            current_word_count = overlap_count
            start_line = max(1, line_idx - len(overlap_lines) + 1)

    # Sisanya
    if current_lines:
        chunk_text = "\n".join(current_lines).strip()
        if chunk_text:
            chunks.append({
                'chunk_id': f"{file_path}#L{start_line}-L{len(lines)}",
                'file_path': file_path,
                'start_line': start_line,
                'end_line': len(lines),
                'text': chunk_text,
                'word_count': len(chunk_text.split()),
                'estimated_tokens': int(len(chunk_text.split()) * 1.33)
            })

    return chunks

def normalize_path(path: Path, relative_to: Optional[Path] = None) -> Optional[Dict[str, Any]]:
    """
    Memproses satu berkas: identifikasi tipe, ekstraksi, pemangkasan boilerplate,
    dan pembentukan Markdown terstandarisasi.
    """
    if not path.is_file():
        return None

    ext = path.suffix.lower()
    raw_size = path.stat().st_size
    rel_name = str(path.relative_to(relative_to)) if relative_to else path.name
    rel_name = rel_name.replace('\\', '/')

    raw_text = ""
    doc_type = "code"

    if ext in DOC_EXTENSIONS:
        doc_type = "binary_doc"
        if ext == '.pdf':
            raw_text = extract_text_from_pdf(path)
        elif ext in {'.docx', '.doc'}:
            raw_text = extract_text_from_docx(path)
    elif ext in TEXT_EXTENSIONS or path.name in {'Dockerfile', 'Makefile', 'LICENSE'}:
        doc_type = "source_code" if ext in EXT_LANG_MAP else "text"
        try:
            raw_text = path.read_text(encoding='utf-8', errors='ignore')
        except Exception:
            return None
    else:
        return None

    if not raw_text.strip():
        return None

    # Pembersihan sintaks & boilerplate
    cleaned = prune_boilerplate(raw_text, ext)
    if not cleaned:
        return None

    # Bungkus dalam format Markdown yang representatif
    lang = EXT_LANG_MAP.get(ext, '')
    if doc_type == "binary_doc" or ext == '.md':
        markdown = f"# Document: {rel_name}\n\n{cleaned}"
    else:
        markdown = f"# File: {rel_name}\n\n```{lang}\n{cleaned}\n```"

    clean_size = len(markdown.encode('utf-8'))
    reduction_bytes = max(0, raw_size - clean_size)
    reduction_pct = round((reduction_bytes / max(raw_size, 1)) * 100, 1)

    chunks = chunk_markdown(rel_name, markdown)

    return {
        'path': rel_name,
        'doc_type': doc_type,
        'extension': ext or '.txt',
        'raw_size_bytes': raw_size,
        'clean_size_bytes': clean_size,
        'reduction_pct': reduction_pct,
        'markdown': markdown,
        'chunks': chunks,
        'total_chunks': len(chunks),
        'word_count': len(markdown.split()),
        'estimated_tokens': int(len(markdown.split()) * 1.33)
    }
