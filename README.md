# WarnAI

WarnAI is a complete Laravel 11 + FastAPI workspace intelligence product. Laravel is the public web orchestrator and dashboard; the private Python service handles normalization, local NLP, hybrid retrieval, and analytics.

## Product flow

1. Open the Laravel dashboard at `http://localhost`.
2. Upload a ZIP repository or source file.
3. Laravel validates the upload and forwards it to the internal AI engine.
4. The engine removes boilerplate, converts supported source files to Markdown, and indexes them with BM25 plus optional local `all-MiniLM-L6-v2` embeddings.
5. Search questions return ranked context snippets while the dashboard exposes document, query, token, and retrieval-mode analytics.

## Architecture

- `backend-laravel`: Laravel 11 routes, controller, Blade dashboard, validation, CSRF, and internal HTTP orchestration.
- `ai-engine-python`: FastAPI normalization, NLP cleanup, local deep-learning embedding path, hybrid retrieval, and metrics.
- `docker-compose.yml`: PostgreSQL/pgvector, Redis, Python engine, Laravel PHP-FPM, and Nginx.

## Run

```bash
copy .env.example .env
docker compose up --build
```

Then open http://localhost. Runtime secrets, virtual environments, cache, logs, and generated storage are excluded by `.gitignore`.
