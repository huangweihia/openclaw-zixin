<div id="oc-pq-root" class="hidden fixed inset-0 z-[80] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" aria-hidden="true">
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[92vh] overflow-hidden flex flex-col border border-slate-200">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 bg-slate-50">
            <div class="font-semibold text-slate-900">趣味人格测试</div>
            <button type="button" id="oc-pq-close" class="text-slate-500 hover:text-slate-800 text-2xl leading-none px-2" aria-label="关闭">&times;</button>
        </div>
        <div id="oc-pq-body" class="p-5 overflow-y-auto text-sm text-slate-700 space-y-4"></div>
        <div class="px-5 py-3 border-t border-slate-100 flex justify-end gap-2 bg-white">
            <button type="button" id="oc-pq-back" class="hidden px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm">上一步</button>
            <button type="button" id="oc-pq-next" class="px-4 py-2 rounded-lg bg-teal-600 text-white text-sm disabled:opacity-40" disabled>开始</button>
        </div>
    </div>
</div>

<script>
(function () {
    var openBtn = document.getElementById('oc-pq-open');
    var root = document.getElementById('oc-pq-root');
    if (!openBtn || !root) return;

    var bodyEl = document.getElementById('oc-pq-body');
    var btnNext = document.getElementById('oc-pq-next');
    var btnBack = document.getElementById('oc-pq-back');
    var btnClose = document.getElementById('oc-pq-close');

    var apiBase = @json(url('/api/personality-quiz'));
    var registerUrl = @json(route('register'));
    var csrfToken = (function () {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    })();

    var LS_KEY = 'oc_pq_guest_token';
    function ensureGuestToken() {
        try {
            var t = localStorage.getItem(LS_KEY);
            if (t && /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(t)) {
                return t;
            }
            if (typeof crypto !== 'undefined' && crypto.randomUUID) {
                t = crypto.randomUUID();
            } else {
                t = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                    var r = Math.random() * 16 | 0;
                    var v = c === 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                });
            }
            localStorage.setItem(LS_KEY, t);
            return t;
        } catch (e) {
            return null;
        }
    }

    function isLoggedIn() {
        var m = document.querySelector('meta[name="user-id"]');
        return !!(m && m.getAttribute('content'));
    }

    var state = {
        step: 'loading',
        payload: null,
        index: 0,
        answers: {},
        guestToken: null
    };

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function setOpen(on) {
        root.classList.toggle('hidden', !on);
        root.setAttribute('aria-hidden', on ? 'false' : 'true');
        if (on) document.documentElement.style.overflow = 'hidden';
        else document.documentElement.style.overflow = '';
    }

    function render() {
        btnBack.classList.add('hidden');
        btnNext.disabled = false;
        btnNext.textContent = '下一步';

        if (state.step === 'loading') {
            bodyEl.innerHTML = '<p class="text-slate-600">加载题库…</p>';
            btnNext.disabled = true;
            btnNext.textContent = '请稍候';
            return;
        }

        if (state.step === 'intro') {
            var disc = state.payload && state.payload.disclaimer ? esc(state.payload.disclaimer) : '';
            var gp = state.payload && state.payload.guest_play ? state.payload.guest_play : {};
            var can = !!gp.can_play;
            var reg = esc(gp.register_url || registerUrl);
            var introMain = '<div class="space-y-2">' +
                '<p class="text-base text-slate-900 font-semibold">共 ' + (state.payload.questions || []).length + ' 题，按直觉选最像你的即可。</p>' +
                '<ul class="text-sm text-slate-600 list-disc pl-5 space-y-1">' +
                '<li>无需登录即可首次体验</li>' +
                '<li>每题 3 个选项，选最像你的一项</li>' +
                '<li>结果仅供娱乐参考</li>' +
                '</ul>' +
                '</div>';
            if (!can) {
                var msg = gp.message ? esc(gp.message) : '每位游客仅可完整体验一次，注册账号后可再次参与。';
                introMain = '<p class="text-base text-amber-800 font-medium">' + msg + '</p>' +
                    '<p class="text-sm text-slate-600 mt-3">网站<strong>无需登录</strong>即可首次体验；再次参与请先注册并登录。</p>' +
                    '<a href="' + reg + '" class="mt-4 inline-flex px-4 py-2 rounded-lg bg-teal-600 text-white text-sm no-underline">去注册</a>';
            }
            bodyEl.innerHTML = introMain + (disc ? '<p class="text-xs text-slate-500 mt-2">' + disc + '</p>' : '');
            btnNext.disabled = !can;
            btnNext.textContent = can ? '开始答题' : '关闭';
            return;
        }

        if (state.step === 'question') {
            var qs = state.payload.questions || [];
            var q = qs[state.index];
            if (!q) {
                state.step = 'submitting';
                render();
                return;
            }
            btnBack.classList.remove('hidden');
            var opts = (q.options || []).map(function (o) {
                var checked = String(state.answers[q.id]) === String(o.value) ? ' checked' : '';
                return '<label class="flex gap-3 items-start p-3 rounded-xl border border-slate-200 hover:border-teal-400 cursor-pointer">' +
                    '<input type="radio" name="oc_pq_opt" class="mt-1" data-value="' + esc(o.value) + '"' + checked + ' />' +
                    '<span>' + esc(o.label) + '</span></label>';
            }).join('');
            bodyEl.innerHTML = '<div class="text-xs text-slate-500 mb-1">第 ' + (state.index + 1) + ' / ' + qs.length + ' 题</div>' +
                '<div class="text-base font-medium text-slate-900 mb-3">' + esc(q.body) + '</div>' +
                '<div class="space-y-2">' + opts + '</div>';
            btnNext.disabled = !state.answers[q.id];
            btnNext.textContent = state.index >= qs.length - 1 ? '查看结果' : '下一题';

            bodyEl.querySelectorAll('input[name="oc_pq_opt"]').forEach(function (el) {
                el.addEventListener('change', function () {
                    state.answers[q.id] = Number(el.getAttribute('data-value'));
                    btnNext.disabled = false;
                });
            });
            return;
        }

        if (state.step === 'submitting') {
            bodyEl.innerHTML = '<p class="text-slate-600">正在计算结果…</p>';
            btnNext.disabled = true;
            btnBack.classList.add('hidden');
            var submitBody = { answers: state.answers };
            if (!isLoggedIn()) {
                if (!state.guestToken) {
                    state.step = 'error';
                    state.errMsg = '无法识别游客身份，请关闭后重试。';
                    render();
                    return;
                }
                submitBody.guest_token = state.guestToken;
            }
            fetch(apiBase + '/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                credentials: 'same-origin',
                body: JSON.stringify(submitBody)
            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
                .then(function (res) {
                    if (!res.ok) {
                        state.step = 'error';
                        state.errMsg = (res.j && res.j.message) ? res.j.message : '提交失败';
                        if (res.j && res.j.register_url) {
                            state.errRegisterUrl = res.j.register_url;
                        }
                        render();
                        return;
                    }
                    state.step = 'result';
                    state.result = res.j;
                    render();
                }).catch(function () {
                    state.step = 'error';
                    state.errMsg = '网络异常';
                    render();
                });
            return;
        }

        if (state.step === 'error') {
            var errHtml = '<p class="text-red-600">' + esc(state.errMsg || '出错') + '</p>';
            if (state.errRegisterUrl) {
                errHtml += '<a href="' + esc(state.errRegisterUrl) + '" class="mt-3 inline-flex px-4 py-2 rounded-lg bg-teal-600 text-white text-sm no-underline">去注册</a>';
            }
            bodyEl.innerHTML = errHtml;
            btnNext.textContent = '关闭';
            btnNext.disabled = false;
            return;
        }

        if (state.step === 'result' && state.result) {
            var fin = state.result.final || {};
            var m = state.result.match || {};
            var lines = [];
            lines.push('<div class="grid md:grid-cols-2 gap-4">');
            lines.push('<div class="rounded-xl bg-teal-50 border border-teal-100 p-4 space-y-2">');
            lines.push('<div class="text-xs text-teal-800 uppercase tracking-wide">' + esc(fin.code || '') + '</div>');
            lines.push('<div class="text-2xl font-bold text-slate-900">' + esc(fin.cn_name || '') + '</div>');
            if (fin.intro) lines.push('<div class="text-sm text-slate-700">' + esc(fin.intro) + '</div>');
            var sim = m.display_similarity;
            if (sim != null) {
                var simLine = '标准库最接近参考：约 ' + esc(String(sim)) + '%';
                if (m.used_fallback) {
                    simLine += '（已启用兜底结果类型）';
                    if (m.best_standard_code) {
                        simLine += ' 最近似代码：' + esc(m.best_standard_code);
                    }
                }
                lines.push('<div class="text-xs text-slate-600 mt-2">' + simLine + '</div>');
            }
            lines.push('</div>'); // left card

            if (fin.image_url) {
                lines.push('<div class="rounded-xl border border-slate-200 overflow-hidden bg-white">');
                lines.push('<img alt="" src="' + esc(fin.image_url) + '" class="w-full h-[220px] object-cover" loading="lazy" />');
                lines.push('<div class="p-3 text-xs text-slate-500">结果配图可在后台配置（image_url）。</div>');
                lines.push('</div>');
            } else {
                lines.push('<div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">暂无结果图，可在后台为该类型填写 image_url。</div>');
            }
            lines.push('</div>'); // grid

            if (fin.description) {
                lines.push('<p class="text-sm leading-relaxed text-slate-700 whitespace-pre-wrap">' + esc(fin.description) + '</p>');
            }
            var dims = state.result.dimensions || [];
            if (dims.length) {
                lines.push('<div class="text-xs font-semibold text-slate-500 mt-2">各维度摘要</div>');
                lines.push('<ul class="space-y-2 max-h-40 overflow-y-auto pr-1">');
                dims.forEach(function (d) {
                    lines.push('<li class="border border-slate-100 rounded-lg p-2"><div class="font-medium text-slate-900">' + esc(d.name) +
                        ' <span class="text-slate-400 font-mono">' + esc(d.level || '') + '</span></div>' +
                        '<div class="text-xs text-slate-600 mt-1">' + esc(d.explanation || '') + '</div></li>');
                });
                lines.push('</ul>');
            }
            bodyEl.innerHTML = lines.join('');
            btnNext.textContent = '完成';
            btnBack.classList.add('hidden');
            return;
        }

        bodyEl.innerHTML = '<p class="text-slate-600">未知状态</p>';
    }

    function resetFlow() {
        var gt = ensureGuestToken();
        state = {
            step: 'loading',
            payload: null,
            index: 0,
            answers: {},
            guestToken: gt,
            errRegisterUrl: null
        };
        render();
        var url = apiBase;
        if (!isLoggedIn()) {
            if (!gt) {
                state.step = 'error';
                state.errMsg = '当前环境无法保存游客标识（例如浏览器禁用本地存储），请注册后使用。';
                render();
                return;
            }
            url += (url.indexOf('?') >= 0 ? '&' : '?') + 'guest_token=' + encodeURIComponent(state.guestToken);
        }
        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                state.payload = data;
                state.step = 'intro';
                render();
            }).catch(function () {
                state.step = 'error';
                state.errMsg = '无法加载题库';
                render();
            });
    }

    openBtn.addEventListener('click', function () {
        setOpen(true);
        resetFlow();
    });
    btnClose.addEventListener('click', function () { setOpen(false); });

    btnNext.addEventListener('click', function () {
        if (state.step === 'intro') {
            var gp = state.payload && state.payload.guest_play ? state.payload.guest_play : {};
            if (!gp.can_play) {
                setOpen(false);
                return;
            }
            state.step = 'question';
            state.index = 0;
            render();
            return;
        }
        if (state.step === 'question') {
            var qs = state.payload.questions || [];
            if (state.index >= qs.length - 1) {
                state.step = 'submitting';
                render();
                return;
            }
            state.index += 1;
            render();
            return;
        }
        if (state.step === 'error' || state.step === 'result') {
            setOpen(false);
            return;
        }
    });

    btnBack.addEventListener('click', function () {
        if (state.step === 'question' && state.index > 0) {
            state.index -= 1;
            render();
        }
    });
})();
</script>
