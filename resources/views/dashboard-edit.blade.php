@extends('layouts.site')

@section('title', '编辑资料 — OpenClaw 智信')

@section('content')
    <h1 class="text-2xl md:text-3xl font-bold mb-8 oc-heading">编辑资料</h1>

    @include('partials.dashboard-profile-forms')
@endsection

@push('scripts')
    <script>
        (function () {
            const np = document.getElementById('new-pw');
            const st = document.getElementById('dash-pw-strength');
            if (np && st) {
                np.addEventListener('input', function () {
                    var n = np.value.length;
                    if (!n) {
                        st.textContent = '';
                        return;
                    }
                    if (n < 6) st.textContent = '强度：弱（至少 6 位）';
                    else if (n <= 10) st.textContent = '强度：中';
                    else st.textContent = '强度：强';
                });
            }

            const sendBtn = document.getElementById('dash-email-send');
            const emailEl = document.getElementById('dash-new-email');
            if (sendBtn && emailEl) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                let cooldown = 0;
                let timer = null;
                sendBtn.addEventListener('click', async function () {
                    const email = emailEl.value.trim();
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        alert('请先填写正确的新邮箱');
                        return;
                    }
                    if (cooldown > 0) return;
                    sendBtn.disabled = true;
                    try {
                        const res = await fetch('{{ url('/profile/email/send-code') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({ email }),
                        });
                        const data = await res.json().catch(function () {
                            return {};
                        });
                        if (!res.ok) {
                            alert(data.errors?.email?.[0] || data.message || '发送失败');
                            sendBtn.disabled = false;
                            return;
                        }
                        cooldown = 60;
                        sendBtn.textContent = '已发送（60s）';
                        timer = setInterval(function () {
                            cooldown--;
                            if (cooldown <= 0) {
                                clearInterval(timer);
                                sendBtn.textContent = '获取验证码';
                                sendBtn.disabled = false;
                            } else {
                                sendBtn.textContent = '已发送（' + cooldown + 's）';
                            }
                        }, 1000);
                    } catch (e) {
                        alert('网络错误');
                        sendBtn.disabled = false;
                    }
                });
            }
        })();
    </script>
@endpush
