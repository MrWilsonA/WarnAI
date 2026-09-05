# warn.ai

Local repository normalization and lexical retrieval engine. Run with `docker compose up --build`, or run the Python engine with `uvicorn app.main:app --reload --port 8001` from `ai-engine-python`. Use `POST /normalize` for ZIP/source ingestion, `POST /search` for retrieval, and `GET /health` for status.
