const state = { loaded: false };
const $ = (id) => document.getElementById(id);
const api = async (url, options = {}) => {
    const response = await fetch(url, options);
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(payload.message || 'The request could not be completed.');
    return payload;
};
const renderMetrics = (analytics) => {
    $('documents').textContent = analytics.documents ?? 0;
    $('queries').textContent = analytics.queries ?? 0;
    $('tokens').textContent = (analytics.estimated_tokens ?? 0).toLocaleString();
    $('retrieval-mode').textContent = analytics.deep_learning_embeddings ? 'HYBRID' : 'BM25';
    $('embedding-state').textContent = analytics.deep_learning_embeddings ? 'Semantic + lexical' : 'Lexical fallback';
};
const refresh = async () => {
    try {
        const payload = await api('/api/health');
        renderMetrics(payload.analytics || {});
        $('engine-status').innerHTML = '<span class="status-dot"></span> Engine online';
        state.loaded = true;
    } catch {
        $('engine-status').innerHTML = '<span class="status-dot" style="background:#ef8b8b"></span> Engine offline';
    }
};
$('file').addEventListener('change', (event) => {
    const file = event.target.files[0];
    $('file-name').textContent = file ? `${file.name} · ${(file.size / 1024 / 1024).toFixed(2)} MB` : 'No file selected';
    document.querySelector('.dropzone').classList.toggle('has-file', Boolean(file));
});
$('upload-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = event.currentTarget.querySelector('button');
    const file = $('file').files[0];
    if (!file) return;
    button.disabled = true; button.querySelector('span').textContent = 'Indexing…';
    try {
        const body = new FormData(); body.append('file', file);
        const payload = await api('/api/normalize', { method: 'POST', body });
        $('upload-feedback').textContent = `${payload.count} files indexed in ${payload.elapsed_ms} ms.`;
        renderMetrics(payload.analytics || {});
    } catch (error) { $('upload-feedback').textContent = error.message; }
    finally { button.disabled = false; button.querySelector('span').textContent = 'Normalize and index'; }
});
$('search-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const results = $('results'); results.className = 'results'; results.innerHTML = '<div class="empty-state"><div class="empty-icon">◌</div><p>Searching local context…</p></div>';
    try {
        const payload = await api('/api/search', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ query: $('query').value, limit: 8 }) });
        if (!payload.results?.length) { results.innerHTML = '<div class="empty-state"><div class="empty-icon">⌕</div><p>No relevant context found.</p></div>'; return; }
        results.innerHTML = payload.results.map((item) => `<div class="result"><b>${item.path}</b><span class="score">score ${item.score}</span><p>${item.markdown.slice(0, 260).replaceAll('<', '&lt;')}…</p></div>`).join('');
        renderMetrics(payload.analytics || {});
    } catch (error) { results.innerHTML = `<div class="empty-state"><p>${error.message}</p></div>`; }
});
refresh();
