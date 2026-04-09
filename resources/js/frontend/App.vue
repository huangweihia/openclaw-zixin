<script setup>
import { onMounted } from 'vue';

// 页面加载时恢复皮肤
onMounted(() => {
    const savedSkin = localStorage.getItem('preferred_skin');
    if (savedSkin) {
        document.documentElement.setAttribute('data-skin', savedSkin);
    }
});
</script>

<template>
    <div id="app">
        <router-view />
    </div>
</template>

<style>
/* 全局 CSS 变量 - 默认皮肤（深空蓝） */
:root,
:root:not([data-skin]),
:root[data-skin="style1"] {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --primary-light: #818cf8;
    --secondary: #ec4899;
    --gradient-primary: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);
    --dark: #0f172a;
    --dark-light: #1e293b;
    --gray: #64748b;
    --gray-light: #94a3b8;
    --light: #f1f5f9;
    --white: #ffffff;
}

/* 商务蓝 */
:root[data-skin="style2"] {
    --primary: #3b82f6;
    --primary-dark: #2563eb;
    --primary-light: #60a5fa;
    --secondary: #1e40af;
    --gradient-primary: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
    --dark: #1e3a8a;
    --dark-light: #1e40af;
    --gray: #6b7280;
    --gray-light: #9ca3af;
    --light: #f3f4f6;
    --white: #ffffff;
}

/* 清新绿 */
:root[data-skin="style3"] {
    --primary: #10b981;
    --primary-dark: #059669;
    --primary-light: #34d399;
    --secondary: #14b8a6;
    --gradient-primary: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);
    --dark: #064e3b;
    --dark-light: #065f46;
    --gray: #6b7280;
    --gray-light: #9ca3af;
    --light: #d1fae5;
    --white: #ffffff;
}

/* 暖橙风 */
:root[data-skin="style4"] {
    --primary: #f59e0b;
    --primary-dark: #d97706;
    --primary-light: #fbbf24;
    --secondary: #ea580c;
    --gradient-primary: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
    --dark: #7c2d12;
    --dark-light: #9a3412;
    --gray: #6b7280;
    --gray-light: #9ca3af;
    --light: #fef3c7;
    --white: #ffffff;
}

/* 暗夜黑 */
:root[data-skin="style5"] {
    --primary: #000000;
    --primary-dark: #1a1a1a;
    --primary-light: #404040;
    --secondary: #262626;
    --gradient-primary: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
    --dark: #000000;
    --dark-light: #0a0a0a;
    --gray: #404040;
    --gray-light: #737373;
    --light: #1a1a1a;
    --white: #ffffff;
}

/* 深空蓝首页 */
:root[data-skin="style6"] {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --primary-light: #818cf8;
    --secondary: #ec4899;
    --gradient-primary: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);
    --dark: #0f172a;
    --dark-light: #1e293b;
    --gray: #64748b;
    --gray-light: #94a3b8;
    --light: #f1f5f9;
    --white: #ffffff;
}

/* 护眼绿首页 */
:root[data-skin="style7"] {
    --primary: #10b981;
    --primary-dark: #059669;
    --primary-light: #34d399;
    --secondary: #14b8a6;
    --gradient-primary: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);
    --dark: #0c1a12;
    --dark-light: #14291f;
    --gray: #5c7c70;
    --gray-light: #8ba89a;
    --light: #e8f5e9;
    --white: #ffffff;
}

/* 全局重置 */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    font-size: 16px;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

body {
    font-family: 'PingFang SC', 'Microsoft YaHei', 'Helvetica Neue', Arial, sans-serif;
    background: var(--light, #f1f5f9);
    color: #1e293b;
    line-height: 1.6;
}

#app {
    min-height: 100vh;
}

/* 滚动条美化 */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(148, 163, 184, 0.1);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: var(--gray, #64748b);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--gray-light, #94a3b8);
}
</style>
