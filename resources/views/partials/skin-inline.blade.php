{{-- 皮肤初始化脚本（防止闪烁） --}}
<script>
(function() {
    // 从 localStorage 读取上次选择的皮肤
    const savedSkin = localStorage.getItem('preferred_skin');
    if (savedSkin) {
        document.documentElement.setAttribute('data-skin', savedSkin);
    }
})();
</script>
