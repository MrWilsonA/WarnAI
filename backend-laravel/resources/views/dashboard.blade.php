<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WarnAI · Workspace-Aware Repository Normalization & Adaptive Inference</title>
    
    <!-- Modern Typography: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icon Library -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Application Stylesheet -->
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <div class="app-container">
        <!-- Top Navigation / Brand Header -->
        <header class="app-header">
            <div class="brand-section">
                <div class="brand-logo-frame">
                    <img src="/images/logo.png" alt="WarnAI Emblem" class="brand-logo-img">
                </div>
                <div class="brand-info">
                    <div class="brand-title-row">
                        <h1>WarnAI</h1>
                        <span class="version-badge">v2.0-CORE</span>
                        <span class="arch-badge"><i data-lucide="shield-check"></i> AIR-GAPPED</span>
                    </div>
                    <p class="brand-subtitle">Workspace-Aware Repository Normalization & Adaptive Inference AI</p>
                </div>
            </div>

            <div class="header-badges">
                <div class="telemetry-pill">
                    <i data-lucide="cpu"></i>
                    <span>ONNX 384d</span>
                </div>
                <div id="engine-tag" class="engine-tag">
                    <i data-lucide="git-merge"></i>
                    <span>HYBRID BM25 + VECTORS</span>
                </div>
                <div id="engine-badge" class="status-badge">
                    <span class="pulse-dot"></span>
                    <span class="status-label">CONNECTING…</span>
                </div>
            </div>
        </header>

        <!-- Top Telemetry & Metrics Row -->
        <section class="metrics-row">
            <div class="metric-box">
                <div class="metric-header">
                    <span class="metric-title">Documents</span>
                    <div class="metric-icon-wrap">
                        <i data-lucide="file-code-2"></i>
                    </div>
                </div>
                <div id="metric-docs" class="metric-value">0</div>
                <div class="metric-footer">
                    <span class="metric-badge"><i data-lucide="check-circle-2"></i> Normalized</span>
                    <span class="metric-sub">repository assets</span>
                </div>
            </div>

            <div class="metric-box">
                <div class="metric-header">
                    <span class="metric-title">Semantic Chunks</span>
                    <div class="metric-icon-wrap">
                        <i data-lucide="boxes"></i>
                    </div>
                </div>
                <div id="metric-chunks" class="metric-value">0</div>
                <div class="metric-footer">
                    <span class="metric-badge"><i data-lucide="network"></i> Vectorized</span>
                    <span class="metric-sub">384d embeddings</span>
                </div>
            </div>

            <div class="metric-box">
                <div class="metric-header">
                    <span class="metric-title">Tokens Indexed</span>
                    <div class="metric-icon-wrap">
                        <i data-lucide="binary"></i>
                    </div>
                </div>
                <div id="metric-tokens" class="metric-value">0</div>
                <div class="metric-footer">
                    <span class="metric-badge"><i data-lucide="scissors"></i> Pruned</span>
                    <span class="metric-sub">clean prompt tokens</span>
                </div>
            </div>

            <div class="metric-box highlight-amber">
                <div class="metric-header">
                    <span class="metric-title">Context Savings</span>
                    <div class="metric-icon-wrap amber">
                        <i data-lucide="gauge"></i>
                    </div>
                </div>
                <div id="metric-savings" class="metric-value amber">0%</div>
                <div class="metric-footer">
                    <span class="metric-badge amber"><i data-lucide="trending-down"></i> Efficiency</span>
                    <span class="metric-sub">boilerplate eliminated</span>
                </div>
            </div>
        </section>

        <!-- Workstation Segmented Navigation Tabs -->
        <nav class="tab-nav">
            <button class="tab-btn active" data-tab="ingest">
                <i data-lucide="hard-drive-download"></i>
                <span>Ingestion & Normalization</span>
            </button>
            <button class="tab-btn" data-tab="search">
                <i data-lucide="search-code"></i>
                <span>Hybrid Retrieval</span>
            </button>
            <button class="tab-btn" data-tab="skeleton">
                <i data-lucide="git-fork"></i>
                <span>Project Skeleton</span>
            </button>
            <button class="tab-btn" data-tab="inference">
                <i data-lucide="sparkles"></i>
                <span>Local LLM Inference</span>
            </button>
            <button class="tab-btn" data-tab="analytics">
                <i data-lucide="bar-chart-3"></i>
                <span>Tokenomics & Analytics</span>
            </button>
        </nav>

        <!-- TAB 1: INGESTION -->
        <section id="tab-ingest" class="tab-content active">
            <div class="panel-card">
                <div class="panel-header">
                    <div class="panel-header-info">
                        <h2 class="panel-title"><i data-lucide="file-archive"></i> Repository Normalization Pipeline</h2>
                        <p class="panel-desc">Ingest a full codebase (.ZIP), technical specification (.PDF / .DOCX), or individual source file. Redundant boilerplate, lockfiles, and vendored code are stripped, standardized to clean Markdown, and indexed into local vector storage.</p>
                    </div>
                    <div class="panel-badge-group">
                        <span class="format-tag">.ZIP</span>
                        <span class="format-tag">.PY</span>
                        <span class="format-tag">.PHP</span>
                        <span class="format-tag">.TS / .JS</span>
                        <span class="format-tag">.GO</span>
                        <span class="format-tag">.PDF / .DOCX</span>
                    </div>
                </div>

                <div id="dropzone" class="dropzone-container">
                    <input type="file" id="file-input" style="display:none;" accept=".zip,.py,.php,.js,.ts,.jsx,.tsx,.java,.go,.rs,.rb,.md,.txt,.yaml,.yml,.json,.toml,.ini,.sh,.sql,.html,.css,.pdf,.docx">
                    <div class="dropzone-content">
                        <div class="dropzone-icon-ring">
                            <i data-lucide="upload-cloud" class="dropzone-icon"></i>
                        </div>
                        <div class="dropzone-text">
                            <h3>Drop codebase archive or technical files here</h3>
                            <p>or <span class="dropzone-browse">browse filesystem</span> · Maximum archive payload up to 100 MB</p>
                        </div>
                        <div id="selected-file-pill" class="file-pill" style="display:none;"></div>
                    </div>
                </div>

                <div class="upload-actions">
                    <label class="toggle-checkbox">
                        <input type="checkbox" id="async-checkbox">
                        <span class="toggle-switch"></span>
                        <span class="toggle-label">
                            <strong>Asynchronous Background Worker</strong>
                            <small>Queue parsing job through Redis + Database worker for massive repositories</small>
                        </span>
                    </label>
                    
                    <button id="btn-upload" class="btn-primary" disabled>
                        <i data-lucide="zap"></i>
                        <span>Normalize & Index Repository</span>
                    </button>
                </div>

                <div id="upload-feedback" class="upload-feedback-area"></div>
            </div>

            <!-- Ingested Files Table Preview -->
            <div id="ingested-files-card" class="panel-card" style="display:none; margin-top: 24px;">
                <div class="panel-header">
                    <div class="panel-header-info">
                        <h3 class="panel-title"><i data-lucide="layers"></i> Processed Repository Assets</h3>
                        <p class="panel-desc">File-by-file boilerplate compression, chunking boundaries, and token metrics.</p>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="table-simple">
                        <thead>
                            <tr>
                                <th><i data-lucide="file"></i> File Path</th>
                                <th>Raw Size</th>
                                <th>Clean Size</th>
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
                    <div class="panel-header-info">
                        <h2 class="panel-title"><i data-lucide="search"></i> High-Precision Hybrid Retrieval</h2>
                        <p class="panel-desc">Execute dual-engine context queries combining BM25 keyword matching (term precision) with local ONNX 384d semantic vectors (conceptual intent) for optimal LLM context packing.</p>
                    </div>
                </div>

                <div class="query-presets">
                    <span class="preset-chip" data-query="How does the normalization and boilerplate pruning pipeline work?">
                        <i data-lucide="terminal"></i> Boilerplate pruning
                    </span>
                    <span class="preset-chip" data-query="Where is the local LLM inference gateway implemented?">
                        <i data-lucide="terminal"></i> Inference gateway
                    </span>
                    <span class="preset-chip" data-query="How does BM25 and vector hybrid search calculate scores?">
                        <i data-lucide="terminal"></i> Hybrid scoring formula
                    </span>
                    <span class="preset-chip" data-query="Show the tokenomics and operational analytics metrics aggregation">
                        <i data-lucide="terminal"></i> Tokenomics metrics
                    </span>
                </div>

                <div class="search-bar-wrapper">
                    <div class="search-input-box">
                        <i data-lucide="search" class="search-prefix-icon"></i>
                        <input type="text" id="search-query" class="search-input-field" placeholder="Ask a question or enter code symbols (e.g. 'Where is authentication handled?')...">
                        <span class="kbd-badge">↵ Enter</span>
                    </div>
                    <button id="btn-search" class="btn-primary">
                        <i data-lucide="sparkles"></i>
                        <span>Search Context</span>
                    </button>
                </div>

                <div class="search-controls">
                    <div class="control-item">
                        <label for="search-limit"><i data-lucide="sliders"></i> Top-K Chunks:</label>
                        <input type="range" id="search-limit" min="1" max="15" value="5">
                        <b id="search-limit-val" class="slider-val">5</b>
                    </div>
                    <div class="control-item score-formula-badge">
                        <i data-lucide="blend"></i>
                        <span>Scoring: <strong>45% BM25 + 55% Cosine Vector</strong></span>
                    </div>
                </div>

                <div id="search-results" class="results-container">
                    <div class="empty-state">
                        <div class="empty-icon-wrap">
                            <i data-lucide="search"></i>
                        </div>
                        <h4>Awaiting Query Execution</h4>
                        <p>Enter a query or select a preset above to retrieve ranked, pruned context chunks with score attribution.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- TAB 3: PROJECT SKELETON -->
        <section id="tab-skeleton" class="tab-content">
            <div class="panel-card">
                <div class="panel-header" style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div class="panel-header-info">
                        <h2 class="panel-title"><i data-lucide="git-fork"></i> Project Architecture Skeleton</h2>
                        <p class="panel-desc">Deterministic hierarchy summary capturing module trees, AST class structures, and exported interfaces (<code>project_skeleton.md</code>).</p>
                    </div>
                    <button id="btn-copy-skeleton" class="btn-secondary">
                        <i data-lucide="copy"></i>
                        <span>Copy Skeleton</span>
                    </button>
                </div>

                <div class="code-terminal-frame">
                    <div class="terminal-bar">
                        <div class="terminal-dots">
                            <span class="dot red"></span>
                            <span class="dot yellow"></span>
                            <span class="dot green"></span>
                        </div>
                        <span class="terminal-title">project_skeleton.md</span>
                        <div class="terminal-actions">
                            <span class="terminal-badge"><i data-lucide="code-2"></i> AST EXTRACTOR</span>
                        </div>
                    </div>
                    <pre id="skeleton-display" class="skeleton-box"># Ingest a repository to view its structural skeleton.</pre>
                </div>
            </div>
        </section>

        <!-- TAB 4: LOCAL LLM INFERENCE -->
        <section id="tab-inference" class="tab-content">
            <div class="panel-card">
                <div class="panel-header">
                    <div class="panel-header-info">
                        <h2 class="panel-title"><i data-lucide="bot"></i> Local LLM Reasoning & Context Assembly</h2>
                        <p class="panel-desc">Assemble grounded code contexts strictly within your token budget and dispatch reasoning tasks to internal LLM endpoints (Ollama / llama.cpp) with zero cloud exposure.</p>
                    </div>
                </div>

                <div class="inference-controls-bar">
                    <div class="search-input-box" style="flex: 2;">
                        <i data-lucide="help-circle" class="search-prefix-icon"></i>
                        <input type="text" id="infer-query" class="search-input-field" placeholder="Enter reasoning question for local LLM (e.g. 'Explain the ingestion architecture')...">
                    </div>
                    
                    <div class="select-box-wrap" style="flex: 1;">
                        <i data-lucide="cpu" class="select-prefix-icon"></i>
                        <select id="infer-model" class="search-select-field">
                            <option value="qwen2.5-coder">Qwen2.5-Coder (Local Default)</option>
                            <option value="mistral">Mistral 7B</option>
                            <option value="llama3.2">Llama 3.2</option>
                            <option value="deepseek-coder">DeepSeek Coder</option>
                        </select>
                    </div>
                </div>

                <div class="search-controls" style="margin-bottom: 24px;">
                    <div class="control-item">
                        <label for="infer-budget"><i data-lucide="gauge"></i> Context Token Budget:</label>
                        <input type="range" id="infer-budget" min="512" max="8192" step="256" value="2048">
                        <b id="infer-budget-val" class="slider-val amber">2048 tokens</b>
                    </div>
                    <div class="control-item" style="margin-left: auto; gap: 12px;">
                        <button id="btn-assemble-prompt" class="btn-secondary">
                            <i data-lucide="eye"></i>
                            <span>Inspect Assembled Prompt</span>
                        </button>
                        <button id="btn-run-infer" class="btn-primary">
                            <i data-lucide="zap"></i>
                            <span>Run Local Inference</span>
                        </button>
                    </div>
                </div>

                <div class="inference-grid">
                    <!-- Left: Assembled Context Prompt -->
                    <div class="console-box">
                        <div class="console-header">
                            <div class="console-title">
                                <i data-lucide="file-text"></i>
                                <span>ASSEMBLED CONTEXT PROMPT</span>
                            </div>
                            <span id="prompt-token-meter" class="token-meter-pill">0 tokens</span>
                        </div>
                        <pre id="prompt-preview" class="prompt-preview-box">Click 'Inspect Assembled Prompt' to view the unified prompt within the token budget.</pre>
                    </div>

                    <!-- Right: Local LLM Response -->
                    <div class="console-box">
                        <div class="console-header">
                            <div class="console-title">
                                <i data-lucide="sparkles"></i>
                                <span>LOCAL LLM SYNTHESIS</span>
                            </div>
                            <span class="telemetry-pill mini"><i data-lucide="shield"></i> 100% LOCAL</span>
                        </div>
                        <div id="inference-answer" class="answer-card">
                            <div class="empty-state" style="padding: 36px 16px;">
                                <div class="empty-icon-wrap">
                                    <i data-lucide="bot"></i>
                                </div>
                                <h4>Grounded Output Console</h4>
                                <p>Synthesized answers grounded on retrieved codebase chunks will appear here.</p>
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
                    <div class="panel-header-info">
                        <h2 class="panel-title"><i data-lucide="bar-chart-3"></i> Tokenomics & Operational Analytics Engine</h2>
                        <p class="panel-desc">Real-time data analytics aggregated via Pandas & NumPy measuring token reduction efficiency, term frequency distributions (TF-IDF), and retrieval latency.</p>
                    </div>
                </div>

                <div class="analytics-grid">
                    <!-- TF-IDF Top Topics -->
                    <div class="chart-card">
                        <div class="chart-title">
                            <div class="chart-title-left">
                                <i data-lucide="tag"></i>
                                <span>NLP Topic & Term Extraction (TF-IDF)</span>
                            </div>
                            <small class="chart-dim-tag">CORPUS KEYWORDS</small>
                        </div>
                        <div id="top-topics-cloud" class="topics-cloud">
                            <span class="empty-cloud-text">Loading extracted topics…</span>
                        </div>
                    </div>

                    <!-- Latency Profiler -->
                    <div class="chart-card">
                        <div class="chart-title">
                            <div class="chart-title-left">
                                <i data-lucide="timer"></i>
                                <span>Retrieval & Reasoning Latency Profile</span>
                            </div>
                            <small class="chart-dim-tag">NUMPY PROFILER</small>
                        </div>
                        <table class="table-simple latency-table">
                            <tr>
                                <th><i data-lucide="activity"></i> Average Retrieval Latency:</th>
                                <td id="lat-avg" class="lat-val amber">0 ms</td>
                            </tr>
                            <tr>
                                <th><i data-lucide="bar-chart-2"></i> p50 Latency (Median):</th>
                                <td id="lat-p50" class="lat-val green">0 ms</td>
                            </tr>
                            <tr>
                                <th><i data-lucide="trending-up"></i> p95 Latency (Tail):</th>
                                <td id="lat-p95" class="lat-val orange">0 ms</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Extension Distribution -->
                    <div class="chart-card" style="grid-column: span 2;">
                        <div class="chart-title">
                            <div class="chart-title-left">
                                <i data-lucide="pie-chart"></i>
                                <span>Codebase Composition & Extension Breakdown</span>
                            </div>
                            <small class="chart-dim-tag">PANDAS DATA ENGINE</small>
                        </div>
                        <div class="table-wrapper">
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

    <!-- Application Script -->
    <script src="/js/app.js"></script>
</body>
</html>
