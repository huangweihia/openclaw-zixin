<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { ElMessage } from 'element-plus';

const route = useRoute();
const router = useRouter();

const email = ref('');
const password = ref('');
const remember = ref(false);
const error = ref('');
const loading = ref(false);

async function submit() {
    error.value = '';
    loading.value = true;
    try {
        await axios.get('/sanctum/csrf-cookie');
        await axios.post('/api/admin/login', {
            email: email.value,
            password: password.value,
            remember: remember.value,
        });
        ElMessage.success({ message: '登录成功，正在进入控制台…', duration: 1200 });
        const redir = typeof route.query.redirect === 'string' ? route.query.redirect : '/';
        setTimeout(() => {
            router.replace(redir || '/');
        }, 320);
    } catch (e) {
        const msg = e.response?.data?.errors?.email?.[0] || e.response?.data?.message;
        error.value = msg || '登录失败，请检查账号密码';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="login-page">
        <div class="login-page__bg" aria-hidden="true">
            <div class="login-page__orb login-page__orb--1" />
            <div class="login-page__orb login-page__orb--2" />
            <div class="login-page__grid" />
        </div>

        <div class="login-page__content">
            <el-card class="login-card" shadow="hover">
                <template #header>
                    <div class="login-card__head">
                        <div class="login-brand" aria-hidden="true">
                            <span class="login-brand__mark">◆</span>
                            <div class="login-brand__text">
                                <span class="login-brand__name">智信</span>
                                <span class="login-brand__tag">Admin</span>
                            </div>
                        </div>
                        <h1 class="login-card__title">管理后台登录</h1>
                        <el-text type="info" size="small">OpenClaw 智信 · 控制台</el-text>
                    </div>
                </template>

                <el-form class="login-form" label-position="top" @submit.prevent="submit">
                    <el-form-item label="邮箱">
                        <el-input
                            v-model="email"
                            type="email"
                            size="large"
                            clearable
                            placeholder="name@company.com"
                            autocomplete="username"
                        />
                    </el-form-item>
                    <el-form-item label="密码">
                        <el-input
                            v-model="password"
                            type="password"
                            size="large"
                            show-password
                            placeholder="请输入密码"
                            autocomplete="current-password"
                        />
                    </el-form-item>
                    <el-form-item>
                        <el-checkbox v-model="remember">记住我</el-checkbox>
                    </el-form-item>
                    <el-alert v-if="error" type="error" :title="error" :closable="false" show-icon class="login-alert" />
                    <el-button type="primary" size="large" class="login-submit" native-type="submit" :loading="loading" block>
                        {{ loading ? '登录中…' : '登录' }}
                    </el-button>
                </el-form>
            </el-card>

            <el-text size="small" type="info" class="login-page__hint">仅限授权人员访问 · 请妥善保管账号</el-text>
        </div>
    </div>
</template>

<style scoped>
.login-page {
    position: relative;
    min-height: 100vh;
    min-height: 100dvh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem 1rem 2rem;
    color: var(--el-text-color-primary);
    overflow-x: hidden;
}

.login-page__bg {
    position: absolute;
    inset: 0;
    z-index: 0;
    background: linear-gradient(160deg, #f1f5f9 0%, #e2e8f0 45%, #cbd5e1 100%);
}

.login-page__orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.55;
    pointer-events: none;
}

.login-page__orb--1 {
    width: min(72vw, 520px);
    height: min(72vw, 520px);
    top: -12%;
    right: -8%;
    background: radial-gradient(circle at 30% 30%, #3b82f6, #6366f1 55%, transparent 70%);
}

.login-page__orb--2 {
    width: min(60vw, 420px);
    height: min(60vw, 420px);
    bottom: -10%;
    left: -12%;
    background: radial-gradient(circle at 70% 70%, #06b6d4, #8b5cf6 50%, transparent 72%);
}

.login-page__grid {
    position: absolute;
    inset: 0;
    opacity: 0.35;
    background-image:
        linear-gradient(rgba(15, 23, 42, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(15, 23, 42, 0.04) 1px, transparent 1px);
    background-size: 48px 48px;
    mask-image: radial-gradient(ellipse 80% 70% at 50% 45%, #000 20%, transparent 100%);
}

.login-page__content {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 420px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.25rem;
}

.login-card {
    width: 100%;
    border-radius: 16px !important;
    --el-card-border-color: rgba(255, 255, 255, 0.85);
    background: rgba(255, 255, 255, 0.88) !important;
    backdrop-filter: blur(16px);
}

.login-card :deep(.el-card__header) {
    border-bottom: none;
    padding-bottom: 0;
}

.login-card :deep(.el-card__body) {
    padding-top: 8px;
}

.login-card__head {
    text-align: center;
}

.login-brand {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1rem;
    padding: 0.35rem 0.65rem 0.35rem 0.45rem;
    background: rgba(15, 23, 42, 0.06);
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, 0.06);
}

.login-brand__mark {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 10px;
    background: linear-gradient(135deg, #1e293b, #334155);
    color: #f8fafc;
    font-size: 0.85rem;
    line-height: 1;
}

.login-brand__text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.15;
    text-align: left;
}

.login-brand__name {
    font-weight: 700;
    font-size: 0.95rem;
    letter-spacing: 0.02em;
    color: #0f172a;
}

.login-brand__tag {
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #64748b;
}

.login-card__title {
    margin: 0 0 0.35rem;
    font-size: 1.375rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: #0f172a;
}

.login-form {
    margin-top: 0.25rem;
}

.login-alert {
    margin-bottom: 12px;
}

.login-submit {
    width: 100%;
    margin-top: 0.5rem;
    font-weight: 600;
}

.login-page__hint {
    text-align: center;
    max-width: 22rem;
    line-height: 1.5;
}
</style>
