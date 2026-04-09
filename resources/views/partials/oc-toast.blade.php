{{-- 全局 Toast（对齐功能清单 00 / 功能 16：右上角、3s、悬停暂停、点击关闭） --}}
<div id="oc-toast-host" class="oc-toast-host" aria-live="polite"></div>
<script>
    (function () {
        window.ocToast = function (message, type) {
            type = type || 'success';
            var host = document.getElementById('oc-toast-host');
            if (!host || message == null || message === '') return;

            var el = document.createElement('div');
            var base = 'oc-toast-item oc-flash oc-flash--dismiss';
            if (type === 'error') {
                el.className = base + ' oc-flash--error';
                el.setAttribute('role', 'alert');
            } else if (type === 'warning') {
                el.className = base + ' oc-flash--warning';
                el.setAttribute('role', 'status');
            } else {
                el.className = base + ' oc-flash--success';
                el.setAttribute('role', 'status');
            }

            var inner = document.createElement('div');
            inner.className = 'oc-toast__row';

            var emoji = document.createElement('span');
            emoji.className = 'oc-toast__emoji';
            emoji.setAttribute('aria-hidden', 'true');
            emoji.textContent = type === 'error' ? '❌' : type === 'warning' ? '⚠️' : '✅';

            var text = document.createElement('span');
            text.className = 'oc-toast__text';
            text.textContent = String(message);

            var close = document.createElement('button');
            close.type = 'button';
            close.className = 'oc-toast__close';
            close.setAttribute('aria-label', '关闭');
            close.innerHTML = '&times;';

            inner.appendChild(emoji);
            inner.appendChild(text);
            inner.appendChild(close);
            el.appendChild(inner);
            host.appendChild(el);

            var hideTimer = null;
            function remove() {
                if (hideTimer) clearTimeout(hideTimer);
                el.style.opacity = '0';
                setTimeout(function () {
                    el.remove();
                }, 280);
            }
            function schedule() {
                if (hideTimer) clearTimeout(hideTimer);
                hideTimer = setTimeout(remove, 3000);
            }

            el.addEventListener('mouseenter', function () {
                if (hideTimer) clearTimeout(hideTimer);
            });
            el.addEventListener('mouseleave', function () {
                schedule();
            });
            close.addEventListener('click', function (e) {
                e.stopPropagation();
                remove();
            });
            el.addEventListener('click', function (e) {
                if (e.target === close || e.target.closest('.oc-toast__close')) return;
                remove();
            });

            schedule();
        };
    })();
</script>
