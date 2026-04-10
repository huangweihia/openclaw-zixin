<div id="oc-pq-root" class="hidden fixed inset-0 z-[80] flex items-end justify-center sm:items-center p-0 sm:p-4 bg-slate-900/50 backdrop-blur-sm" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="oc-pq-title">
    <div class="bg-white shadow-2xl w-full max-w-full md:w-1/2 md:max-w-[min(50vw,48rem)] rounded-t-2xl md:rounded-2xl min-w-0 min-h-0 max-h-[92vh] overflow-hidden flex flex-col border border-slate-200">
        <div class="flex shrink-0 items-center justify-between gap-2 px-4 py-3 sm:px-5 border-b border-slate-100 bg-slate-50">
            <div id="oc-pq-title" class="font-semibold text-slate-900 text-base sm:text-lg truncate pr-2">SBTI</div>
            <button type="button" id="oc-pq-close" class="shrink-0 min-w-[44px] min-h-[44px] inline-flex items-center justify-center text-slate-500 hover:text-slate-800 text-2xl leading-none rounded-lg active:bg-slate-100 touch-manipulation" aria-label="关闭">&times;</button>
        </div>
        <div id="oc-pq-body" class="flex-1 min-h-0 overflow-y-auto overscroll-y-contain p-4 sm:p-5 text-sm text-slate-700 space-y-4 [overflow-anchor:none]"></div>
        <div class="shrink-0 px-4 py-3 sm:px-5 border-t border-slate-100 flex flex-wrap justify-end gap-2 bg-white pb-[max(0.75rem,env(safe-area-inset-bottom))]">
            <button type="button" id="oc-pq-back" class="hidden min-h-[44px] px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm touch-manipulation active:bg-slate-50">上一步</button>
            <button type="button" id="oc-pq-next" class="min-h-[44px] px-4 py-2 rounded-lg bg-teal-600 text-white text-sm disabled:opacity-40 touch-manipulation active:bg-teal-700" disabled>开始</button>
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
    /** 无 image_url 时用站内 SBTI 搞怪卡片图（public/images/sbti-results/*.svg，可后台覆盖或替换同路径 PNG） */
    var ocPqSbtiArtBase = @json(rtrim(asset('images/sbti-results'), '/'));
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
        guestToken: null,
        quizToken: null
    };

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function ocPqPickResultImageUrl(fin) {
        var u = fin && fin.image_url ? String(fin.image_url).trim() : '';
        if (u) return u;
        var base = ocPqSbtiArtBase || '';
        var code = fin && fin.code ? String(fin.code).toUpperCase() : '';
        var slugByCode = { PLANNER: 'planner', EXPLORER: 'explorer', BALANCE: 'balance', WAVE: 'wave', GUARD: 'guard', RUSH: 'rush', MIXED: 'mixed' };
        var slug = slugByCode[code];
        if (base && slug) return base + '/' + slug + '.svg';
        var pool = ['planner', 'explorer', 'balance', 'wave', 'guard', 'rush', 'mixed'];
        var h = 0;
        var c = code || 'SBTI';
        for (var i = 0; i < c.length; i++) {
            h = ((h << 5) - h) + c.charCodeAt(i);
            h |= 0;
        }
        return base ? base + '/' + pool[Math.abs(h) % pool.length] + '.svg' : '';
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
            var reg = esc(gp.register_url || registerUrl);
            var msg = gp.message ? esc(gp.message) : '每位游客仅可完整体验一次，注册账号后可再次参与。';
            var introMain = '<p class="text-base text-amber-800 font-medium">' + msg + '</p>' +
                '<p class="text-sm text-slate-600 mt-3">网站<strong>无需登录</strong>即可首次体验；再次参与请先注册并登录。</p>' +
                '<a href="' + reg + '" class="mt-4 inline-flex px-4 py-2 rounded-lg bg-teal-600 text-white text-sm no-underline">去注册</a>';
            bodyEl.innerHTML = introMain + (disc ? '<p class="text-xs text-slate-500 mt-2">' + disc + '</p>' : '');
            btnNext.disabled = false;
            btnNext.textContent = '关闭';
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
                return '<label class="flex gap-3 items-start p-3 rounded-xl border border-slate-200 hover:border-teal-400 cursor-pointer touch-manipulation active:bg-slate-50">' +
                    '<input type="radio" name="oc_pq_opt" class="mt-1 shrink-0" data-value="' + esc(o.value) + '"' + checked + ' />' +
                    '<span class="break-words text-sm leading-relaxed sm:text-base">' + esc(o.label) + '</span></label>';
            }).join('');
            var disc0 = (state.index === 0 && state.payload && state.payload.disclaimer) ? esc(state.payload.disclaimer) : '';
            var hint = state.index === 0
                ? '<p class="text-xs text-slate-500 mb-2">共 ' + qs.length + ' 题，按直觉选最像你的即可（关闭后再次打开会重新抽题）。</p>' +
                    (disc0 ? '<p class="text-[11px] text-slate-400 mb-2">' + disc0 + '</p>' : '')
                : '';
            bodyEl.innerHTML = hint +
                '<div class="text-xs text-slate-500 mb-1">第 ' + (state.index + 1) + ' / ' + qs.length + ' 题</div>' +
                '<div class="text-base sm:text-lg font-medium text-slate-900 mb-3 break-words leading-snug">' + esc(q.body) + '</div>' +
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
            if (!state.quizToken) {
                state.step = 'error';
                state.errMsg = '题目会话已失效，请关闭后重新开始。';
                render();
                return;
            }
            submitBody.quiz_token = state.quizToken;
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

            var resultImg = ocPqPickResultImageUrl(fin);
            if (resultImg) {
                lines.push('<div class="rounded-xl border border-slate-200 overflow-hidden bg-white">');
                lines.push('<img alt="SBTI 结果氛围图" src="' + esc(resultImg) + '" class="w-full h-[min(40vw,220px)] min-h-[160px] object-cover" loading="lazy" />');
                lines.push('</div>');
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
            quizToken: null,
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
                state.quizToken = data && data.quiz_token ? String(data.quiz_token) : null;
                var gp = data && data.guest_play ? data.guest_play : {};
                var can = !!gp.can_play;
                if (can) {
                    state.step = 'question';
                    state.index = 0;
                } else {
                    state.step = 'intro';
                }
                render();
            }).catch(function () {
                state.step = 'error';
                state.errMsg = '无法加载题库';
                render();
            });
    }

    function openModalAndStart() {
        setOpen(true);
        resetFlow();
    }

    openBtn.addEventListener('click', function () {
        openModalAndStart();
    });
    btnClose.addEventListener('click', function () { setOpen(false); });
    root.addEventListener('click', function (e) {
        if (e.target === root) setOpen(false);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && root && !root.classList.contains('hidden')) {
            setOpen(false);
        }
    });

    // 进入首页且本块已渲染时自动打开弹窗（可点 ×、遮罩或 Esc 关闭）；按钮仍可再次打开
    function scheduleAutoOpen() {
        requestAnimationFrame(function () {
            requestAnimationFrame(openModalAndStart);
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scheduleAutoOpen);
    } else {
        scheduleAutoOpen();
    }

    btnNext.addEventListener('click', function () {
        if (state.step === 'intro') {
            setOpen(false);
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
