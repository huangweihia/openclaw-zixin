{{-- 全站：点头像看资料 + 站内留言（需登录）；与评论区 data-oc-user-card 配合 --}}
@include('partials.user-mini-modal')
@push('scripts')
    <script>
        (function () {
            const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const currentUserId = () => document.querySelector('meta[name="user-id"]')?.getAttribute('content');
            const usersBase = @json(rtrim(url('/users'), '/'));
            function escHtml(t) {
                return String(t ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }
            function safeHttpUrl(s) {
                const x = String(s || '').trim();
                return /^https?:\/\//i.test(x) ? x : '';
            }
            const toast = (msg, type) => {
                if (typeof window.ocToast === 'function') {
                    window.ocToast(msg, type || 'success');
                } else {
                    alert(msg);
                }
            };
            let miniUserId = null;

            document.body.addEventListener('click', async function (e) {
                const cardBtn = e.target.closest('[data-oc-user-card]');
                if (!cardBtn) return;
                e.preventDefault();
                const uid = cardBtn.getAttribute('data-oc-user-card');
                if (!uid) return;
                miniUserId = uid;
                const modal = document.getElementById('oc-user-mini-modal');
                const body = document.getElementById('oc-user-mini-body');
                const msgWrap = document.getElementById('oc-user-mini-msg-wrap');
                const msgTa = document.getElementById('oc-user-mini-msg');
                if (!modal || !body) {
                    toast('页面未加载留言组件', 'error');
                    return;
                }
                body.textContent = '加载中…';
                if (msgTa) msgTa.value = '';
                msgWrap?.classList.add('hidden');
                modal.classList.remove('hidden');
                try {
                    const url = usersBase + '/' + encodeURIComponent(uid) + '/snippet';
                    const res = await fetch(url, {
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        body.textContent = data.error || data.message || '加载失败';
                        return;
                    }
                    const avUrl = safeHttpUrl(data.avatar);
                    const av = avUrl
                        ? '<img src="' +
                          String(avUrl).replace(/"/g, '') +
                          '" alt="" class="w-14 h-14 rounded-full object-cover mb-3" />'
                        : '<span class="inline-flex w-14 h-14 rounded-full items-center justify-center text-white font-bold mb-3" style="background:var(--gradient-primary)">' +
                          escHtml((data.name || '?').slice(0, 1)) +
                          '</span>';
                    body.innerHTML =
                        '<div class="flex flex-col items-center text-center">' +
                        av +
                        '<p class="font-semibold oc-heading m-0">' +
                        escHtml(data.name || '') +
                        '</p>' +
                        '<p class="text-xs oc-muted m-1">' +
                        escHtml(data.role_label || '') +
                        '</p>' +
                        '<p class="text-sm oc-heading m-0 mt-2 text-left w-full whitespace-pre-wrap">' +
                        escHtml(data.bio || '暂无简介') +
                        '</p></div>';
                    const self = data.is_self || String(currentUserId()) === String(uid);
                    const logged = !!currentUserId();
                    if (msgWrap) {
                        if (logged && !self) msgWrap.classList.remove('hidden');
                        else msgWrap.classList.add('hidden');
                    }
                } catch (err) {
                    body.textContent = '网络错误';
                }
            });

            const uModal = document.getElementById('oc-user-mini-modal');
            document.getElementById('oc-user-mini-close')?.addEventListener('click', function () {
                uModal?.classList.add('hidden');
            });
            uModal?.addEventListener('click', function (e) {
                if (e.target === uModal) uModal.classList.add('hidden');
            });
            document.getElementById('oc-user-mini-send')?.addEventListener('click', async function () {
                if (!miniUserId) return;
                if (!currentUserId()) {
                    toast('请先登录', 'error');
                    return;
                }
                const text = (document.getElementById('oc-user-mini-msg')?.value || '').trim();
                if (!text) {
                    toast('请输入留言', 'error');
                    return;
                }
                const url = usersBase + '/' + encodeURIComponent(miniUserId) + '/message';
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
                        body: JSON.stringify({ body: text }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        let err = data.error || data.message || '发送失败';
                        if (data.errors && data.errors.body && data.errors.body[0]) {
                            err = data.errors.body[0];
                        }
                        toast(err, 'error');
                        return;
                    }
                    toast(data.message || '留言已发送', 'success');
                    uModal?.classList.add('hidden');
                } catch (err) {
                    toast('网络错误', 'error');
                }
            });
        })();
    </script>
@endpush
