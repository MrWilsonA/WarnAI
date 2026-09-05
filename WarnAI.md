
```markdown
# Master Project Specification: warnai (warn.ai)

## 1. Project Overview & Context

### 1.1 Problem Statement
Penggunaan Model Bahasa Alami (LLM) pada repositori kode atau kumpulan dokumen korporat skala besar kerap terbentur batas *context window*, biaya komputasi tinggi, dan penurunan akurasi penalaran (*context rot/lost-in-the-middle*). Sebagian besar solusi yang ada mengandalkan agen otonom (*agentic workflows*) yang lambat dan mahal, atau API cloud pihak ketiga yang membahayakan privasi data internal.

### 1.2 Solution Concept
**warnai (warn.ai)** — singkatan dari *Workspace-Aware Repository Normalization & Adaptive Inference AI* — adalah platform orkestrasi repositori dan optimasi *context engineering* mandiri (*fully local, zero external API key, non-agentic*). 

Sistem bertindak sebagai lapisan penengah (*middleware*) yang memproses repositori kode atau tumpukan dokumen biner mentah (PDF, DOCX, kode sumber), membersihkannya dari sintaks redundan, mengonversinya menjadi Markdown hierarkis terstandarisasi, serta memangkas konteks (*context pruning*) secara deterministik menggunakan model *embedding* lokal dan pencarian leksikal berkecepatan tinggi sebelum disajikan ke LLM lokal.

---

## 2. Core Modules & Architecture

Sistem dibangun di atas arsitektur *asynchronous event-driven* yang memisahkan lapisan penyajian web (*orchestrator*) dari mesin pemrosesan dokumen intensif (*worker*).

### 2.1 File Ingestion & Markdown Normalization Pipeline
* Menerima repositori berkas (ZIP, folder mentah, dokumen individual).
* Mengonversi semua format biner dan dokumen teks ke format Markdown murni yang hemat token.
* Melakukan pemangkasan boilerplate: menghapus metadata styling, tag visual/bawaan dokumen, komentar kode tak bernilai, serta memadatkan *whitespace*.
* Mempertahankan struktur pohon (*repository skeleton*) yang merangkum daftar modul, relasi kelas, dan antarmuka fungsi utama dalam satu berkas indeks ringkas (`project_skeleton.md`).

### 2.2 Semantic Pruning & Local Indexing Engine (No-Agent, Zero-API)
* Mengabaikan paradigma multi-agent yang lambat; menggunakan *hybrid search* (kombinasi leksikal BM25 dan vektor semantik lokal).
* Pembuatan representasi vektor (*vector embeddings*) berjalan sepenuhnya pada CPU/GPU lokal via ONNX Runtime menggunakan model ringan (`bge-small-en-v1.5` atau `all-MiniLM-L6-v2`).
* Penyimpanan vektor dan indeks teks menggunakan PostgreSQL dengan ekstensi `pgvector`.
* Saat pengguna mengajukan kueri, sistem hanya menarik cuplikan Markdown teratas yang relevan berdasarkan skor gabungan leksikal-vektor, lalu menyusunnya ke dalam *prompt context* terpadu.

### 2.3 Local LLM Inference Gateway
* Mengintegrasikan server inferensi lokal (Ollama / llama.cpp / vLLM lokal) via REST endpoint internal.
* Menyediakan manajemen *context budget*: mengontrol batas jumlah token input secara presisi agar tidak melampaui kapasitas komputasi mesin lokal.

### 2.4 Tokenomics & Operational Analytics
* Menganalisis dan mencatat metrik setiap proses normalisasi dan kueri:
  * Rasio reduksi ukuran berkas (ukuran mentah vs ukuran Markdown terkompresi).
  * Efisiensi token (estimasi penghematan token konteks).
  * Distribusi term/topik yang paling sering dicari dalam repositori.
  * Metrik latensi inferensi dan akurasi retrieval (precision vs noise).
* Dashboard analitik disajikan secara visual untuk monitoring performa sistem.

---

## 3. Technology Stack

| Layer | Komponen / Library | Fungsi Utama |
| :--- | :--- | :--- |
| **Web Orchestrator & API** | Laravel 11+ (PHP 8.3) | Manajemen proyek, autentikasi, API Gateway, dan orkestrasi task |
| **Task Queue & Caching** | Redis & Laravel Horizon | Penanganan antrean asynchronous untuk parsing berkas besar |
| **Document Processing Engine**| Python 3.11 (FastAPI worker) | Ekstraksi dokumen (MarkItDown, PyMuPDF) dan normalisasi Markdown |
| **Local NLP & Deep Learning** | FastEmbed / ONNX Runtime | Pembuatan embedding lokal (`bge-small-en-v1.5`) tanpa ketergantungan API cloud |
| **Lexical Search** | Rank-BM25 / Scikit-Learn | Penyaringan kata kunci cepat deterministik |
| **Database & Vector Store** | PostgreSQL 16 + `pgvector` | Penyimpanan data relasional, metadata proyek, dan indeks vektor |
| **Local Inference Server** | Ollama / llama.cpp HTTP server | Server LLM lokal internal (contoh model: Qwen2.5-Coder / Mistral) |
| **Data Analytics** | Pandas, NumPy, Laravel Charts | Agregasi data reduksi token, latensi retrieval, dan log pemakaian |
| **Infrastructure** | Docker & Docker Compose | Kontainerisasi seluruh ekosistem layanan |

---

## 4. Directory & Service Structure

```text
warnai/
├── docker/
│   ├── laravel/
│   │   ├── Dockerfile
│   │   └── php.ini
│   ├── python-engine/
│   │   ├── Dockerfile
│   │   └── requirements.txt
│   └── nginx/
│       └── default.conf
├── backend-laravel/          # Web Orchestrator & Ingestion Gateway
│   ├── app/
│   │   ├── Http/Controllers/
│   │   ├── Jobs/ProcessRepositoryJob.php
│   │   ├── Services/ContextAssemblyService.php
│   │   └── Models/
│   ├── routes/
│   ├── config/
│   └── composer.json
├── ai-engine-python/         # Document Normalization & Local Embedding Service
│   ├── app/
│   │   ├── normalizer/       # Parser PDF/Docx/Code ke Clean Markdown
│   │   ├── search/           # Hybrid search: BM25 + ONNX Embeddings
│   │   ├── analytics/        # Token calculation & reduction metrics
│   │   └── main.py           # FastAPI entrypoint
│   └── requirements.txt
├── docker-compose.yml
├── .env.example
└── README.md

```

---

## 5. Docker Infrastructure Blueprint

Berikut adalah konfigurasi kontainer terpadu untuk menjalankan seluruh layanan **warnai**:

### 5.1 `docker-compose.yml`

```yaml
services:
  # Database Utama & Vector Store
  postgres:
    image: pgvector/pgvector:pg16
    container_name: warnai_postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: ${DB_DATABASE:-warnai}
      POSTGRES_USER: ${DB_USERNAME:-warnai_user}
      POSTGRES_PASSWORD: ${DB_PASSWORD:-secret}
    ports:
      - "5432:5432"
    volumes:
      - pgdata:/var/lib/postgresql/data
    networks:
      - warnai-network

  # Antrean & In-memory Cache
  redis:
    image: redis:7-alpine
    container_name: warnai_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redisdata:/data
    networks:
      - warnai-network

  # Engine AI & Normalisasi (Python FastAPI)
  ai-engine:
    build:
      context: ./ai-engine-python
      dockerfile: ../docker/python-engine/Dockerfile
    container_name: warnai_ai_engine
    restart: unless-stopped
    environment:
      - DATABASE_URL=postgresql://${DB_USERNAME:-warnai_user}:${DB_PASSWORD:-secret}@postgres:5432/${DB_DATABASE:-warnai}
      - REDIS_URL=redis://redis:6379/0
      - EMBEDDING_MODEL=BAAI/bge-small-en-v1.5
    volumes:
      - ./ai-engine-python:/app
      - shared_storage:/app/storage
    ports:
      - "8001:8001"
    depends_on:
      - postgres
      - redis
    networks:
      - warnai-network

  # Web Orchestrator & API (Laravel)
  laravel-app:
    build:
      context: ./backend-laravel
      dockerfile: ../docker/laravel/Dockerfile
    container_name: warnai_laravel
    restart: unless-stopped
    environment:
      - APP_NAME=warnai
      - APP_ENV=local
      - APP_KEY=${APP_KEY}
      - DB_HOST=postgres
      - DB_DATABASE=${DB_DATABASE:-warnai}
      - DB_USERNAME=${DB_USERNAME:-warnai_user}
      - DB_PASSWORD=${DB_PASSWORD:-secret}
      - REDIS_HOST=redis
      - AI_ENGINE_URL=http://ai-engine:8001
    volumes:
      - ./backend-laravel:/var/www/html
      - shared_storage:/var/www/html/storage/app/shared
    depends_on:
      - postgres
      - redis
      - ai-engine
    networks:
      - warnai-network

  # Background Worker (Laravel Queue / Horizon)
  laravel-worker:
    build:
      context: ./backend-laravel
      dockerfile: ../docker/laravel/Dockerfile
    container_name: warnai_worker
    restart: unless-stopped
    command: php artisan queue:work redis --queue=default,parsing,indexing --verbose --tries=3 --timeout=1200
    volumes:
      - ./backend-laravel:/var/www/html
      - shared_storage:/var/www/html/storage/app/shared
    depends_on:
      - laravel-app
    networks:
      - warnai-network

  # Reverse Proxy
  nginx:
    image: nginx:alpine
    container_name: warnai_nginx
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - ./backend-laravel:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - laravel-app
    networks:
      - warnai-network

networks:
  warnai-network:
    driver: bridge

volumes:
  pgdata:
  redisdata:
  shared_storage:

```

### 5.2 `docker/python-engine/Dockerfile`

```dockerfile
FROM python:3.11-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    curl \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .
RUN pip install --no-cache-dir --upgrade pip && \
    pip install --no-cache-dir -r requirements.txt

COPY . .

EXPOSE 8001

CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8001"]

```

### 5.3 `docker/laravel/Dockerfile`

```dockerfile
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev

RUN docker-php-ext-install pdo pdo_pgsql mbstring zip bcmath pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]

```

---

## 6. End-to-End Workflow

1. **Upload & Job Creation:** Pengguna mengunggah arsip kode/dokumen proyek via UI Laravel. Laravel menyimpan berkas ke `shared_storage` dan mengirim *task* parsing ke antrean Redis.
2. **Normalisasi Asynchronous:** Worker Python membaca berkas, membersihkan struktur biner/kode, dan mengonversi ke Markdown bersih. Sekaligus dihasilkan berkas `project_skeleton.md`.
3. **Indexing:** Potongan teks Markdown (*chunks*) dihitung representasi vektornya menggunakan model lokal dan disimpan ke PostgreSQL `pgvector`, lengkap dengan indeks BM25.
4. **Context Assembly (Kueri Masuk):** Saat pengguna bertanya mengenai proyek, sistem mengambil ringkasan dari skeleton dan mencocokkan potongan Markdown yang paling relevan (*hybrid search*).
5. **Inference & Metrics Logging:** Potongan Markdown yang ringkas disuntikkan ke LLM lokal. Modul Data Analytics mencatat rasio kompresi token, waktu pemrosesan, dan memetakan statistik efisiensi ke dashboard pengguna.

```

```