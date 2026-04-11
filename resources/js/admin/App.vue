<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { ADMIN_MENU, isMenuItemActive } from './constants/menu';
import { setAdminUser } from './permissions';
// 注意：菜单权限过滤必须依赖响应式状态，否则登录后不会触发重算

const route = useRoute();
const router = useRouter();
const isLoginPage = computed(() => route.name === 'login');

const adminUser = ref(null);

const pageTitle = computed(() => {
    const t = route.meta?.title;
    return typeof t === 'string' && t !== '' ? t : '管理后台';
});

const activeMenuIndex = computed(() => {
    for (const block of ADMIN_MENU) {
        for (const item of block.items) {
            if (isMenuItemActive(route.path, item)) {
                return item.to;
            }
        }
    }
    return '/';
});

const breadcrumbItems = computed(() => {
    for (const block of ADMIN_MENU) {
        for (const item of block.items) {
            if (isMenuItemActive(route.path, item)) {
                return [block.section, item.label];
            }
        }
    }
    return ['管理后台', pageTitle.value];
});

const activeMenuSection = computed(() => {
    for (const block of filteredMenu.value) {
        for (const item of block.items) {
            if (isMenuItemActive(route.path, item)) {
                return block.section;
            }
        }
    }
    return filteredMenu.value[0]?.section ?? '总览';
});

const filteredMenu = computed(() => {
    const perms = Array.isArray(adminUser.value?.admin_permissions) ? adminUser.value.admin_permissions : [];
    const hasPerm = (permKey) => {
        if (!permKey) return true;
        return perms.includes('*') || perms.includes(permKey);
    };

    return ADMIN_MENU.map((block) => {
        const items = (block.items || []).filter((it) => !it.perm || hasPerm(it.perm));
        return { ...block, items };
    }).filter((b) => (b.items || []).length > 0);
});

const adminInitial = computed(() => {
    const u = adminUser.value;
    const raw = (u?.name || u?.email || '').trim();
    if (!raw) return '管';
    return raw.slice(0, 1).toUpperCase();
});

const adminRoleText = computed(() => {
    const u = adminUser.value;
    if (!u) return '';
    if (u.admin_permissions?.includes('*')) return '超级管理员';
    return u.role === 'admin' ? '管理员' : u.role || '管理员';
});

function onMenuSelect(key) {
    // key is an absolute path like "/articles" or "/"
    if (!key) return;
    router.push({ path: key });
}

async function refreshMe() {
    if (isLoginPage.value) {
        adminUser.value = null;
        setAdminUser(null);
        return;
    }
    try {
        const { data } = await axios.get('/api/admin/me');
        adminUser.value = data.user ?? null;
        setAdminUser(adminUser.value);
    } catch {
        adminUser.value = null;
        setAdminUser(null);
    }
}

watch(isLoginPage, () => refreshMe(), { immediate: true });

function itemClass(item) {
    return { 'is-active': isMenuItemActive(route.path, item) };
}

async function logout() {
    try {
        // 确保带上 CSRF Cookie（与 Login.vue 保持一致）
        await axios.get('/sanctum/csrf-cookie');
        await axios.post('/api/admin/logout');
    } catch {
        /* ignore */
    }
    adminUser.value = null;
    setAdminUser(null);
    router.replace({ name: 'login', query: { force: '1' } });
}
</script>

<template>
    <template v-if="isLoginPage">
        <router-view />
    </template>
    <el-container v-else class="admin-shell">
        <el-aside class="admin-aside" width="248px">
            <div class="admin-brand">
                <span class="admin-brand__logo">◆</span>
                <div class="admin-brand__meta">
                    <strong>智信后台</strong>
                    <span>OpenClaw Admin</span>
                </div>
            </div>
            <el-scrollbar class="admin-menu-scroll">
                <el-menu
                    class="admin-el-menu"
                    :default-active="activeMenuIndex"
                    :default-openeds="[activeMenuSection]"
                    unique-opened
                    background-color="#001529"
                    text-color="#b4c0cc"
                    active-text-color="#ffffff"
                    @select="onMenuSelect"
                >
                    <el-sub-menu
                        v-for="block in filteredMenu"
                        :key="block.section"
                        :index="block.section"
                    >
                        <template #title>
                            <span class="menu-block__title-text">{{ block.section }}</span>
                        </template>
                        <el-menu-item
                            v-for="item in block.items"
                            :key="item.to + item.label"
                            :index="item.to"
                        >
                            <span class="menu-item__icon">{{ item.icon }}</span>
                            <span class="menu-item__text">{{ item.label }}</span>
                        </el-menu-item>
                    </el-sub-menu>
                </el-menu>
            </el-scrollbar>
        </el-aside>
        <el-container>
            <el-header class="admin-header">
                <div>
                    <h1 class="admin-header__title">{{ pageTitle }}</h1>
                    <el-breadcrumb class="admin-header__crumb" separator="/">
                        <el-breadcrumb-item v-for="(t, idx) in breadcrumbItems" :key="t + idx">
                            {{ t }}
                        </el-breadcrumb-item>
                    </el-breadcrumb>
                </div>
                <div class="admin-header__right">
                    <template v-if="adminUser">
                        <el-avatar size="small" class="admin-header__avatar">
                            {{ adminInitial }}
                        </el-avatar>
                        <div class="admin-header__user">
                            <div class="admin-header__user-name">
                                {{ adminUser.name || adminUser.email }}
                            </div>
                            <div class="admin-header__user-role">
                                {{ adminRoleText }}
                            </div>
                        </div>
                    </template>
                    <el-button type="danger" size="small" @click="logout">退出登录</el-button>
                </div>
            </el-header>
            <el-main class="admin-main" data-admin-scroll-root>
                <div class="admin-main__inner">
                    <router-view />
                </div>
            </el-main>
        </el-container>
    </el-container>
</template>

<style scoped>
.admin-shell {
    height: 100vh !important;
    height: 100dvh !important;
    overflow: hidden;
    background: #f5f7fa;
}

.admin-aside {
    height: 100vh;
    height: 100dvh;
    display: flex;
    flex-direction: column;
    background: #001529;
    color: #e5eaf3;
}

.admin-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}

.admin-brand__logo {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: #1677ff;
    font-size: 13px;
    font-weight: 700;
}

.admin-brand__meta {
    display: flex;
    flex-direction: column;
    line-height: 1.1;
}

.admin-brand__meta strong {
    font-size: 14px;
}

.admin-brand__meta span {
    font-size: 12px;
    color: #9da9b8;
}

.admin-menu-scroll {
    flex: 1;
    padding: 10px 8px 14px;
}

.admin-el-menu {
    border-right: none;
    background-color: #001529;
    min-height: 100%;
}

.menu-block__title-text {
    font-size: 12px;
    color: #8ea1b5;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.menu-item__icon {
    width: 22px;
    display: inline-flex;
    justify-content: center;
    font-size: 14px;
}

.menu-item__text {
    font-size: 14px;
}

.admin-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    height: 64px;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
}

.admin-header__title {
    margin: 0;
    font-size: 18px;
    color: #111827;
}

.admin-header__crumb {
    margin-top: 6px;
}

.admin-header__right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.admin-header__avatar {
    background: #1677ff;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
}

.admin-header__user {
    display: flex;
    flex-direction: column;
    line-height: 1.1;
    margin-right: 4px;
}

.admin-header__user-name {
    font-size: 12px;
    color: #111827;
}

.admin-header__user-role {
    font-size: 11px;
    color: #6b7280;
}

.admin-main {
    padding: 20px 18px 24px;
    overflow-y: auto;
    background: #f5f7fa;
}

.admin-main__inner {
    max-width: 1400px;
    margin: 0 auto;
}
</style>

<!-- 后台弹窗内错误提示（与各页 .bad / .err 配色一致） -->
<style>
.admin-modal-err {
    margin: 0 0 0.75rem;
    padding: 0.5rem 0.65rem;
    font-size: 0.85rem;
    line-height: 1.45;
    color: #b91c1c;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
}

/**
 * 全局统一后台内容视觉（不改各业务页逻辑/结构）
 * 由于各页使用了 scoped 的通用 class（card/table/btn/modal...），这里用更高优先级覆盖关键视觉。
 */
.admin-main .card {
    border: 1px solid #e5e7eb !important;
    border-radius: 12px !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
}

.admin-main .table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
}

.admin-main .table th {
    background: #f8fafc !important;
    color: #334155 !important;
    font-weight: 700 !important;
}

.admin-main .table th,
.admin-main .table td {
    border-bottom: 1px solid #e5e7eb !important;
}

.admin-main .table tbody tr:hover td {
    background: #f6f9ff !important;
}

.admin-main .btn {
    border: 1px solid #d1d5db !important;
    background: #fff !important;
    color: #0f172a !important;
    border-radius: 10px !important;
    padding: 0.5rem 1rem !important;
    transition:
        background 0.15s ease,
        border-color 0.15s ease,
        color 0.15s ease !important;
}

.admin-main .btn:disabled {
    opacity: 0.55 !important;
    cursor: not-allowed !important;
}

.admin-main .btn.primary {
    background: #1677ff !important;
    border-color: #1677ff !important;
    color: #fff !important;
}

/* 兼容各业务页沿用的 btn--pri 命名（避免白底白字导致“按钮看不见”） */
.admin-main .btn--pri {
    background: #1677ff !important;
    border-color: #1677ff !important;
    color: #fff !important;
}

.admin-main .link {
    color: #1677ff !important;
}

.admin-main .link.danger {
    color: #f5222d !important;
}

.admin-main .pill {
    border-radius: 999px !important;
    font-weight: 800 !important;
    letter-spacing: 0.01em !important;
}

.admin-main .modal {
    background: rgba(0, 0, 0, 0.4) !important;
    backdrop-filter: blur(4px) !important;
}

.admin-main .modal__box {
    border-radius: 14px !important;
    border: 1px solid #e5e7eb !important;
    box-shadow: 0 18px 55px rgba(15, 23, 42, 0.22) !important;
}

.admin-main .modal__head {
    margin-bottom: 0.95rem !important;
}

.admin-main .err {
    color: #f5222d !important;
}

.admin-main .ok {
    color: #13c46b !important;
}

.admin-main .chart-card {
    border: 1px solid #e5e7eb !important;
    border-radius: 12px !important;
}

.admin-main .chart-select select {
    border-radius: 10px !important;
    border: 1px solid #d1d5db !important;
}
</style>
