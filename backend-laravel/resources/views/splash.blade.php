<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WarnAI · Workspace-Aware Repository Normalization & Adaptive Inference</title>
    
    <!-- Favicon & Brand Icons -->
    <link rel="icon" type="image/png" href="/images/logo.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/images/logo.png">

    <!-- Modern Typography: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icon Library -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Application Stylesheet -->
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="splash-body">
    <div class="splash-container">
        <!-- Top Utility Header -->
        <header class="splash-top-bar">
            <div class="splash-brand-compact">
                <img src="/images/logo.png" alt="WarnAI" class="splash-logo-mini">
                <span class="splash-brand-name">WarnAI</span>
                <span class="version-badge">v2.0-CORE</span>
            </div>

            <div class="header-badges">
                <div class="arch-badge">
                    <i data-lucide="shield-check"></i>
                    <span>AIR-GAPPED SYSTEM</span>
                </div>
                <div id="splash-engine-badge" class="status-badge">
                    <span class="pulse-dot"></span>
                    <span id="splash-status-text">CONNECTING…</span>
                </div>
                <a href="/workspace" class="btn-primary btn-sm">
                    <span>Enter Workbench</span>
                    <i data-lucide="arrow-right"></i>
                </a>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="splash-hero">
            <div class="splash-hero-emblem-wrap">
                <div class="splash-hero-emblem-frame">
                    <img src="/images/logo.png" alt="WarnAI Emblem" class="splash-hero-emblem">
                </div>
                <div class="emblem-ambient-glow"></div>
            </div>

            <div class="splash-hero-text">
                <div class="hero-eyebrow">
                    <i data-lucide="terminal"></i>
                    <span>LOCAL CONTEXT ENGINEERING & REPOSITORY ORCHESTRATION</span>
                </div>
                <h1 class="splash-hero-title">
                    High-Precision Codebase Intelligence, <span class="text-amber">Zero Cloud Exposure</span>.
                </h1>
                <p class="splash-hero-desc">
                    WarnAI strips redundant boilerplate, generates deterministic architectural skeletons, and performs hybrid BM25 + ONNX 384d semantic retrieval to serve ultra-dense prompts to local LLMs with absolute data privacy.
                </p>

                <!-- Action CTA Bar -->
                <div class="splash-cta-bar">
                    <a href="/workspace" class="btn-primary btn-hero">
                        <i data-lucide="sparkles"></i>
                        <span>Launch Workspace Workbench</span>
                        <span class="cta-arrow">→</span>
                    </a>
                    <a href="/workspace#ingest" class="btn-secondary btn-hero">
                        <i data-lucide="hard-drive-upload"></i>
                        <span>Quick Ingestion Pipeline</span>
                    </a>
                    <a href="#architecture" class="btn-ghost btn-hero">
                        <i data-lucide="layers"></i>
                        <span>Explore Architecture</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Live Telemetry Bar -->
        <section class="splash-telemetry-bar">
            <div class="telemetry-item">
                <div class="telemetry-icon-wrap">
                    <i data-lucide="cpu"></i>
                </div>
                <div class="telemetry-info">
                    <span class="telemetry-label">Embedding Engine</span>
                    <strong class="telemetry-val" id="splash-embed-engine">FastEmbed ONNX (384d)</strong>
                </div>
            </div>

            <div class="telemetry-item">
                <div class="telemetry-icon-wrap">
                    <i data-lucide="git-merge"></i>
                </div>
                <div class="telemetry-info">
                    <span class="telemetry-label">Retrieval Mode</span>
                    <strong class="telemetry-val" id="splash-retrieval-mode">Hybrid BM25 + Vectors</strong>
                </div>
            </div>

            <div class="telemetry-item">
                <div class="telemetry-icon-wrap">
                    <i data-lucide="database"></i>
                </div>
                <div class="telemetry-info">
                    <span class="telemetry-label">Vector Store</span>
                    <strong class="telemetry-val">PostgreSQL 16 + pgvector</strong>
                </div>
            </div>

            <div class="telemetry-item">
                <div class="telemetry-icon-wrap">
                    <i data-lucide="bot"></i>
                </div>
                <div class="telemetry-info">
                    <span class="telemetry-label">Local LLM Gateway</span>
                    <strong class="telemetry-val">Ollama / llama.cpp REST</strong>
                </div>
            </div>
        </section>

        <!-- Main Splash Menu Navigation Grid -->
        <section class="splash-menu-section">
            <div class="section-heading">
                <h2 class="section-title">
                    <i data-lucide="compass"></i>
                    <span>Workbench Navigation Portal</span>
                </h2>
                <p class="section-desc">Select an operational module to jump straight into the developer environment.</p>
            </div>

            <div class="splash-menu-grid">
                <!-- Card 1: Ingestion & Normalization -->
                <a href="/workspace#ingest" class="menu-card highlight-card">
                    <div class="menu-card-top">
                        <div class="menu-card-icon-wrap amber">
                            <i data-lucide="hard-drive-download"></i>
                        </div>
                        <span class="menu-card-badge">MODULE 01</span>
                    </div>
                    <h3 class="menu-card-title">Repository Normalization Pipeline</h3>
                    <p class="menu-card-desc">Ingest ZIP archives, source code files, and technical PDF/DOCX documents. Strips boilerplate and converts everything to clean Markdown.</p>
                    <div class="menu-card-footer">
                        <span>Launch Ingestion Pipeline</span>
                        <i data-lucide="arrow-up-right"></i>
                    </div>
                </a>

                <!-- Card 2: Semantic Hybrid Retrieval -->
                <a href="/workspace#search" class="menu-card">
                    <div class="menu-card-top">
                        <div class="menu-card-icon-wrap cyan">
                            <i data-lucide="search-code"></i>
                        </div>
                        <span class="menu-card-badge">MODULE 02</span>
                    </div>
                    <h3 class="menu-card-title">Semantic & Lexical Hybrid Search</h3>
                    <p class="menu-card-desc">Execute dual-score context queries combining BM25 keyword matching with local ONNX 384d semantic dense vector cosine similarities.</p>
                    <div class="menu-card-footer">
                        <span>Open Hybrid Search</span>
                        <i data-lucide="arrow-up-right"></i>
                    </div>
                </a>

                <!-- Card 3: Project Skeleton -->
                <a href="/workspace#skeleton" class="menu-card">
                    <div class="menu-card-top">
                        <div class="menu-card-icon-wrap emerald">
                            <i data-lucide="git-fork"></i>
                        </div>
                        <span class="menu-card-badge">MODULE 03</span>
                    </div>
                    <h3 class="menu-card-title">Deterministic Architecture Skeleton</h3>
                    <p class="menu-card-desc">Inspect and copy <code>project_skeleton.md</code>, capturing module trees, AST class hierarchy, and exported signatures deterministically.</p>
                    <div class="menu-card-footer">
                        <span>View Architecture Skeleton</span>
                        <i data-lucide="arrow-up-right"></i>
                    </div>
                </a>

                <!-- Card 4: Local LLM Inference Gateway -->
                <a href="/workspace#inference" class="menu-card">
                    <div class="menu-card-top">
                        <div class="menu-card-icon-wrap amber">
                            <i data-lucide="bot"></i>
                        </div>
                        <span class="menu-card-badge">MODULE 04</span>
                    </div>
                    <h3 class="menu-card-title">Local LLM Reasoning Gateway</h3>
                    <p class="menu-card-desc">Assemble grounded prompts under strict context token budgets and execute local reasoning with Ollama or llama.cpp without API keys.</p>
                    <div class="menu-card-footer">
                        <span>Open Inference Console</span>
                        <i data-lucide="arrow-up-right"></i>
                    </div>
                </a>

                <!-- Card 5: Tokenomics & Analytics -->
                <a href="/workspace#analytics" class="menu-card">
                    <div class="menu-card-top">
                        <div class="menu-card-icon-wrap purple">
                            <i data-lucide="bar-chart-3"></i>
                        </div>
                        <span class="menu-card-badge">MODULE 05</span>
                    </div>
                    <h3 class="menu-card-title">Tokenomics & Data Analytics</h3>
                    <p class="menu-card-desc">Monitor token compression ratios, TF-IDF topic keyword extraction, and latency telemetry profiler aggregated via Pandas & NumPy.</p>
                    <div class="menu-card-footer">
                        <span>View Operational Analytics</span>
                        <i data-lucide="arrow-up-right"></i>
                    </div>
                </a>

                <!-- Card 6: Clean Markdown Exporter -->
                <a href="/workspace#ingest" class="menu-card">
                    <div class="menu-card-top">
                        <div class="menu-card-icon-wrap orange">
                            <i data-lucide="package-check"></i>
                        </div>
                        <span class="menu-card-badge">EXPORT</span>
                    </div>
                    <h3 class="menu-card-title">Clean Markdown Package Exporter</h3>
                    <p class="menu-card-desc">Export and download your normalized project as a unified Markdown bundle (.md) or a ZIP archive ready for any external LLM prompt window.</p>
                    <div class="menu-card-footer">
                        <span>Export Clean Codebase</span>
                        <i data-lucide="arrow-up-right"></i>
                    </div>
                </a>
            </div>
        </section>

        <!-- Architecture Spec Highlights -->
        <section id="architecture" class="splash-arch-section">
            <div class="section-heading">
                <h2 class="section-title">
                    <i data-lucide="cpu"></i>
                    <span>WarnAI Technical Architecture</span>
                </h2>
                <p class="section-desc">Key engineering pillars as specified in the WarnAI technical design document.</p>
            </div>

            <div class="arch-pillars-grid">
                <div class="arch-pillar-box">
                    <div class="pillar-number">01</div>
                    <h4>Non-Agentic Determinism</h4>
                    <p>Bypasses brittle, slow multi-agent loops in favor of instantaneous, deterministic context normalization and high-speed ranking.</p>
                </div>

                <div class="arch-pillar-box">
                    <div class="pillar-number">02</div>
                    <h4>100% Air-Gapped Local Stack</h4>
                    <p>Zero external API keys, zero cloud telemetry. FastEmbed ONNX Runtime and local PostgreSQL vector storage safeguard confidential code.</p>
                </div>

                <div class="arch-pillar-box">
                    <div class="pillar-number">03</div>
                    <h4>Hybrid Context Ranking</h4>
                    <p>Blends lexical BM25 exact-term precision with dense 384d semantic vectors (<code>0.45 BM25 + 0.55 Vector</code>) for maximum retrieval accuracy.</p>
                </div>

                <div class="arch-pillar-box">
                    <div class="pillar-number">04</div>
                    <h4>Tokenomics Efficiency</h4>
                    <p>Eliminates up to 60-80% of redundant repository noise and whitespace, protecting small local LLM context windows from degradation.</p>
                </div>
            </div>
        </section>

        <!-- Footer Section -->
        <footer class="splash-footer">
            <div class="footer-left">
                <div class="footer-brand">
                    <img src="/images/logo.png" alt="WarnAI" class="footer-logo">
                    <strong>WarnAI</strong>
                </div>
                <p>Workspace-Aware Repository Normalization & Adaptive Inference AI · Built for private, local development.</p>
            </div>
            <div class="footer-right">
                <span class="footer-shortcut">Press <kbd>W</kbd> to launch workbench</span>
                <span class="footer-shortcut">Press <kbd>S</kbd> for search</span>
            </div>
        </footer>
    </div>

    <!-- Script for Live Status & Shortcuts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();

            // Fetch live health status
            fetch('/api/health')
                .then(res => res.json())
                .then(data => {
                    const badge = document.getElementById('splash-engine-badge');
                    const text = document.getElementById('splash-status-text');
                    const isOnline = data.status !== 'offline';
                    
                    if (badge && text) {
                        badge.className = `status-badge ${isOnline ? '' : 'offline'}`;
                        text.textContent = isOnline ? 'ENGINE ONLINE' : 'ENGINE OFFLINE';
                    }

                    if (data.embedding_engine) {
                        const embedEl = document.getElementById('splash-embed-engine');
                        if (embedEl) embedEl.textContent = data.embedding_engine.toUpperCase();
                    }

                    if (data.retrieval_mode) {
                        const retEl = document.getElementById('splash-retrieval-mode');
                        if (retEl) retEl.textContent = data.retrieval_mode.toUpperCase();
                    }
                })
                .catch(() => {
                    const badge = document.getElementById('splash-engine-badge');
                    const text = document.getElementById('splash-status-text');
                    if (badge && text) {
                        badge.className = 'status-badge offline';
                        text.textContent = 'ENGINE OFFLINE';
                    }
                });

            // Keyboard Shortcuts
            document.addEventListener('keydown', (e) => {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                if (e.key === 'w' || e.key === 'W') {
                    window.location.href = '/workspace';
                }
                if (e.key === 's' || e.key === 'S') {
                    window.location.href = '/workspace#search';
                }
            });
        });
    </script>
</body>
</html>
