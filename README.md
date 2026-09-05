# WarnAI (warn.ai)
### Workspace-Aware Repository Normalization & Adaptive Inference AI

**WarnAI** adalah platform orkestrasi repositori dan optimasi *context engineering* mandiri (*fully local, zero external API key, non-agentic*). Sistem bertindak sebagai lapisan penengah (*middleware*) yang memproses repositori kode atau tumpukan dokumen mentah (PDF, DOCX, kode sumber), membersihkannya dari sintaks redundan, mengonversinya menjadi Markdown hierarkis terstandarisasi, serta memangkas konteks (*context pruning*) secara deterministik menggunakan model *embedding* lokal dan pencarian leksikal berkecepatan tinggi sebelum disajikan ke LLM lokal.

---

## Tech Stack & Core Pillars

| Pilar | Komponen / Pustaka | Fungsi Utama |
| :--- | :--- | :--- |
| **Deep Learning** | FastEmbed / SentenceTransformers / ONNX Runtime | Pembuatan embedding lokal (`BAAI/bge-small-en-v1.5` atau `all-MiniLM-L6-v2`, 384d) tanpa API eksternal |
| **Natural Language Processing (NLP)** | Rank-BM25, PyMuPDF, Python-docx, Tiktoken | Ekstraksi dokumen multi-format, pemangkasan boilerplate, generasi `project_skeleton.md`, dan pencarian leksikal BM25 |
| **Data Analytics** | Pandas, NumPy, Scikit-Learn | Agregasi data reduksi token, analisis topik (TF-IDF), profil latensi (p50, p95), dan distribusi ekstensi |
| **Web Orchestrator & Gateway** | Laravel 11 (PHP 8.3) & Blade | Manajemen proyek, ContextAssemblyService, background job queue, dan dashboard modern |
| **Antrean & Cache** | Redis & Laravel Horizon | Penanganan antrean asynchronous untuk parsing repositori besar |
| **Database & Vector Store** | PostgreSQL 16 + `pgvector` | Penyimpanan data relasional dan indeks vektor berdimensi 384 |
| **Local Inference Server** | Ollama / llama.cpp REST Gateway | Inferensi LLM internal dengan kontrol batas jumlah token input (*context budget*) |
| **Infrastruktur** | Docker & Docker Compose | Kontainerisasi seluruh ekosistem layanan (6 service) |

---

## Directory Structure

```text
WarnAI/
├── docker/
│   ├── laravel/
│   │   ├── Dockerfile
│   │   └── php.ini
│   ├── python-engine/
│   │   ├── Dockerfile
│   ├── nginx/
│   │   └── default.conf
├── backend-laravel/          # Web Orchestrator & Ingestion Gateway
│   ├── app/
│   │   ├── Http/Controllers/WorkspaceController.php
│   │   ├── Jobs/ProcessRepositoryJob.php
│   │   ├── Services/ContextAssemblyService.php
│   │   └── Models/WorkspaceProject.php
│   ├── public/
│   │   ├── css/app.css
│   │   └── js/app.js
│   ├── resources/views/dashboard.blade.php
│   ├── routes/web.php
│   └── composer.json
├── ai-engine-python/         # Document Normalization & Local Embedding Service
│   ├── app/
│   │   ├── normalizer/       # Parser PDF/Docx/Code, Boilerplate Pruning, Skeleton
│   │   │   ├── core.py
│   │   │   └── skeleton.py
│   │   ├── search/           # Hybrid Search: BM25 + Deep Learning Embeddings
│   │   │   ├── bm25.py
│   │   │   ├── embeddings.py
│   │   │   └── engine.py
│   │   ├── analytics/        # Tokenomics, Topic Modeling & Latency Profiling
│   │   │   ├── tokenomics.py
│   │   │   ├── nlp_analytics.py
│   │   │   └── engine.py
│   │   ├── inference/        # Local LLM Context Budgeting Gateway
│   │   │   └── gateway.py
│   │   └── main.py           # FastAPI entrypoint
│   └── requirements.txt
├── docker-compose.yml
├── .env.example
├── WarnAI.md                 # Master Project Specification
└── README.md
```

---

## Cara Menjalankan (Docker Compose)

1. Buat berkas environment dari `.env.example`:
   ```bash
   copy .env.example .env
   ```

2. Jalankan seluruh ekosistem layanan melalui Docker Compose:
   ```bash
   docker compose up --build
   ```

3. Akses antarmuka:
   - **Laravel Web Orchestrator & Dashboard:** [http://localhost](http://localhost)
   - **FastAPI AI Engine Docs & Health:** [http://localhost:8001/docs](http://localhost:8001/docs)

---

## Fitur Utama

1. **Multi-Format Ingestion & Pruning:**
   Mendukung berkas ZIP repositori utuh, dokumen PDF, DOCX, dan lebih dari 20 format kode pemrograman. Menghapus komentar trivial, metadata styling dokumen, dan memadatkan whitespace.

2. **Project Architecture Skeleton (`project_skeleton.md`):**
   Membangun pohon direktori dan katalog antarmuka kelas/fungsi secara otomatis untuk orientasi kontekstual LLM yang hemat token.

3. **Hybrid Context Retrieval:**
   Pencarian semantik berbobot dinamis:
   $$\text{Score} = 0.45 \times \text{BM25}_{\text{norm}} + 0.55 \times \text{CosineSimilarity}$$

4. **Context Budgeting & Local LLM Gateway:**
   Mengontrol alokasi budget token masukan (1k, 2k, 4k, 8k tokens) sebelum dikirim ke model LLM lokal (Ollama / llama.cpp / Qwen2.5-Coder).

5. **Tokenomics & Operational Analytics:**
   Analitik berbasis Pandas & NumPy untuk menghitung rasio kompresi berkas, penghematan token, latensi retrieval (p50, p95), dan ekstraksi topik dominan (TF-IDF).
