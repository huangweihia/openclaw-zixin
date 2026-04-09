<script setup>
import { ref } from 'vue';
import Skeleton from '../../frontend/components/Skeleton.vue';
import Toast from '../../frontend/components/Toast.vue';
import SkinSwitcher from '../../Components/SkinSwitcher.vue';
import { toast } from '../../frontend/utils/toast';
import loadingDirective from '../../frontend/directives/loading.js';

const vLoading = loadingDirective;

/**
 * 与 docs/功能清单/00-公共功能模块.md、docs/原型图/组件设计文档.md「附、Vue 公共能力对照」一致
 */
const registry = [
    {
        impl: 'Skeleton.vue',
        feature: '功能 20 页面加载骨架屏',
        prototype: '@component-skeleton · §组件 14',
        path: 'resources/js/frontend/components/Skeleton.vue',
        params: 'type, rows, width, height',
    },
    {
        impl: 'Toast.vue',
        feature: '功能 16 Toast 提示',
        prototype: '§五 全局消息通知；附表（无单独 @component-toast）',
        path: 'resources/js/frontend/components/Toast.vue',
        params: 'modelValue, message, type, duration',
    },
    {
        impl: 'toast.js',
        feature: '功能 16 Toast 提示',
        prototype: '同上',
        path: 'resources/js/frontend/utils/toast.js',
        params: 'toast.success / error / warning / info',
    },
    {
        impl: 'v-loading',
        feature: '功能 21 按钮 loading',
        prototype: '@component-loading · §组件 13',
        path: 'resources/js/frontend/directives/loading.js',
        params: 'value 布尔；修饰符 full',
    },
    {
        impl: 'SkinSwitcher.vue',
        feature: '功能 11～15 全局皮肤切换',
        prototype: '§组件 0 顶部导航用户区；00 §四 皮肤',
        path: 'resources/js/Components/SkinSwitcher.vue',
        params: 'variant: navbar | floating',
    },
];

const demoToastVisible = ref(false);
const demoToastMsg = ref('这是一条受控 Toast 示例');
const demoToastType = ref('success');

const btnBusy = ref(false);
const btnFullBusy = ref(false);

function openDemoToast(type) {
    const labels = {
        success: '操作成功（受控 Toast）',
        error: '操作失败（受控 Toast）',
        warning: '请注意（受控 Toast）',
        info: '提示信息（受控 Toast）',
    };
    demoToastType.value = type;
    demoToastMsg.value = labels[type] || labels.info;
    demoToastVisible.value = true;
}

function fireUtilToast(kind) {
    const fn = toast[kind];
    if (typeof fn === 'function') {
        fn(`toast 工具 · ${kind}`);
    }
}

function runBusyDemo() {
    btnBusy.value = true;
    setTimeout(() => {
        btnBusy.value = false;
    }, 1600);
}

function runFullBusyDemo() {
    btnFullBusy.value = true;
    setTimeout(() => {
        btnFullBusy.value = false;
    }, 1600);
}

/** 主滚动容器为 layout 内 [data-admin-scroll-root]，scrollIntoView 会滚到正确区域 */
function scrollToSection(id) {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>

<template>
    <div class="showcase">
        <header class="showcase__head">
            <h1 class="showcase__title">公共组件查看</h1>
            <p class="showcase__lead">
                与
                <code class="inline-code">docs/功能清单/00-公共功能模块.md</code>
                （功能 16、20、21、11～15）及
                <code class="inline-code">docs/原型图/组件设计文档.md</code>
                （附、Vue 公共能力对照 · @component ID）逐项对齐；源码位于
                <code class="inline-code">resources/js</code>
                。Toast / v-loading 依赖 Tailwind 工具类；管理端入口已加载 CDN 并关闭 preflight。
            </p>
            <p class="showcase__notice" role="note">
                首屏主要是<strong>索引表 + Skeleton 演示</strong>，篇幅较长。
                <strong>Toast、v-loading、SkinSwitcher</strong> 在下方，请<strong>向下滑动</strong>主内容区，或点下方锚点跳转。
            </p>
            <nav class="showcase-nav" aria-label="本页章节">
                <button type="button" class="nav-chip" @click="scrollToSection('showcase-registry')">组件索引</button>
                <button type="button" class="nav-chip" @click="scrollToSection('showcase-skeleton')">Skeleton</button>
                <button type="button" class="nav-chip" @click="scrollToSection('showcase-toast')">Toast</button>
                <button type="button" class="nav-chip" @click="scrollToSection('showcase-loading')">v-loading</button>
                <button type="button" class="nav-chip" @click="scrollToSection('showcase-skin')">SkinSwitcher</button>
            </nav>
        </header>

        <section id="showcase-registry" class="card card--anchor">
            <h2 class="card__title">组件索引（功能清单 × 原型 ID × 源码）</h2>
            <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>实现</th>
                        <th>00-公共功能模块</th>
                        <th>组件设计文档</th>
                        <th>源码路径</th>
                        <th>主要入参</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(r, i) in registry" :key="r.path + i">
                        <td class="tbl__name">{{ r.impl }}</td>
                        <td>{{ r.feature }}</td>
                        <td class="tbl__proto">{{ r.prototype }}</td>
                        <td class="mono tbl__path">{{ r.path }}</td>
                        <td class="mono tbl__props">{{ r.params }}</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </section>

        <section id="showcase-skeleton" class="card card--anchor">
            <h2 class="card__title">Skeleton（@component-skeleton · 功能 20）</h2>
            <p class="card__hint">对应原型 §组件 14；组件内联样式，不依赖 Tailwind。</p>
            <div class="demo-grid">
                <div class="demo-box">
                    <h3 class="demo-box__t">type="text"</h3>
                    <Skeleton type="text" :rows="3" />
                </div>
                <div class="demo-box">
                    <h3 class="demo-box__t">type="image"</h3>
                    <Skeleton type="image" height="120px" width="100%" />
                </div>
                <div class="demo-box">
                    <h3 class="demo-box__t">type="card"</h3>
                    <Skeleton type="card" />
                </div>
                <div class="demo-box demo-box--wide">
                    <h3 class="demo-box__t">type="list"</h3>
                    <Skeleton type="list" :rows="3" />
                </div>
            </div>
            <p class="card__more">↓ 下方还有 Toast、v-loading、SkinSwitcher 演示</p>
        </section>

        <section id="showcase-toast" class="card card--anchor">
            <h2 class="card__title">Toast（功能 16 · 组件 + toast 工具）</h2>
            <p class="card__hint">对应 00 §五 功能 16；原型附表（无单独 @component ID）。下方为受控组件与命令式 API（与前台 /test 一致）。</p>
            <div class="row-btns">
                <div class="row-btns__grp">
                    <span class="row-btns__lbl">受控 Toast</span>
                    <button type="button" class="btn btn--sec" @click="openDemoToast('success')">success</button>
                    <button type="button" class="btn btn--sec" @click="openDemoToast('error')">error</button>
                    <button type="button" class="btn btn--sec" @click="openDemoToast('warning')">warning</button>
                    <button type="button" class="btn btn--sec" @click="openDemoToast('info')">info</button>
                </div>
                <div class="row-btns__grp">
                    <span class="row-btns__lbl">toast 工具</span>
                    <button type="button" class="btn btn--sec" @click="fireUtilToast('success')">success</button>
                    <button type="button" class="btn btn--sec" @click="fireUtilToast('error')">error</button>
                    <button type="button" class="btn btn--sec" @click="fireUtilToast('warning')">warning</button>
                    <button type="button" class="btn btn--sec" @click="fireUtilToast('info')">info</button>
                </div>
            </div>
            <Toast v-model="demoToastVisible" :message="demoToastMsg" :type="demoToastType" :duration="4000" />
        </section>

        <section id="showcase-loading" class="card card--anchor">
            <h2 class="card__title">v-loading（@component-loading · 功能 21）</h2>
            <p class="card__hint">对应原型 §组件 13、00 功能 21；本页 script setup 局部注册，与前台 `main.js` 全局注册等价。</p>
            <div class="row-btns">
                <button type="button" class="btn btn--pri" v-loading="btnBusy" @click="runBusyDemo">
                    点我演示按钮 loading
                </button>
                <button type="button" class="btn btn--sec" v-loading:full="btnFullBusy" @click="runFullBusyDemo">
                    点我演示 full 遮罩（1.6s）
                </button>
            </div>
        </section>

        <section id="showcase-skin" class="card card--warn card--anchor">
            <h2 class="card__title">SkinSwitcher（功能 11～15 · 导航区扩展）</h2>
            <p class="card__warn">
                会请求公开接口
                <code class="inline-code">GET /api/skins</code>
                与
                <code class="inline-code">GET /api/skins/current</code>
                ，并向
                <code class="inline-code">document.documentElement</code>
                写入 CSS 变量与
                <code class="inline-code">data-skin</code>
                ，同时写入
                <code class="inline-code">localStorage.preferred_skin</code>
                。若管理后台样式异常，请刷新页面或在前台恢复皮肤后再回来。
            </p>
            <div class="skin-shell">
                <SkinSwitcher variant="navbar" />
            </div>
        </section>
    </div>
</template>

<style scoped>
.showcase {
    max-width: 960px;
    padding-bottom: 3.5rem;
}
.card--anchor {
    scroll-margin-top: 1rem;
}
.showcase__notice {
    margin: 0.85rem 0 0.75rem;
    padding: 0.65rem 0.85rem;
    font-size: 0.82rem;
    line-height: 1.55;
    color: #1e40af;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
}
.showcase-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-bottom: 0.25rem;
}
.nav-chip {
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.35rem 0.65rem;
    border-radius: 999px;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #334155;
    cursor: pointer;
}
.nav-chip:hover {
    border-color: #93c5fd;
    background: #eff6ff;
    color: #1d4ed8;
}
.showcase__head {
    margin-bottom: 1.25rem;
}
.showcase__title {
    margin: 0 0 0.35rem;
    font-size: 1.5rem;
    font-weight: 700;
}
.showcase__lead {
    margin: 0;
    font-size: 0.88rem;
    color: #64748b;
    line-height: 1.55;
    max-width: 52rem;
}
.inline-code {
    font-size: 0.82em;
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
    background: #f1f5f9;
    color: #0f172a;
}
.card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.1rem 1.2rem;
    margin-bottom: 1rem;
}
.card--warn {
    border-color: #fcd34d;
    background: linear-gradient(135deg, #fffbeb 0%, #fff 40%);
}
.card__title {
    margin: 0 0 0.65rem;
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
}
.card__hint {
    margin: 0 0 0.85rem;
    font-size: 0.82rem;
    color: #64748b;
}
.card__more {
    margin: 1rem 0 0;
    padding-top: 0.85rem;
    border-top: 1px dashed #cbd5e1;
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
}
.tbl-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 0 -0.15rem;
    padding: 0 0.15rem;
}
.tbl-wrap .tbl {
    min-width: 52rem;
}
.tbl__proto {
    font-size: 0.76rem;
    line-height: 1.4;
    color: #475569;
    max-width: 14rem;
}
.card__warn {
    margin: 0 0 1rem;
    font-size: 0.82rem;
    color: #92400e;
    line-height: 1.55;
}
.tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}
.tbl th {
    padding: 0.5rem 0.65rem;
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
    border-bottom: 1px solid #e2e8f0;
}
.tbl td {
    padding: 0.55rem 0.65rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
    color: #334155;
}
.tbl__name {
    font-weight: 600;
    color: #0f172a;
    white-space: nowrap;
}
.tbl__path {
    font-size: 0.78rem;
    word-break: break-all;
}
.tbl__props {
    font-size: 0.78rem;
}
.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
}
.demo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
}
.demo-box {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.85rem;
    background: #fafafa;
}
.demo-box--wide {
    grid-column: 1 / -1;
}
.demo-box__t {
    margin: 0 0 0.65rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
}
.row-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem;
    align-items: flex-start;
}
.row-btns__grp {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    align-items: center;
}
.row-btns__lbl {
    font-size: 0.78rem;
    color: #64748b;
    margin-right: 0.35rem;
    width: 100%;
    flex-basis: 100%;
    font-weight: 600;
}
.row-btns__grp .row-btns__lbl {
    flex-basis: auto;
    width: auto;
    margin-right: 0.5rem;
}
.btn {
    font-size: 0.82rem;
    padding: 0.45rem 0.85rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #334155;
    cursor: pointer;
    font-weight: 600;
}
.btn:hover {
    background: #f8fafc;
}
.btn--pri {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
.btn--pri:hover {
    background: #1d4ed8;
}
.btn--sec {
    background: #f1f5f9;
    border-color: #e2e8f0;
}
.skin-shell {
    position: relative;
    min-height: 3.5rem;
    padding: 0.5rem;
    background: #fff;
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
}
</style>
