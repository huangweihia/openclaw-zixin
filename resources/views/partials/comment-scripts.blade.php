@push('scripts')
    <script>
        (function () {
            const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const toast = (msg, type) => {
                if (typeof window.ocToast === 'function') {
                    window.ocToast(msg, type || 'success');
                } else {
                    alert(msg);
                }
            };
            let reportUrl = '';

            document.body.addEventListener('click', async function (e) {
                const likeBtn = e.target.closest('.oc-comment-like');
                if (likeBtn) {
                    e.preventDefault();
                    const url = likeBtn.getAttribute('data-url');
                    if (!url) return;
                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({}),
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            toast(data.error || '操作失败', 'error');
                            return;
                        }
                        const cnt = likeBtn.querySelector('.oc-like-count');
                        if (cnt && typeof data.count !== 'undefined') {
                            cnt.textContent = '(' + data.count + ')';
                        }
                        const lab = likeBtn.querySelector('.oc-like-label');
                        if (lab) {
                            lab.textContent = data.liked ? '已赞' : '点赞';
                        }
                        likeBtn.setAttribute('data-liked', data.liked ? '1' : '0');
                    } catch (err) {
                        toast('网络错误，请稍后重试', 'error');
                    }
                    return;
                }

                const repBtn = e.target.closest('.oc-comment-report');
                if (repBtn) {
                    e.preventDefault();
                    reportUrl = repBtn.getAttribute('data-url') || '';
                    const modal = document.getElementById('oc-report-modal');
                    const desc = document.getElementById('oc-report-desc');
                    if (desc) desc.value = '';
                    if (modal) modal.classList.remove('hidden');
                }
            });

            document.body.addEventListener('submit', async function (e) {
                const form = e.target.closest('.oc-comment-form-ajax');
                if (!form) return;
                e.preventDefault();
                const fd = new FormData(form);
                fd.set('ajax', '1');
                const ta = form.querySelector('textarea[name="content"]');
                const val = (ta && ta.value && ta.value.trim()) || '';
                if (!val) {
                    toast('请输入内容', 'error');
                    return;
                }
                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json, text/javascript, */*; q=0.01',
                            'X-CSRF-TOKEN': csrf(),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: fd,
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        toast(data.error || data.message || '发送失败', 'error');
                        return;
                    }
                    if (data.html) {
                        document.querySelector('#comments-list .oc-comments-empty')?.remove();
                        if (data.isReply && data.rootId) {
                            const thread = document.getElementById('comment-thread-' + data.rootId);
                            const list = thread && thread.querySelector('.oc-comment__flat-replies');
                            if (list) {
                                const wrap = document.createElement('div');
                                wrap.innerHTML = data.html.trim();
                                const node = wrap.firstElementChild;
                                if (node) list.appendChild(node);
                            }
                        } else {
                            const listEl = document.getElementById('comments-list');
                            if (listEl) {
                                const wrap = document.createElement('div');
                                wrap.innerHTML = data.html.trim();
                                const node = wrap.firstElementChild;
                                if (node) listEl.insertBefore(node, listEl.firstChild);
                            }
                        }
                    }
                    if (ta) ta.value = '';
                    form.closest('[id^="reply-wrap-"]')?.classList.add('hidden');
                    toast('已发布', 'success');
                } catch (err) {
                    toast('网络错误', 'error');
                }
            });

            const modal = document.getElementById('oc-report-modal');
            const cancel = document.getElementById('oc-report-cancel');
            const submit = document.getElementById('oc-report-submit');
            cancel?.addEventListener('click', function () {
                modal?.classList.add('hidden');
            });
            modal?.addEventListener('click', function (e) {
                if (e.target === modal) modal.classList.add('hidden');
            });
            submit?.addEventListener('click', async function () {
                if (!reportUrl) return;
                const reason = document.getElementById('oc-report-reason')?.value || 'other';
                const descEl = document.getElementById('oc-report-desc');
                const description = (descEl && descEl.value && descEl.value.trim()) || null;
                try {
                    const res = await fetch(reportUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ reason, description }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        toast(data.error || '举报失败', 'error');
                        return;
                    }
                    toast(data.success || '举报成功，感谢反馈', 'success');
                    modal.classList.add('hidden');
                } catch (err) {
                    toast('网络错误，请稍后重试', 'error');
                }
            });
        })();
    </script>
@endpush
