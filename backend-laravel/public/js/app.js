// WarnAI Modern Frontend Application Logic
const state = {
    activeTab: 'ingest',
    selectedFile: null,
    retrievalMode: 'hybrid',
};

const $ = (id) => document.getElementById(id);
const $$ = (sel) => document.querySelectorAll(sel);

// Icon re-initialization helper for dynamic DOM injection
const renderIcons = () => {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
};

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
    badge.innerHTML = `
        <span class="pulse-dot"></span>
        <span class="status-label">${isOnline ? 'ENGINE ONLINE' : 'ENGINE OFFLINE'}</span>
    `;
    
    if (data.retrieval_mode) {
        const engineTag = $('engine-tag');
        if (engineTag) {
            engineTag.innerHTML = `
                <i data-lucide="git-merge"></i>
                <span>${escapeHtml(data.retrieval_mode).toUpperCase()}</span>
            `;
        }
    }
    renderIcons();
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
        badge.innerHTML = `
            <span class="pulse-dot"></span>
            <span class="status-label">ENGINE OFFLINE</span>
        `;
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
        renderIcons();
    });
});

// Dropzone & File Selection
const dropzone = $('dropzone');
const fileInput = $('file-input');

if (dropzone && fileInput) {
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
}

const handleFileSelect = (file) => {
    state.selectedFile = file;
    const mb = (file.size / (1024 * 1024)).toFixed(2);
    const pill = $('selected-file-pill');
    pill.innerHTML = `
        <i data-lucide="file-archive"></i>
        <span><strong>${escapeHtml(file.name)}</strong> (${mb} MB)</span>
    `;
    pill.style.display = 'inline-flex';
    $('btn-upload').disabled = false;
    renderIcons();
};

// Upload & Normalize
$('btn-upload')?.addEventListener('click', async () => {
    if (!state.selectedFile) return;
    const btn = $('btn-upload');
    const feedback = $('upload-feedback');
    const isAsync = $('async-checkbox')?.checked || false;

    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin"></i> <span>${isAsync ? 'Queuing Ingestion…' : 'Normalizing & Indexing…'}</span>`;
    feedback.innerHTML = `
        <div style="display:flex; align-items:center; gap:8px; color:var(--amber-400); font-family:var(--font-mono); font-size:12px;">
            <i data-lucide="loader-2" class="spin"></i>
            <span>Pruning boilerplate, generating AST skeleton, and calculating embeddings…</span>
        </div>
    `;
    renderIcons();

    try {
        const formData = new FormData();
        formData.append('file', state.selectedFile);
        if (isAsync) formData.append('async', '1');
        if ($('replace-checkbox')?.checked) formData.append('replace', '1');

        const result = await api('/api/normalize', {
            method: 'POST',
            body: formData,
        });

        if (result.status === 'queued') {
            feedback.innerHTML = `
                <div style="display:flex; align-items:center; gap:8px; color:var(--signal-green); font-family:var(--font-mono); font-size:12.5px;">
                    <i data-lucide="check-circle-2"></i>
                    <span>${escapeHtml(result.message)}</span>
                </div>
            `;
        } else {
            const savings = result.tokenomics?.size_reduction_pct ?? 0;
            feedback.innerHTML = `
                <div style="display:flex; align-items:center; gap:8px; color:var(--signal-green); font-weight:700; margin-bottom:6px;">
                    <i data-lucide="check-circle-2"></i>
                    <span>Indexed ${result.files_count} files (${result.total_chunks} chunks) in ${result.elapsed_ms} ms</span>
                </div>
                <div style="font-family:var(--font-mono); font-size:11.5px; color:var(--text-secondary); margin-left: 24px;">
                    Boilerplate Reduction: <strong style="color:var(--amber-400);">${savings}%</strong> · Estimated Tokens: <strong>${(result.tokenomics?.estimated_tokens ?? 0).toLocaleString()}</strong>
                </div>
            `;
            if (result.files?.length) {
                renderFilesTable(result.files);
            }
        }
        await refreshStatus();
    } catch (err) {
        feedback.innerHTML = `
            <div style="display:flex; align-items:center; gap:8px; color:var(--signal-red); font-family:var(--font-mono); font-size:12px;">
                <i data-lucide="alert-triangle"></i>
                <span>Ingestion failed: ${escapeHtml(err.message)}</span>
            </div>
        `;
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i data-lucide="zap"></i> <span>Normalize & Index Repository</span>`;
        renderIcons();
    }
});

const renderFilesTable = (files) => {
    const container = $('ingested-files-card');
    if (!container) return;
    container.style.display = 'block';

    const tbody = $('ingested-files-body');
    tbody.innerHTML = files.map((f) => `
        <tr>
            <td style="font-family:var(--font-mono); color:var(--signal-blue); font-weight:600;">
                <i data-lucide="file-code" style="width:13px; height:13px; margin-right:4px;"></i>
                ${escapeHtml(f.path)}
            </td>
            <td style="font-family:var(--font-mono);">${(f.raw_size / 1024).toFixed(1)} KB</td>
            <td style="font-family:var(--font-mono);">${(f.clean_size / 1024).toFixed(1)} KB</td>
            <td style="font-family:var(--font-mono); color:var(--signal-green); font-weight:700;">-${f.reduction_pct}%</td>
            <td style="font-family:var(--font-mono);">${f.chunks}</td>
            <td style="font-family:var(--font-mono);">${f.tokens.toLocaleString()}</td>
            <td>
                <button class="btn-preview-file" data-path="${escapeHtml(f.path)}">
                    <i data-lucide="eye"></i> <span>View</span>
                </button>
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.btn-preview-file').forEach(btn => {
        btn.addEventListener('click', () => {
            openMarkdownModal(btn.dataset.path);
        });
    });

    renderIcons();
};

const openMarkdownModal = async (path) => {
    const modal = $('markdown-modal');
    if (!modal) return;
    $('modal-filename').textContent = path;
    $('modal-content').textContent = 'Fetching clean markdown content…';
    modal.style.display = 'flex';
    renderIcons();

    try {
        const data = await api(`/api/file/markdown?path=${encodeURIComponent(path)}`);
        $('modal-content').textContent = data.markdown || 'No content available.';
    } catch (err) {
        $('modal-content').textContent = `Failed to load markdown: ${err.message}`;
    }
};

$('btn-close-modal')?.addEventListener('click', () => {
    const modal = $('markdown-modal');
    if (modal) modal.style.display = 'none';
});

$('markdown-modal')?.addEventListener('click', (e) => {
    if (e.target === $('markdown-modal')) {
        $('markdown-modal').style.display = 'none';
    }
});

$('btn-copy-modal')?.addEventListener('click', () => {
    const text = $('modal-content').textContent;
    navigator.clipboard.writeText(text);
    const btn = $('btn-copy-modal');
    const orig = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="check"></i> <span>Copied!</span>`;
    renderIcons();
    setTimeout(() => {
        btn.innerHTML = orig;
        renderIcons();
    }, 2000);
});

// Workspace Reset Handler
$('btn-reset-workspace')?.addEventListener('click', async () => {
    if (!confirm('Clear current workspace? This will remove all indexed files so you can start a clean new project.')) {
        return;
    }
    try {
        const res = await api('/api/workspace/reset', { method: 'POST' });
        alert(res.message || 'Workspace reset.');
        $('ingested-files-card').style.display = 'none';
        $('upload-feedback').innerHTML = '';
        $('skeleton-display').textContent = '# Workspace cleared. Ingest a repository to view its structural skeleton.';
        $('search-results').innerHTML = `
            <div class="empty-state">
                <div class="empty-icon-wrap"><i data-lucide="search"></i></div>
                <h4>Awaiting Query Execution</h4>
                <p>Enter a query or select a preset above to retrieve ranked, pruned context chunks.</p>
            </div>
        `;
        await refreshStatus();
        renderIcons();
    } catch (err) {
        alert(`Failed to reset workspace: ${err.message}`);
    }
});

// Hybrid Search
const executeSearch = async () => {
    const query = $('search-query').value.trim();
    if (!query) return;

    const limit = parseInt($('search-limit').value, 10) || 5;
    const resultsContainer = $('search-results');
    resultsContainer.innerHTML = `
        <div class="empty-state">
            <div class="empty-icon-wrap">
                <i data-lucide="loader-2" class="spin"></i>
            </div>
            <h4>Synthesizing Context Retrieval</h4>
            <p>Executing dual-score fusion (BM25 lexical + ONNX 384d semantic dense embeddings)…</p>
        </div>
    `;
    renderIcons();

    try {
        const payload = await api('/api/search', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query, limit }),
        });

        if (!payload.results?.length) {
            resultsContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon-wrap">
                        <i data-lucide="search-x"></i>
                    </div>
                    <h4>No Matching Context Chunks</h4>
                    <p>No repository context chunks matched the criteria. Try adjusting keywords or ingesting code.</p>
                </div>
            `;
            renderIcons();
            return;
        }

        resultsContainer.innerHTML = payload.results.map((item, idx) => `
            <div class="chunk-card">
                <div class="chunk-header">
                    <div class="chunk-path">
                        <i data-lucide="file-code-2" style="width:13px; height:13px; margin-right:4px;"></i>
                        #${idx + 1} ${escapeHtml(item.path)} (L${item.start_line} - L${item.end_line})
                    </div>
                    <div class="score-pills">
                        <span class="score-pill hybrid" title="Combined Hybrid Score">Score: ${item.score}</span>
                        <span class="score-pill lexical" title="BM25 Lexical Score">BM25: ${item.lexical_score}</span>
                        <span class="score-pill semantic" title="Dense Cosine Semantic Score">Vector: ${item.semantic_score}</span>
                        <span class="score-pill" style="background:rgba(255,255,255,0.05); color:var(--text-secondary);">${item.tokens} tok</span>
                    </div>
                </div>
                <div class="chunk-code">${escapeHtml(item.markdown)}</div>
            </div>
        `).join('');

        await refreshStatus();
        renderIcons();
    } catch (err) {
        resultsContainer.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon-wrap" style="color:var(--signal-red);">
                    <i data-lucide="alert-octagon"></i>
                </div>
                <h4>Search Error</h4>
                <p style="color:var(--signal-red); font-family:var(--font-mono);">${escapeHtml(err.message)}</p>
            </div>
        `;
        renderIcons();
    }
};

$('btn-search')?.addEventListener('click', executeSearch);
$('search-query')?.addEventListener('keydown', (e) => {
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
$('search-limit')?.addEventListener('input', (e) => {
    $('search-limit-val').textContent = e.target.value;
});

// Skeleton Loader
const loadSkeleton = async () => {
    const box = $('skeleton-display');
    box.textContent = 'Loading repository AST skeleton…';
    try {
        const data = await api('/api/skeleton');
        box.textContent = data.skeleton || '# Ingest a repository to view its structural skeleton.';
    } catch {
        box.textContent = '# Failed to load skeleton from server.';
    }
};

$('btn-copy-skeleton')?.addEventListener('click', () => {
    const text = $('skeleton-display').textContent;
    navigator.clipboard.writeText(text);
    const btn = $('btn-copy-skeleton');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="check"></i> <span>Copied!</span>`;
    renderIcons();
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        renderIcons();
    }, 2000);
});

// Local LLM Inference
$('btn-assemble-prompt')?.addEventListener('click', async () => {
    const query = $('infer-query').value.trim();
    if (!query) return;

    const budget = parseInt($('infer-budget').value, 10) || 2048;
    const previewBox = $('prompt-preview');
    previewBox.textContent = 'Assembling pruned context into budget…';

    try {
        const data = await api('/api/context/assemble', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query, token_budget: budget }),
        });

        $('prompt-token-meter').textContent = `${data.assembled_prompt_tokens} tokens (${data.budget_utilization_pct}% budget)`;
        previewBox.textContent = data.prompt;
    } catch (err) {
        previewBox.textContent = `Assembly failed: ${err.message}`;
    }
});

$('btn-run-infer')?.addEventListener('click', async () => {
    const query = $('infer-query').value.trim();
    if (!query) return;

    const budget = parseInt($('infer-budget').value, 10) || 2048;
    const model = $('infer-model').value || 'qwen2.5-coder';
    const btn = $('btn-run-infer');
    const answerBox = $('inference-answer');

    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin"></i> <span>Reasoning…</span>`;
    answerBox.innerHTML = `
        <div class="empty-state" style="padding: 24px;">
            <div class="empty-icon-wrap"><i data-lucide="loader-2" class="spin"></i></div>
            <h4>Synthesizing Reasoning Response</h4>
            <p>Evaluating grounded codebase chunks on local LLM endpoint…</p>
        </div>
    `;
    renderIcons();

    try {
        const data = await api('/api/infer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query, token_budget: budget, model }),
        });

        answerBox.innerHTML = `
            <div class="answer-content">${escapeHtml(data.answer)}</div>
            <div style="font-family:var(--font-mono); font-size:11.5px; color:var(--text-muted); display:flex; justify-content:space-between; margin-top:12px; padding-top:10px; border-top:1px solid var(--border-subtle);">
                <span>Engine: <strong style="color:var(--text-primary);">${escapeHtml(data.engine)}</strong> · Model: <strong style="color:var(--amber-400);">${escapeHtml(data.model_used)}</strong></span>
                <span>Latency: <strong style="color:var(--signal-green);">${data.latency_ms} ms</strong> · Tokens: <strong>${data.total_tokens}</strong></span>
            </div>
            <div class="citation-list">
                ${data.sources?.map((s) => `
                    <span class="citation-badge">
                        <i data-lucide="file-text" style="width:11px; height:11px;"></i>
                        ${escapeHtml(s.path)} (${s.score})
                    </span>
                `).join('') || ''}
            </div>
        `;
    } catch (err) {
        answerBox.innerHTML = `
            <div style="color:var(--signal-red); font-family:var(--font-mono); font-size:12.5px;">
                <i data-lucide="alert-triangle" style="margin-right:4px;"></i>
                Inference error: ${escapeHtml(err.message)}
            </div>
        `;
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i data-lucide="zap"></i> <span>Run Local Inference</span>`;
        renderIcons();
    }
});

$('infer-budget')?.addEventListener('input', (e) => {
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
                <div class="topic-tag">
                    <i data-lucide="hash" style="width:11px; height:11px; margin-right:2px;"></i>
                    ${escapeHtml(t.term)} <small style="opacity:0.6; margin-left:4px;">${t.weight}</small>
                </div>
            `).join('');
        } else {
            topicsContainer.innerHTML = '<span class="empty-cloud-text">No topics extracted yet. Ingest a repository first.</span>';
        }

        // Extension distribution
        const extBody = $('ext-dist-body');
        if (data.extension_distribution?.length) {
            extBody.innerHTML = data.extension_distribution.map((e) => `
                <tr>
                    <td style="font-family:var(--font-mono); color:var(--amber-400); font-weight:600;">${escapeHtml(e.extension)}</td>
                    <td style="font-family:var(--font-mono);">${e.count}</td>
                    <td style="font-family:var(--font-mono);">${(e.total_bytes / 1024).toFixed(1)} KB</td>
                    <td style="font-family:var(--font-mono);">${e.total_tokens?.toLocaleString() || 0}</td>
                </tr>
            `).join('');
        } else {
            extBody.innerHTML = '<tr><td colspan="4" style="color:var(--text-dim); text-align:center; padding:24px;">No data available.</td></tr>';
        }

        // Latency
        const lat = data.latency || {};
        $('lat-avg').textContent = `${lat.avg_latency_ms || 0} ms`;
        $('lat-p50').textContent = `${lat.p50_latency_ms || 0} ms`;
        $('lat-p95').textContent = `${lat.p95_latency_ms || 0} ms`;
        renderIcons();
    } catch (err) {
        console.error('Failed to load analytics:', err);
    }
};

const escapeHtml = (str) => {
    if (!str) return '';
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
};

// Animation utility
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .spin {
        animation: spin 1s linear infinite;
        display: inline-block;
    }
`;
document.head.appendChild(style);

// Tab Switching Utility
const switchTab = (tabName) => {
    const btn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
    const content = $(`tab-${tabName}`);
    if (btn && content) {
        $$('.tab-btn').forEach((b) => b.classList.remove('active'));
        $$('.tab-content').forEach((c) => c.classList.remove('active'));
        btn.classList.add('active');
        content.classList.add('active');
        state.activeTab = tabName;
        if (tabName === 'skeleton') loadSkeleton();
        if (tabName === 'analytics') loadAnalytics();
        renderIcons();
    }
};

const handleInitialHash = () => {
    const hash = window.location.hash.replace('#', '');
    if (['ingest', 'search', 'skeleton', 'inference', 'analytics'].includes(hash)) {
        switchTab(hash);
    }
};

// Initial bootstrap
document.addEventListener('DOMContentLoaded', () => {
    handleInitialHash();
    renderIcons();
    refreshStatus();
});
handleInitialHash();
renderIcons();
refreshStatus();
