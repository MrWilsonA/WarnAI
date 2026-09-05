// WarnAI Frontend Application Logic
const state = {
    activeTab: 'ingest',
    selectedFile: null,
    retrievalMode: 'hybrid',
};

const $ = (id) => document.getElementById(id);
const $$ = (sel) => document.querySelectorAll(sel);

// Helper API caller
const api = async (url, options = {}) => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    const headers = options.headers || {};
    if (meta && !headers['X-CSRF-TOKEN']) {
        headers['X-CSRF-TOKEN'] = meta.content;
    }
    const res = await fetch(url, { ...options, headers });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        throw new Error(data.message || `Request failed with status ${res.status}`);
    }
    return data;
};

// Update Top Metrics Cards
const updateMetrics = (data) => {
    if (!data) return;
    const tokenomics = data.tokenomics || {};
    
    $('metric-docs').textContent = data.documents ?? 0;
    $('metric-chunks').textContent = data.total_chunks ?? 0;
    $('metric-tokens').textContent = (data.estimated_tokens ?? 0).toLocaleString();
    
    const savingsPct = tokenomics.token_savings_pct ?? 0;
    $('metric-savings').textContent = `${savingsPct}%`;

    // Status badge
    const badge = $('engine-badge');
    const isOnline = data.status !== 'offline';
    badge.className = `status-badge ${isOnline ? '' : 'offline'}`;
    badge.innerHTML = `<span class="pulse-dot"></span> ${isOnline ? 'ENGINE ONLINE' : 'ENGINE OFFLINE'}`;
    
    if (data.retrieval_mode) {
        $('engine-tag').textContent = data.retrieval_mode;
    }
};

// Refresh Status & Analytics
const refreshStatus = async () => {
    try {
        const health = await api('/api/health');
        const analytics = await api('/api/analytics');
        updateMetrics({ ...health, ...analytics });
    } catch {
        const badge = $('engine-badge');
        badge.className = 'status-badge offline';
        badge.innerHTML = '<span class="pulse-dot"></span> ENGINE OFFLINE';
    }
};

// Tab Navigation
$$('.tab-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        const targetTab = btn.dataset.tab;
        $$('.tab-btn').forEach((b) => b.classList.remove('active'));
        $$('.tab-content').forEach((c) => c.classList.remove('active'));

        btn.classList.add('active');
        $(`tab-${targetTab}`).classList.add('active');
        state.activeTab = targetTab;

        if (targetTab === 'skeleton') loadSkeleton();
        if (targetTab === 'analytics') loadAnalytics();
    });
});

// Dropzone & File Selection
const dropzone = $('dropzone');
const fileInput = $('file-input');

dropzone.addEventListener('click', () => fileInput.click());
dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('dragover');
});
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    if (e.dataTransfer.files?.length) {
        handleFileSelect(e.dataTransfer.files[0]);
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files?.length) {
        handleFileSelect(e.target.files[0]);
    }
});

const handleFileSelect = (file) => {
    state.selectedFile = file;
    const mb = (file.size / (1024 * 1024)).toFixed(2);
    $('selected-file-pill').innerHTML = `<span>📦</span> <b>${file.name}</b> (${mb} MB)`;
    $('selected-file-pill').style.display = 'inline-flex';
    $('btn-upload').disabled = false;
};

// Upload & Normalize
$('btn-upload').addEventListener('click', async () => {
    if (!state.selectedFile) return;
    const btn = $('btn-upload');
    const feedback = $('upload-feedback');
    const isAsync = $('async-checkbox')?.checked || false;

    btn.disabled = true;
    btn.innerHTML = `<span>⏳</span> ${isAsync ? 'Queuing Ingestion…' : 'Normalizing & Indexing…'}`;
    feedback.innerHTML = '<span style="color:var(--accent);">Processing file and extracting structural skeleton…</span>';

    try {
        const formData = new FormData();
        formData.append('file', state.selectedFile);
        if (isAsync) formData.append('async', '1');

        const result = await api('/api/normalize', {
            method: 'POST',
            body: formData,
        });

        if (result.status === 'queued') {
            feedback.innerHTML = `<span style="color:var(--success);">✓ ${result.message}</span>`;
        } else {
            const savings = result.tokenomics?.size_reduction_pct ?? 0;
            feedback.innerHTML = `
                <div style="color:var(--success); font-weight:600; margin-bottom:8px;">
                    ✓ Indexed ${result.files_count} files (${result.total_chunks} chunks) in ${result.elapsed_ms} ms.
                </div>
                <div style="font-size:12px; color:var(--text-muted);">
                    Boilerplate Reduction: <b>${savings}%</b> · Estimated Tokens: <b>${(result.tokenomics?.estimated_tokens ?? 0).toLocaleString()}</b>
                </div>
            `;
            // Render file summary table if files returned
            if (result.files?.length) {
                renderFilesTable(result.files);
            }
        }
        await refreshStatus();
    } catch (err) {
        feedback.innerHTML = `<span style="color:#f87171;">✕ Ingestion failed: ${err.message}</span>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span>⚡</span> Normalize & Index Repository';
    }
});

const renderFilesTable = (files) => {
    const container = $('ingested-files-card');
    if (!container) return;
    container.style.display = 'block';

    const tbody = $('ingested-files-body');
    tbody.innerHTML = files.map((f) => `
        <tr>
            <td style="font-family:monospace; color:var(--accent); font-weight:600;">${f.path}</td>
            <td>${(f.raw_size / 1024).toFixed(1)} KB</td>
            <td>${(f.clean_size / 1024).toFixed(1)} KB</td>
            <td style="color:var(--success); font-weight:600;">-${f.reduction_pct}%</td>
            <td>${f.chunks}</td>
            <td>${f.tokens.toLocaleString()}</td>
        </tr>
    `).join('');
};

// Hybrid Search
const executeSearch = async () => {
    const query = $('search-query').value.trim();
    if (!query) return;

    const limit = parseInt($('search-limit').value, 10) || 5;
    const resultsContainer = $('search-results');
    resultsContainer.innerHTML = `
        <div class="empty-state">
            <div class="empty-icon">◌</div>
            <p>Running hybrid search (BM25 lexical + dense semantic embeddings)…</p>
        </div>
    `;

    try {
        const payload = await api('/api/search', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query, limit }),
        });

        if (!payload.results?.length) {
            resultsContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">⌕</div>
                    <p>No relevant context chunks found matching the query.</p>
                </div>
            `;
            return;
        }

        resultsContainer.innerHTML = payload.results.map((item, idx) => `
            <div class="chunk-card">
                <div class="chunk-header">
                    <div class="chunk-path">#${idx + 1} ${item.path} (L${item.start_line} - L${item.end_line})</div>
                    <div class="score-pills">
                        <span class="score-pill hybrid" title="Combined Hybrid Score">Score: ${item.score}</span>
                        <span class="score-pill lexical" title="BM25 Lexical Score">BM25: ${item.lexical_score}</span>
                        <span class="score-pill semantic" title="Dense Cosine Semantic Score">Vector: ${item.semantic_score}</span>
                        <span class="score-pill" style="background:#1e293b; color:var(--text-muted);">${item.tokens} tokens</span>
                    </div>
                </div>
                <div class="chunk-code">${escapeHtml(item.markdown)}</div>
            </div>
        `).join('');

        await refreshStatus();
    } catch (err) {
        resultsContainer.innerHTML = `<div class="empty-state"><p style="color:#f87171;">${err.message}</p></div>`;
    }
};

$('btn-search').addEventListener('click', executeSearch);
$('search-query').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') executeSearch();
});

// Preset Chips
$$('.preset-chip').forEach((chip) => {
    chip.addEventListener('click', () => {
        $('search-query').value = chip.dataset.query;
        executeSearch();
    });
});

// Limit Slider
$('search-limit').addEventListener('input', (e) => {
    $('search-limit-val').textContent = e.target.value;
});

// Skeleton Loader
const loadSkeleton = async () => {
    const box = $('skeleton-display');
    box.textContent = 'Loading repository skeleton…';
    try {
        const data = await api('/api/skeleton');
        box.textContent = data.skeleton || 'No skeleton available.';
    } catch {
        box.textContent = 'Failed to load skeleton.';
    }
};

$('btn-copy-skeleton')?.addEventListener('click', () => {
    const text = $('skeleton-display').textContent;
    navigator.clipboard.writeText(text);
    alert('Project skeleton copied to clipboard!');
});

// Local LLM Inference
$('btn-assemble-prompt').addEventListener('click', async () => {
    const query = $('infer-query').value.trim();
    if (!query) return;

    const budget = parseInt($('infer-budget').value, 10) || 2048;
    const previewBox = $('prompt-preview');
    previewBox.textContent = 'Assembling pruned context into prompt…';

    try {
        const data = await api('/api/context/assemble', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query, token_budget: budget }),
        });

        $('prompt-token-meter').textContent = `${data.assembled_prompt_tokens} tokens (${data.budget_utilization_pct}% budget utilized)`;
        previewBox.textContent = data.prompt;
    } catch (err) {
        previewBox.textContent = `Assembly failed: ${err.message}`;
    }
});

$('btn-run-infer').addEventListener('click', async () => {
    const query = $('infer-query').value.trim();
    if (!query) return;

    const budget = parseInt($('infer-budget').value, 10) || 2048;
    const model = $('infer-model').value || 'qwen2.5-coder';
    const btn = $('btn-run-infer');
    const answerBox = $('inference-answer');

    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span> Reasoning…';
    answerBox.innerHTML = '<p style="color:var(--text-muted);">Generating answer from local grounded context…</p>';

    try {
        const data = await api('/api/infer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query, token_budget: budget, model }),
        });

        answerBox.innerHTML = `
            <div class="answer-content">${escapeHtml(data.answer)}</div>
            <div style="font-size:12px; color:var(--text-dim); display:flex; justify-content:space-between; margin-top:8px;">
                <span>Engine: <b>${data.engine}</b> · Model: <b>${data.model_used}</b></span>
                <span>Latency: <b>${data.latency_ms} ms</b> · Tokens: <b>${data.total_tokens}</b></span>
            </div>
            <div class="citation-list">
                ${data.sources?.map((s) => `<span class="citation-badge">📄 ${s.path} (${s.score})</span>`).join('') || ''}
            </div>
        `;
    } catch (err) {
        answerBox.innerHTML = `<p style="color:#f87171;">Inference error: ${err.message}</p>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span>⚡</span> Run Local Inference';
    }
});

$('infer-budget').addEventListener('input', (e) => {
    $('infer-budget-val').textContent = `${e.target.value} tokens`;
});

// Analytics Dashboard Loader
const loadAnalytics = async () => {
    try {
        const data = await api('/api/analytics');
        
        // Topic tags
        const topicsContainer = $('top-topics-cloud');
        if (data.top_topics?.length) {
            topicsContainer.innerHTML = data.top_topics.map((t) => `
                <div class="topic-tag">#${t.term} <small style="opacity:0.7">(${t.weight})</small></div>
            `).join('');
        } else {
            topicsContainer.innerHTML = '<span style="color:var(--text-dim);">No topics extracted yet. Ingest a repository first.</span>';
        }

        // Extension distribution
        const extBody = $('ext-dist-body');
        if (data.extension_distribution?.length) {
            extBody.innerHTML = data.extension_distribution.map((e) => `
                <tr>
                    <td style="font-family:monospace; color:var(--accent);">${e.extension}</td>
                    <td>${e.count}</td>
                    <td>${(e.total_bytes / 1024).toFixed(1)} KB</td>
                    <td>${e.total_tokens?.toLocaleString() || 0}</td>
                </tr>
            `).join('');
        } else {
            extBody.innerHTML = '<tr><td colspan="4" style="color:var(--text-dim); text-align:center;">No data available.</td></tr>';
        }

        // Latency
        const lat = data.latency || {};
        $('lat-avg').textContent = `${lat.avg_latency_ms || 0} ms`;
        $('lat-p50').textContent = `${lat.p50_latency_ms || 0} ms`;
        $('lat-p95').textContent = `${lat.p95_latency_ms || 0} ms`;
    } catch (err) {
        console.error('Failed to load analytics:', err);
    }
};

const escapeHtml = (str) => {
    if (!str) return '';
    return str
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
};

// Initial load
refreshStatus();
