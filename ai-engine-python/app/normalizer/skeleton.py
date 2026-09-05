import re
from typing import List, Dict, Any

def extract_interfaces(content: str, ext: str) -> List[Dict[str, str]]:
    """
    Mengekstrak definisi kelas, fungsi, method, dan endpoint interface
    menggunakan analisis leksikal regex berkecepatan tinggi.
    """
    interfaces = []

    if ext == '.py':
        # Classes
        for m in re.finditer(r'^[ \t]*class\s+([A-Za-z0-9_]+)(?:\(([^)]*)\))?:', content, re.MULTILINE):
            cls_name = m.group(1)
            bases = m.group(2) or ''
            interfaces.append({'type': 'class', 'name': cls_name, 'meta': f"inherits ({bases})" if bases else ''})
        # Functions / methods
        for m in re.finditer(r'^[ \t]*(?:async\s+)?def\s+([A-Za-z0-9_]+)\s*\(([^)]*)\)(?:\s*->\s*([^:]+))?:', content, re.MULTILINE):
            fn_name = m.group(1)
            params = m.group(2)
            ret = m.group(3) or 'None'
            # Filter private magic methods kecuali __init__
            if fn_name.startswith('__') and fn_name != '__init__':
                continue
            interfaces.append({'type': 'function', 'name': fn_name, 'meta': f"({params[:60]}) -> {ret.strip()}"})

    elif ext in {'.php'}:
        # PHP Classes & Interfaces
        for m in re.finditer(r'^[ \t]*(?:final\s+|abstract\s+)?(?:class|interface|trait)\s+([A-Za-z0-9_]+)', content, re.MULTILINE):
            interfaces.append({'type': 'class', 'name': m.group(1), 'meta': ''})
        # PHP Methods / Functions
        for m in re.finditer(r'^[ \t]*(?:public|protected|private)?\s*(?:static\s+)?function\s+([A-Za-z0-9_]+)\s*\(([^)]*)\)', content, re.MULTILINE):
            fn_name = m.group(1)
            params = m.group(2)
            interfaces.append({'type': 'function', 'name': fn_name, 'meta': f"({params[:60]})"})

    elif ext in {'.js', '.ts', '.jsx', '.tsx'}:
        # JS/TS Classes
        for m in re.finditer(r'^[ \t]*(?:export\s+)?class\s+([A-Za-z0-9_]+)', content, re.MULTILINE):
            interfaces.append({'type': 'class', 'name': m.group(1), 'meta': ''})
        # Functions & arrow functions
        for m in re.finditer(r'^[ \t]*(?:export\s+)?(?:async\s+)?function\s+([A-Za-z0-9_]+)\s*\(([^)]*)\)', content, re.MULTILINE):
            interfaces.append({'type': 'function', 'name': m.group(1), 'meta': f"({m.group(2)[:60]})"})
        for m in re.finditer(r'^[ \t]*(?:export\s+)?const\s+([A-Za-z0-9_]+)\s*=\s*(?:async\s*)?\(([^)]*)\)\s*=>', content, re.MULTILINE):
            interfaces.append({'type': 'function', 'name': m.group(1), 'meta': f"({m.group(2)[:60]}) =>"})

    elif ext in {'.go'}:
        for m in re.finditer(r'^type\s+([A-Za-z0-9_]+)\s+struct', content, re.MULTILINE):
            interfaces.append({'type': 'struct', 'name': m.group(1), 'meta': ''})
        for m in re.finditer(r'^func\s+(?:\([^)]+\)\s+)?([A-Za-z0-9_]+)\s*\(([^)]*)\)', content, re.MULTILINE):
            interfaces.append({'type': 'function', 'name': m.group(1), 'meta': f"({m.group(2)[:60]})"})

    return interfaces[:15]  # Batasi per file agar ringkas

def generate_skeleton(files: List[Dict[str, Any]]) -> str:
    """
    Membuat dokumen project_skeleton.md yang ringkas dan padat token,
    merangkum hierarki direktori, antarmuka kelas/fungsi, dan metrik repositori.
    """
    lines = [
        "# Project Skeleton (`project_skeleton.md`)",
        "",
        "> Workspace-Aware Repository Overview & Normalized Structural Index.",
        "",
        "## 1. Directory Tree",
        "```text"
    ]

    # Urutkan paths
    paths = sorted(f['path'] for f in files)
    for p in paths:
        lines.append(f"├── {p}")

    lines.extend([
        "```",
        "",
        "## 2. Modules & Core Interfaces Catalog",
        ""
    ])

    for f in files:
        path = f['path']
        ext = f.get('extension', '')
        raw_content = f.get('markdown', '')
        interfaces = extract_interfaces(raw_content, ext)

        if interfaces:
            lines.append(f"### `{path}`")
            for item in interfaces:
                meta = f" `{item['meta']}`" if item['meta'] else ""
                lines.append(f"- **[{item['type']}]** `{item['name']}`{meta}")
            lines.append("")

    lines.extend([
        "## 3. Ingestion Summary",
        f"- **Total Files Normalized:** {len(files)}",
        f"- **Total Chunks Formed:** {sum(f.get('total_chunks', 1) for f in files)}",
        f"- **Total Estimated Tokens:** {sum(f.get('estimated_tokens', 0) for f in files):,}"
    ])

    return "\n".join(lines)
