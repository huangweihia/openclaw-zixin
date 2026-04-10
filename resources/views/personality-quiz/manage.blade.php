<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="personality-quiz-admin-token" content="{{ $adminToken }}">
    <title>SBTI · 管理</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
<div class="max-w-6xl mx-auto px-4 py-8 space-y-10">
    <header class="space-y-2">
        <h1 class="text-2xl font-bold">SBTI · 无登录管理</h1>
        <p class="text-sm text-slate-600">请妥善保管带 token 的完整 URL，勿提交到公开仓库。所有写操作会立刻影响前台题库与计分。</p>
        <button type="button" id="btn-reload" class="text-sm px-3 py-1.5 rounded-lg bg-slate-900 text-white">重新加载数据</button>
        <p id="status" class="text-sm text-emerald-700 min-h-[1.25rem]"></p>
    </header>

    <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4 shadow-sm">
        <h2 class="text-lg font-semibold">设置</h2>
        <div class="flex flex-wrap items-end gap-3">
            <label class="flex flex-col text-sm gap-1">
                <span class="text-slate-600">低匹配阈值 low_match_threshold（0–100）</span>
                <input id="set-threshold" type="number" min="0" max="100" class="border rounded-lg px-3 py-2 w-40" />
            </label>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" id="set-enabled" />
                <span class="text-slate-600">启用（开启时首页展示入口）</span>
            </label>
            <button type="button" id="btn-save-setting" class="px-4 py-2 rounded-lg bg-teal-600 text-white text-sm">保存</button>
        </div>
    </section>

    <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4 shadow-sm">
        <h2 class="text-lg font-semibold">结果类型</h2>
        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <div class="space-y-2">
                <label class="block text-slate-600">code</label>
                <input id="type-code" class="w-full border rounded-lg px-3 py-2" />
                <label class="block text-slate-600">中文名</label>
                <input id="type-cn-name" class="w-full border rounded-lg px-3 py-2" />
                <label class="block text-slate-600">intro</label>
                <input id="type-intro" class="w-full border rounded-lg px-3 py-2" />
                <label class="block text-slate-600">结果图 image_url（可填外链或站内静态资源路径）</label>
                <input id="type-image-url" class="w-full border rounded-lg px-3 py-2" placeholder="https://... 或 /storage/..." />
                <label class="block text-slate-600">pattern（L/M/H，长度=启用维度数；兜底类型可空）</label>
                <input id="type-pattern" class="w-full border rounded-lg px-3 py-2 font-mono text-xs" />
                <label class="inline-flex items-center gap-2"><input type="checkbox" id="type-fallback" /> 兜底类型（全局仅建议保留一个）</label>
                <label class="inline-flex items-center gap-2"><input type="checkbox" id="type-active" checked /> 启用</label>
                <label class="block text-slate-600">排序</label>
                <input id="type-sort" type="number" class="w-full border rounded-lg px-3 py-2" value="0" />
                <label class="block text-slate-600">长文案 description</label>
                <textarea id="type-desc" rows="4" class="w-full border rounded-lg px-3 py-2"></textarea>
                <div class="flex gap-2 flex-wrap">
                    <button type="button" id="btn-type-create" class="px-4 py-2 rounded-lg bg-slate-900 text-white">新建</button>
                    <button type="button" id="btn-type-update" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" disabled>保存选中</button>
                    <button type="button" id="btn-type-clear" class="px-4 py-2 rounded-lg border border-slate-300">清空表单</button>
                </div>
                <input type="hidden" id="type-selected-id" value="" />
            </div>
            <div>
                <p class="text-slate-600 mb-2">当前启用维度数：<span id="dim-count" class="font-mono">—</span></p>
                <div class="border rounded-lg overflow-auto max-h-[480px]">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-100 sticky top-0">
                        <tr>
                            <th class="text-left p-2">code</th>
                            <th class="text-left p-2">名称</th>
                            <th class="text-left p-2">兜底</th>
                            <th class="p-2"></th>
                        </tr>
                        </thead>
                        <tbody id="type-rows"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4 shadow-sm">
        <h2 class="text-lg font-semibold">维度 · 题目 · 选项</h2>
        <p class="text-sm text-slate-600">点选维度后可改文案与排序；题目/选项支持新增与保存。</p>
        <div class="flex flex-wrap gap-3 items-center">
            <label class="text-sm text-slate-600">维度</label>
            <select id="dim-select" class="border rounded-lg px-3 py-2 min-w-[240px]"></select>
        </div>
        <div id="dim-panel" class="grid md:grid-cols-2 gap-4 text-sm hidden">
            <div class="space-y-2">
                <label class="block text-slate-600">code</label>
                <input id="dim-code" class="w-full border rounded-lg px-3 py-2 font-mono text-xs" />
                <label class="block text-slate-600">名称</label>
                <input id="dim-name" class="w-full border rounded-lg px-3 py-2" />
                <label class="block text-slate-600">分组 model_group</label>
                <input id="dim-group" class="w-full border rounded-lg px-3 py-2" />
                <label class="block text-slate-600">排序</label>
                <input id="dim-sort" type="number" class="w-full border rounded-lg px-3 py-2" />
                <label class="inline-flex items-center gap-2"><input type="checkbox" id="dim-active" /> 启用</label>
                <label class="block text-slate-600">L 文案</label>
                <textarea id="dim-el" rows="2" class="w-full border rounded-lg px-3 py-2"></textarea>
                <label class="block text-slate-600">M 文案</label>
                <textarea id="dim-em" rows="2" class="w-full border rounded-lg px-3 py-2"></textarea>
                <label class="block text-slate-600">H 文案</label>
                <textarea id="dim-eh" rows="2" class="w-full border rounded-lg px-3 py-2"></textarea>
                <button type="button" id="btn-dim-save" class="px-4 py-2 rounded-lg bg-slate-900 text-white">保存维度</button>
            </div>
            <div class="space-y-3">
                <div class="border rounded-lg p-3 space-y-2 bg-slate-50">
                    <div class="font-medium">新建题目</div>
                    <textarea id="q-new-body" rows="2" class="w-full border rounded-lg px-3 py-2" placeholder="题干"></textarea>
                    <div class="flex gap-2 flex-wrap">
                        <input id="q-new-sort" type="number" class="border rounded-lg px-2 py-1 w-24" placeholder="排序" />
                        <button type="button" id="btn-q-create" class="px-3 py-1.5 rounded-lg bg-teal-600 text-white text-sm">添加题目</button>
                    </div>
                </div>
                <div id="questions-wrap" class="space-y-3"></div>
            </div>
        </div>
    </section>
</div>

<script>
(function () {
    const tokenMeta = document.querySelector('meta[name="personality-quiz-admin-token"]');
    const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
    const statusEl = document.getElementById('status');
    let state = { dimensions: [], types: [], settings: {} };

    function setStatus(msg, ok) {
        statusEl.textContent = msg || '';
        statusEl.className = 'text-sm min-h-[1.25rem] ' + (ok === false ? 'text-red-600' : 'text-emerald-700');
    }

    function api(path, opts) {
        const headers = Object.assign({
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Personality-Quiz-Admin-Token': token
        }, (opts && opts.headers) || {});
        return fetch('{{ url('/api/personality-quiz/admin') }}' + path, Object.assign({}, opts, { headers }))
            .then(async r => {
                const text = await r.text();
                let data = null;
                try { data = text ? JSON.parse(text) : null; } catch (e) { data = { message: text }; }
                if (!r.ok) {
                    const msg = (data && (data.message || (data.errors && JSON.stringify(data.errors)))) || ('HTTP ' + r.status);
                    throw new Error(msg);
                }
                return data;
            });
    }

    function renderTypes() {
        const tbody = document.getElementById('type-rows');
        tbody.innerHTML = '';
        state.types.forEach(t => {
            const tr = document.createElement('tr');
            tr.className = 'border-t border-slate-100 hover:bg-slate-50';
            tr.innerHTML = '<td class="p-2 font-mono">' + t.code + '</td>' +
                '<td class="p-2">' + (t.cn_name || '') + '</td>' +
                '<td class="p-2">' + (t.is_fallback ? '是' : '') + '</td>' +
                '<td class="p-2 text-right space-x-2">' +
                '<button type="button" class="text-indigo-600 underline btn-pick-type" data-id="' + t.id + '">编辑</button>' +
                '<button type="button" class="text-red-600 underline btn-del-type" data-id="' + t.id + '">删</button>' +
                '</td>';
            tbody.appendChild(tr);
        });
        tbody.querySelectorAll('.btn-pick-type').forEach(btn => {
            btn.addEventListener('click', () => pickType(Number(btn.getAttribute('data-id'))));
        });
        tbody.querySelectorAll('.btn-del-type').forEach(btn => {
            btn.addEventListener('click', () => delType(Number(btn.getAttribute('data-id'))));
        });
    }

    function pickType(id) {
        const t = state.types.find(x => x.id === id);
        if (!t) return;
        document.getElementById('type-selected-id').value = String(t.id);
        document.getElementById('type-code').value = t.code || '';
        document.getElementById('type-cn-name').value = t.cn_name || '';
        document.getElementById('type-intro').value = t.intro || '';
        document.getElementById('type-image-url').value = t.image_url || '';
        document.getElementById('type-pattern').value = t.pattern || '';
        document.getElementById('type-fallback').checked = !!t.is_fallback;
        document.getElementById('type-active').checked = !!t.is_active;
        document.getElementById('type-sort').value = t.sort_order ?? 0;
        document.getElementById('type-desc').value = t.description || '';
        document.getElementById('btn-type-update').disabled = false;
    }

    function clearTypeForm() {
        document.getElementById('type-selected-id').value = '';
        document.getElementById('type-code').value = '';
        document.getElementById('type-cn-name').value = '';
        document.getElementById('type-intro').value = '';
        document.getElementById('type-image-url').value = '';
        document.getElementById('type-pattern').value = '';
        document.getElementById('type-fallback').checked = false;
        document.getElementById('type-active').checked = true;
        document.getElementById('type-sort').value = '0';
        document.getElementById('type-desc').value = '';
        document.getElementById('btn-type-update').disabled = true;
    }

    function renderDimSelect() {
        const sel = document.getElementById('dim-select');
        const cur = sel.value;
        sel.innerHTML = '<option value="">请选择维度</option>';
        state.dimensions.forEach(d => {
            const opt = document.createElement('option');
            opt.value = String(d.id);
            opt.textContent = d.code + ' · ' + d.name;
            sel.appendChild(opt);
        });
        if (cur && [...sel.options].some(o => o.value === cur)) sel.value = cur;
    }

    function renderQuestions(dim) {
        const wrap = document.getElementById('questions-wrap');
        wrap.innerHTML = '';
        if (!dim || !dim.questions) return;
        dim.questions.forEach(q => {
            const box = document.createElement('div');
            box.className = 'border rounded-lg p-3 space-y-2';
            let optsHtml = '';
            (q.options || []).forEach(o => {
                optsHtml += '<div class="flex flex-wrap gap-2 items-center border-t border-slate-100 pt-2">' +
                    '<span class="text-slate-500 w-16">opt #' + o.id + '</span>' +
                    '<input data-opt-id="' + o.id + '" data-qid="' + q.id + '" class="opt-label flex-1 min-w-[120px] border rounded px-2 py-1" value="' + (o.label || '').replace(/"/g, '&quot;') + '" />' +
                    '<input data-opt-id="' + o.id + '" type="number" class="opt-val w-20 border rounded px-2 py-1" value="' + o.value + '" />' +
                    '<input data-opt-id="' + o.id + '" type="number" class="opt-sort w-20 border rounded px-2 py-1" placeholder="序" value="' + o.sort_order + '" />' +
                    '<button type="button" class="text-xs px-2 py-1 rounded bg-slate-800 text-white btn-save-opt" data-id="' + o.id + '">存选项</button>' +
                    '<button type="button" class="text-xs text-red-600 underline btn-del-opt" data-id="' + o.id + '">删</button>' +
                    '</div>';
            });
            box.innerHTML = '<div class="font-medium">题目 #' + q.id + '</div>' +
                '<textarea class="q-body w-full border rounded-lg px-3 py-2" rows="2" data-qid="' + q.id + '">' + (q.body || '') + '</textarea>' +
                '<div class="flex gap-2 flex-wrap items-center">' +
                '<input type="number" class="q-sort border rounded px-2 py-1 w-24" data-qid="' + q.id + '" value="' + q.sort_order + '" />' +
                '<label class="inline-flex items-center gap-1 text-xs"><input type="checkbox" class="q-active" data-qid="' + q.id + '" ' + (q.is_active ? 'checked' : '') + '/> 启用</label>' +
                '<button type="button" class="text-xs px-2 py-1 rounded bg-indigo-600 text-white btn-save-q" data-id="' + q.id + '">保存题目</button>' +
                '<button type="button" class="text-xs text-red-600 underline btn-del-q" data-id="' + q.id + '">删题目</button>' +
                '</div>' +
                '<div class="space-y-1">' + optsHtml + '</div>' +
                '<div class="pt-2 border-t border-slate-200 space-y-1">' +
                '<div class="text-xs text-slate-600">新增选项</div>' +
                '<div class="flex flex-wrap gap-2 items-center">' +
                '<input class="new-opt-label border rounded px-2 py-1 flex-1 min-w-[100px]" placeholder="标签" data-qid="' + q.id + '" />' +
                '<input class="new-opt-val border rounded px-2 py-1 w-20" type="number" value="2" data-qid="' + q.id + '" />' +
                '<input class="new-opt-sort border rounded px-2 py-1 w-20" type="number" value="9" data-qid="' + q.id + '" />' +
                '<button type="button" class="text-xs px-2 py-1 rounded bg-teal-600 text-white btn-new-opt" data-qid="' + q.id + '">加选项</button>' +
                '</div></div>';
            wrap.appendChild(box);
        });

        wrap.querySelectorAll('.btn-save-q').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = Number(btn.getAttribute('data-id'));
                const bodyEl = wrap.querySelector('.q-body[data-qid="' + id + '"]');
                const sortEl = wrap.querySelector('.q-sort[data-qid="' + id + '"]');
                const activeEl = wrap.querySelector('.q-active[data-qid="' + id + '"]');
                api('/questions/' + id, {
                    method: 'PATCH',
                    body: JSON.stringify({
                        body: bodyEl.value,
                        sort_order: Number(sortEl.value || 0),
                        is_active: activeEl.checked
                    })
                }).then(() => loadAll()).then(() => setStatus('题目已保存', true)).catch(e => setStatus(e.message, false));
            });
        });
        wrap.querySelectorAll('.btn-del-q').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = Number(btn.getAttribute('data-id'));
                if (!confirm('删除题目 #' + id + '？')) return;
                api('/questions/' + id, { method: 'DELETE' }).then(() => loadAll()).then(() => setStatus('题目已删除', true)).catch(e => setStatus(e.message, false));
            });
        });
        wrap.querySelectorAll('.btn-save-opt').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = Number(btn.getAttribute('data-id'));
                const row = btn.closest('div');
                const label = row.querySelector('.opt-label').value;
                const value = Number(row.querySelector('.opt-val').value);
                const sort_order = Number(row.querySelector('.opt-sort').value || 0);
                api('/options/' + id, { method: 'PATCH', body: JSON.stringify({ label, value, sort_order }) })
                    .then(() => loadAll()).then(() => setStatus('选项已保存', true)).catch(e => setStatus(e.message, false));
            });
        });
        wrap.querySelectorAll('.btn-del-opt').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = Number(btn.getAttribute('data-id'));
                if (!confirm('删除选项 #' + id + '？')) return;
                api('/options/' + id, { method: 'DELETE' }).then(() => loadAll()).then(() => setStatus('选项已删除', true)).catch(e => setStatus(e.message, false));
            });
        });
        wrap.querySelectorAll('.btn-new-opt').forEach(btn => {
            btn.addEventListener('click', () => {
                const qid = Number(btn.getAttribute('data-qid'));
                const host = btn.closest('div').parentElement;
                const label = host.querySelector('.new-opt-label[data-qid="' + qid + '"]').value;
                const value = Number(host.querySelector('.new-opt-val[data-qid="' + qid + '"]').value);
                const sort_order = Number(host.querySelector('.new-opt-sort[data-qid="' + qid + '"]').value || 0);
                api('/options', { method: 'POST', body: JSON.stringify({ personality_question_id: qid, label, value, sort_order }) })
                    .then(() => loadAll()).then(() => setStatus('选项已添加', true)).catch(e => setStatus(e.message, false));
            });
        });
    }

    function loadDimIntoForm(id) {
        const dim = state.dimensions.find(d => d.id === id);
        const panel = document.getElementById('dim-panel');
        if (!dim) { panel.classList.add('hidden'); return; }
        panel.classList.remove('hidden');
        document.getElementById('dim-code').value = dim.code || '';
        document.getElementById('dim-name').value = dim.name || '';
        document.getElementById('dim-group').value = dim.model_group || '';
        document.getElementById('dim-sort').value = dim.sort_order ?? 0;
        document.getElementById('dim-active').checked = !!dim.is_active;
        document.getElementById('dim-el').value = dim.explanation_l || '';
        document.getElementById('dim-em').value = dim.explanation_m || '';
        document.getElementById('dim-eh').value = dim.explanation_h || '';
        renderQuestions(dim);
    }

    function loadAll() {
        return api('/bootstrap').then(data => {
            state.dimensions = data.dimensions || [];
            state.types = data.types || [];
            state.settings = data.settings || {};
            document.getElementById('dim-count').textContent = String(data.active_dimension_count ?? '—');
            document.getElementById('set-threshold').value = state.settings.low_match_threshold ?? '';
            document.getElementById('set-enabled').checked = String(state.settings.enabled ?? '1') !== '0';
            renderTypes();
            renderDimSelect();
            const sel = document.getElementById('dim-select');
            if (sel.value) loadDimIntoForm(Number(sel.value));
            else document.getElementById('dim-panel').classList.add('hidden');
            setStatus('已加载', true);
        }).catch(e => setStatus(e.message, false));
    }

    document.getElementById('btn-reload').addEventListener('click', loadAll);

    document.getElementById('btn-save-setting').addEventListener('click', () => {
        const value = String(document.getElementById('set-threshold').value || '');
        const enabled = document.getElementById('set-enabled').checked ? '1' : '0';
        api('/settings', { method: 'PUT', body: JSON.stringify({ key: 'low_match_threshold', value }) })
            .then(() => api('/settings', { method: 'PUT', body: JSON.stringify({ key: 'enabled', value: enabled }) }))
            .then(() => loadAll())
            .then(() => setStatus('设置已保存', true))
            .catch(e => setStatus(e.message, false));
    });

    document.getElementById('btn-type-create').addEventListener('click', () => {
        const payload = {
            code: document.getElementById('type-code').value.trim(),
            cn_name: document.getElementById('type-cn-name').value.trim(),
            intro: document.getElementById('type-intro').value.trim() || null,
            image_url: document.getElementById('type-image-url').value.trim() || null,
            description: document.getElementById('type-desc').value.trim() || null,
            pattern: document.getElementById('type-pattern').value.trim() || null,
            is_fallback: document.getElementById('type-fallback').checked,
            is_active: document.getElementById('type-active').checked,
            sort_order: Number(document.getElementById('type-sort').value || 0)
        };
        api('/types', { method: 'POST', body: JSON.stringify(payload) })
            .then(() => { clearTypeForm(); return loadAll(); })
            .then(() => setStatus('类型已创建', true)).catch(e => setStatus(e.message, false));
    });

    document.getElementById('btn-type-update').addEventListener('click', () => {
        const id = document.getElementById('type-selected-id').value;
        if (!id) return;
        const payload = {
            code: document.getElementById('type-code').value.trim(),
            cn_name: document.getElementById('type-cn-name').value.trim(),
            intro: document.getElementById('type-intro').value.trim() || null,
            image_url: document.getElementById('type-image-url').value.trim() || null,
            description: document.getElementById('type-desc').value.trim() || null,
            pattern: document.getElementById('type-pattern').value.trim() || null,
            is_fallback: document.getElementById('type-fallback').checked,
            is_active: document.getElementById('type-active').checked,
            sort_order: Number(document.getElementById('type-sort').value || 0)
        };
        api('/types/' + id, { method: 'PATCH', body: JSON.stringify(payload) })
            .then(() => loadAll()).then(() => setStatus('类型已更新', true)).catch(e => setStatus(e.message, false));
    });

    document.getElementById('btn-type-clear').addEventListener('click', clearTypeForm);

    function delType(id) {
        if (!confirm('删除类型 #' + id + '？')) return;
        api('/types/' + id, { method: 'DELETE' }).then(() => loadAll()).then(() => setStatus('类型已删除', true)).catch(e => setStatus(e.message, false));
    }

    document.getElementById('dim-select').addEventListener('change', () => {
        const v = document.getElementById('dim-select').value;
        if (!v) { document.getElementById('dim-panel').classList.add('hidden'); return; }
        loadDimIntoForm(Number(v));
    });

    document.getElementById('btn-dim-save').addEventListener('click', () => {
        const id = Number(document.getElementById('dim-select').value);
        if (!id) return;
        const payload = {
            code: document.getElementById('dim-code').value.trim(),
            name: document.getElementById('dim-name').value.trim(),
            model_group: document.getElementById('dim-group').value.trim() || null,
            sort_order: Number(document.getElementById('dim-sort').value || 0),
            is_active: document.getElementById('dim-active').checked,
            explanation_l: document.getElementById('dim-el').value,
            explanation_m: document.getElementById('dim-em').value,
            explanation_h: document.getElementById('dim-eh').value
        };
        api('/dimensions/' + id, { method: 'PATCH', body: JSON.stringify(payload) })
            .then(() => loadAll()).then(() => {
                document.getElementById('dim-select').value = String(id);
                loadDimIntoForm(id);
                setStatus('维度已保存', true);
            }).catch(e => setStatus(e.message, false));
    });

    document.getElementById('btn-q-create').addEventListener('click', () => {
        const dimId = Number(document.getElementById('dim-select').value);
        if (!dimId) return;
        const body = document.getElementById('q-new-body').value.trim();
        const sort_order = Number(document.getElementById('q-new-sort').value || 0);
        api('/questions', { method: 'POST', body: JSON.stringify({ personality_dimension_id: dimId, body, sort_order, is_active: true }) })
            .then(() => { document.getElementById('q-new-body').value = ''; return loadAll(); })
            .then(() => {
                document.getElementById('dim-select').value = String(dimId);
                loadDimIntoForm(dimId);
                setStatus('题目已添加', true);
            }).catch(e => setStatus(e.message, false));
    });

    loadAll();
})();
</script>
</body>
</html>
