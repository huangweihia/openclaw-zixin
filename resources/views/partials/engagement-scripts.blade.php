@push('scripts')
    <script>
        (function () {
            const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const toast = (msg, type) => {
                if (typeof window.ocToast === 'function') window.ocToast(msg, type || 'success');
            };

            document.body.addEventListener('submit', async function (e) {
                const form = e.target.closest('.oc-engage-ajax');
                if (!form) return;
                e.preventDefault();
                const btn = form.querySelector('button[type="submit"]');
                if (btn?.disabled) return;
                if (btn) btn.disabled = true;
                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: new FormData(form),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.status === 419) {
                        toast('登录状态已失效或页面已过期，请刷新后重试', 'error');
                        return;
                    }
                    if (!res.ok || !data.ok) {
                        toast(data.message || data.error || ('操作失败（' + res.status + '）'), 'error');
                        return;
                    }
                    if (data.message) {
                        toast(data.message, 'success');
                    }
                    if (btn) {
                        const onText = btn.getAttribute('data-on-text') || btn.textContent || '';
                        const offText = btn.getAttribute('data-off-text') || btn.textContent || '';
                        if (typeof data.liked !== 'undefined') {
                            btn.textContent = (data.liked ? onText : offText) + (typeof data.count === 'number' ? ` · ${data.count}` : '');
                            btn.classList.toggle('btn-primary', !!data.liked);
                            btn.classList.toggle('btn-secondary', !data.liked);
                        } else if (typeof data.favorited !== 'undefined') {
                            btn.textContent = data.favorited ? onText : offText;
                            if (typeof data.count === 'number') btn.textContent += ` · ${data.count}`;
                            btn.classList.toggle('btn-primary', !!data.favorited);
                            btn.classList.toggle('btn-secondary', !data.favorited);
                        }
                    }
                } catch (err) {
                    toast('网络错误，请稍后重试', 'error');
                } finally {
                    if (btn) btn.disabled = false;
                }
            });
        })();
    </script>
@endpush
