<script>
    (function () {
        function pad(n) {
            return n < 10 ? '0' + n : '' + n;
        }
        function tick(el) {
            var iso = el.getAttribute('data-oc-pricing-countdown');
            if (!iso) {
                return;
            }
            var end = Date.parse(iso);
            if (isNaN(end)) {
                return;
            }
            var span = el.querySelector('.js-oc-cd');
            if (!span) {
                return;
            }
            function run() {
                var ms = end - Date.now();
                if (ms <= 0) {
                    span.textContent = '已结束';
                    return;
                }
                var s = Math.floor(ms / 1000);
                var d = Math.floor(s / 86400);
                s %= 86400;
                var h = Math.floor(s / 3600);
                s %= 3600;
                var m = Math.floor(s / 60);
                var sec = s % 60;
                if (d > 0) {
                    span.textContent = d + ' 天 ' + pad(h) + ':' + pad(m) + ':' + pad(sec);
                } else {
                    span.textContent = pad(h) + ':' + pad(m) + ':' + pad(sec);
                }
            }
            run();
            setInterval(run, 1000);
        }
        document.querySelectorAll('[data-oc-pricing-countdown]').forEach(tick);
    })();
</script>
