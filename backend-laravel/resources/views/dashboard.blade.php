<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WarnAI · Workspace Intelligence & Local Context Engineering</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <div class="app-container">
        <!-- Header Section -->
        <header class="app-header">
            <div class="brand-section">
                <div class="brand-logo">W</div>
                <div class="brand-info">
                    <h1>WarnAI</h1>
                    <p>Workspace-Aware Repository Normalization & Adaptive Inference AI</p>
                </div>
            </div>
            <div class="header-badges">
                <div id="engine-badge" class="status-badge">
                    <span class="pulse-dot"></span> CONNECTING…
                </div>
                <div id="engine-tag" class="engine-tag">HYBRID BM25 + VECTORS</div>
            </div>
        </header>

        <!-- Top Metrics Row -->
        <section class="metrics-row">
            <div class="metric-box">
                <div class="metric-header">
                    <span class="metric-title">Documents</span>
                    <span class="metric-icon">📄</span>
                </div>
                <div id="metric-docs" class="metric-value">0</div>
                <div class="metric-sub">Normalized repository files</div>
            </div>

            <div class="metric-box">
                <div class="metric-header">
                    <span class="metric-title">Semantic Chunks</span>
                    <span class="metric-icon">🧩</span>
                </div>
                <div id="metric-chunks" class="metric-value">0</div>
                <div class="metric-sub">Vectorized context units</div>
            </div>

            <div class="metric-box">
                <div class="metric-header">
                    <span class="metric-title">Tokens Indexed</span>
                    <span class="metric-icon">🔤</span>
                </div>
                <div id="metric-tokens" class="metric-value">0</div>
                <div class="metric-sub">Pruned & standardized</div>
            </div>

            <div class="metric-box">
                <div class="metric-header">
                    <span class="metric-title">Context Savings</span>
                    <span class="metric-icon">📉</span>
                </div>
                <div id="metric-savings" class="metric-value">0%</div>
                <div class="metric-sub good">Boilerplate token reduction</div>
            </div>
        </section>

        <!-- Navigation Tabs -->
        <nav class="tab-nav">
            <button class="tab-btn active" data-tab="ingest">
                <span>📁</span> Ingestion & Normalization
            </button>
            <button class="tab-btn" data-tab="search">
                <span>🔍</span> Hybrid Retrieval
            </button>
            <button class="tab-btn" data-tab="skeleton">
                <span>🌳</span> Project Skeleton
            </button>
            <button class="tab-btn" data-tab="inference">
                <span>⚡</span> Local LLM Inference
            </button>
            <button class="tab-btn" data-tab="analytics">
                <span>📊</span> Tokenomics & Analytics
            </button>
        </nav>

        <!-- TAB 1: INGESTION -->
        <section id="tab-ingest" class="tab-content active">
            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">Repository Normalization Pipeline</h2>
                    <p class="panel-desc">Upload a ZIP repository, PDF report, DOCX document, or individual code file. Files are stripped of redundant boilerplate, converted to standard Markdown, and indexed locally.</p>
                </div>

                <div id="dropzone" class="dropzone-container">
                    <input type="file" id="file-input" style="display:none;" accept=".zip,.py,.php,.js,.ts,.jsx,.tsx,.java,.go,.rs,.rb,.md,.txt,.yaml,.yml,.json,.toml,.ini,.sh,.sql,.html,.css,.pdf,.docx">
                    <div class="dropzone-icon">📦</div>
                    <div class="dropzone-text">
                        <h3>Drop repository archive or documents here</h3>
                        <p>Supports .ZIP archives, .PDF, .DOCX, and all standard source code languages (max 100 MB)</p>
                    </div>
                    <div id="selected-file-pill" class="file-pill" style="display:none;"></div>
                </div>

                <div class="upload-actions">
                    <label class="checkbox-group">
                        <input type="checkbox" id="async-checkbox">
                        <span>Process asynchronously via Redis Queue worker (recommended for large repositories)</span>
                    </label>
                    <button id="btn-upload" class="btn-primary" disabled>
                        <span>⚡</span> Normalize & Index Repository
                    </button>
                </div>

                <div id="upload-feedback" style="margin-top: 18px;"></div>
            </div>

            <!-- Ingested files table preview -->
            <div id="ingested-files-card" class="panel-card" style="display:none;">
                <div class="panel-header">
                    <h3 class="panel-title">Processed Repository Assets</h3>
                    <p class="panel-desc">File size compression and chunk generation breakdown.</p>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table-simple">
                        <thead>
                            <tr>
                                <th>File Path</th>
                                <th>Raw Size</th>
                                <th>Normalized</th>
                                <th>Reduction</th>
                                <th>Chunks</th>
                                <th>Tokens</th>
                            </tr>
                        </thead>
                        <tbody id="ingested-files-body"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- TAB 2: HYBRID RETRIEVAL -->
        <section id="tab-search" class="tab-content">
            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">Semantic & Lexical Hybrid Retrieval</h2>
                    <p class="panel-desc">Combines BM25 keyword matching with local deep learning embeddings (384d dense vectors) for high-precision context pruning.</p>
                </div>

                <div class="query-presets">
                    <span class="preset-chip" data-query="How does the normalization and boilerplate pruning pipeline work?">💡 Boilerplate pruning</span>
                    <span class="preset-chip" data-query="Where is the local LLM inference gateway implemented?">💡 Inference gateway</span>
                    <span class="preset-chip" data-query="How does BM25 and vector hybrid search calculate scores?">💡 Hybrid scoring formula</span>
                    <span class="preset-chip" data-query="Show the tokenomics and operational analytics metrics aggregation">💡 Tokenomics metrics</span>
                </div>

                <div class="search-bar-wrapper">
                    <input type="text" id="search-query" class="search-input-field" placeholder="Ask a question or enter keywords (e.g. 'Where is authentication handled?')...">
                    <button id="btn-search" class="btn-primary">
                        <span>🔍</span> Search Context
                    </button>
                </div>

                <div class="search-controls">
                    <div class="control-item">
                        <label for="search-limit">Top-K Chunks:</label>
                        <input type="range" id="search-limit" min="1" max="15" value="5">
                        <b id="search-limit-val" style="color:var(--text-main);">5</b>
                    </div>
                    <div class="control-item" style="margin-left: auto;">
                        <span style="color:var(--text-dim);">Retrieval Mode:</span>
                        <b style="color:var(--accent);">45% BM25 + 55% Cosine Vector</b>
                    </div>
                </div>

                <div id="search-results" class="results-container">
                    <div class="empty-state">
                        <div class="empty-icon">⌕</div>
                        <p>Enter a query above to retrieve ranked, pruned context chunks.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- TAB 3: PROJECT SKELETON -->
        <section id="tab-skeleton" class="tab-content">
            <div class="panel-card">
                <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h2 class="panel-title">Project Architecture Skeleton (<code>project_skeleton.md</code>)</h2>
                        <p class="panel-desc">Deterministic hierarchy summary capturing module trees, classes, and exported interfaces.</p>
                    </div>
                    <button id="btn-copy-skeleton" class="btn-secondary">
                        <span>📋</span> Copy Skeleton
                    </button>
                </div>

                <pre id="skeleton-display" class="skeleton-box"># Ingest a repository to view its structural skeleton.</pre>
            </div>
        </section>

        <!-- TAB 4: LOCAL LLM INFERENCE -->
        <section id="tab-inference" class="tab-content">
            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">Local LLM Reasoning & Context Assembly Gateway</h2>
                    <p class="panel-desc">Strict context budgeting dynamically packs project skeleton + top pruned chunks into internal LLM endpoints (Ollama / llama.cpp) with zero cloud dependencies.</p>
                </div>

                <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <input type="text" id="infer-query" class="search-input-field" placeholder="Enter reasoning question for local LLM (e.g. 'Explain the ingestion architecture')...">
                    <select id="infer-model" class="search-input-field">
                        <option value="qwen2.5-coder">Qwen2.5-Coder (Local Default)</option>
                        <option value="mistral">Mistral 7B</option>
                        <option value="llama3.2">Llama 3.2</option>
                        <option value="deepseek-coder">DeepSeek Coder</option>
                    </select>
                </div>

                <div class="search-controls" style="margin-bottom: 20px;">
                    <div class="control-item">
                        <label for="infer-budget">Context Token Budget:</label>
                        <input type="range" id="infer-budget" min="512" max="8192" step="256" value="2048">
                        <b id="infer-budget-val" style="color:var(--accent);">2048 tokens</b>
                    </div>
                    <div class="control-item" style="margin-left: auto; gap: 12px;">
                        <button id="btn-assemble-prompt" class="btn-secondary">
                            <span>👁</span> Inspect Assembled Prompt
                        </button>
                        <button id="btn-run-infer" class="btn-primary">
                            <span>⚡</span> Run Local Inference
                        </button>
                    </div>
                </div>

                <div class="inference-grid">
                    <div>
                        <div style="display:flex; justify-content:space-between; margin-bottom: 8px;">
                            <span style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Assembled Context Prompt</span>
                            <span id="prompt-token-meter" style="font-size:11px; color:var(--primary-light);">0 tokens</span>
                        </div>
                        <pre id="prompt-preview" class="prompt-preview-box">Click 'Inspect Assembled Prompt' to view the unified prompt within the token budget.</pre>
                    </div>

                    <div>
                        <div style="display:flex; justify-content:space-between; margin-bottom: 8px;">
                            <span style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Local LLM Response</span>
                        </div>
                        <div id="inference-answer" class="answer-card">
                            <div class="empty-state" style="padding: 24px 0;">
                                <div class="empty-icon">⚡</div>
                                <p>Answers generated from local grounded context will appear here.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- TAB 5: TOKENOMICS & DATA ANALYTICS -->
        <section id="tab-analytics" class="tab-content">
            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">Tokenomics & Operational Analytics Engine</h2>
                    <p class="panel-desc">Real-time data analytics aggregated via Pandas & NumPy measuring token reduction efficiency, term frequency distributions, and retrieval latency.</p>
                </div>

                <div class="analytics-grid">
                    <!-- TF-IDF Top Topics -->
                    <div class="chart-card">
                        <div class="chart-title">
                            <span>NLP Topic & Term Extraction (TF-IDF)</span>
                            <small style="color:var(--text-dim); font-size:11px;">Corpus Keywords</small>
                        </div>
                        <div id="top-topics-cloud" class="topics-cloud">
                            <span style="color:var(--text-dim);">Loading extracted topics…</span>
                        </div>
                    </div>

                    <!-- Latency Profiler -->
                    <div class="chart-card">
                        <div class="chart-title">
                            <span>Retrieval & Reasoning Latency Profile</span>
                            <small style="color:var(--text-dim); font-size:11px;">NumPy Metrics</small>
                        </div>
                        <table class="table-simple">
                            <tr>
                                <th>Average Retrieval Latency:</th>
                                <td id="lat-avg" style="color:var(--accent); font-weight:700;">0 ms</td>
                            </tr>
                            <tr>
                                <th>p50 Latency (Median):</th>
                                <td id="lat-p50" style="color:var(--success); font-weight:700;">0 ms</td>
                            </tr>
                            <tr>
                                <th>p95 Latency (Tail):</th>
                                <td id="lat-p95" style="color:var(--warning); font-weight:700;">0 ms</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Extension Distribution -->
                    <div class="chart-card" style="grid-column: span 2;">
                        <div class="chart-title">
                            <span>Codebase Composition & Extension Breakdown</span>
                            <small style="color:var(--text-dim); font-size:11px;">Pandas Aggregation</small>
                        </div>
                        <div style="overflow-x: auto;">
                            <table class="table-simple">
                                <thead>
                                    <tr>
                                        <th>Extension</th>
                                        <th>File Count</th>
                                        <th>Normalized Bytes</th>
                                        <th>Tokens Indexed</th>
                                    </tr>
                                </thead>
                                <tbody id="ext-dist-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="/js/app.js"></script>
</body>
</html>
