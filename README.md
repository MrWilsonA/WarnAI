# WarnAI

WarnAI is a production-ready local-first workspace intelligence service. It ingests source repositories, normalizes code into compact Markdown, indexes the content with deterministic NLP lexical retrieval, and exposes operational analytics without sending data to a cloud API.

## Capabilities

- **Natural language processing:** boilerplate/comment cleanup, whitespace compression, token estimation, and BM25 lexical retrieval over normalized Markdown.
- **Deep learning ready:** the service boundary is designed for local embedding inference; the current engine remains deterministic and lightweight for offline deployments, while the retrieval API can be extended with ONNX/`sentence-transformers` vectors without changing clients.
- **Data analytics:** document count, query volume, estimated token footprint, average document size, processing latency, and reduction-ready normalized output metrics.
- **Professional web UI:** responsive dashboard served from the engine with upload, indexing, search, health, and live metrics.
- **Privacy:** no external API keys, no telemetry, and all processing happens inside the deployment.

## Run locally

```bash
cd ai-engine-python
python -m pip install -r requirements.txt
uvicorn app.main:app --reload --port 8001
```

Open http://localhost:8001. API endpoints: `GET /health`, `POST /normalize` (ZIP or source file), `POST /search`, and `GET /analytics`.

## Run with Docker

```bash
copy .env.example .env
docker compose up --build
```

Runtime files and secrets are excluded through `.gitignore`; only `.env.example` is tracked.
