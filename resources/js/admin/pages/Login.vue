<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from '../../frontend/utils/toast';

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
        toast.success('登录成功，正在进入控制台…', 1200);
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
            <div class="login-card">
                <header class="login-card__head">
                    <div class="login-brand" aria-hidden="true">
                        <span class="login-brand__mark">◆</span>
                        <div class="login-brand__text">
                            <span class="login-brand__name">智信</span>
                            <span class="login-brand__tag">Admin</span>
                        </div>
                    </div>
                    <h1 class="login-card__title">管理后台登录</h1>
                    <p class="login-card__sub">OpenClaw 智信 · 控制台</p>
                </header>

                <form class="login-form" @submit.prevent="submit">
                    <label class="login-field">
                        <span class="login-field__label">邮箱</span>
                        <input
                            v-model="email"
                            class="login-field__input"
                            type="email"
                            required
                            autocomplete="username"
                            placeholder="name@company.com"
                        />
                    </label>
                    <label class="login-field">
                        <span class="login-field__label">密码</span>
                        <input
                            v-model="password"
                            class="login-field__input"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="请输入密码"
                        />
                    </label>

                    <div class="login-row">
                        <label class="login-remember">
                            <input v-model="remember" type="checkbox" class="login-remember__input" />
                            <span>记住我</span>
                        </label>
                    </div>

                    <div v-if="error" class="login-alert" role="alert">{{ error }}</div>

                    <button type="submit" class="login-submit" :disabled="loading">
                        <span v-if="loading" class="login-submit__spinner" aria-hidden="true" />
                        {{ loading ? '登录中…' : '登录' }}
                    </button>
                </form>
            </div>

            <p class="login-page__hint">仅限授权人员访问 · 请妥善保管账号</p>
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
    font-family:
        'Segoe UI',
        system-ui,
        -apple-system,
        'PingFang SC',
        'Microsoft YaHei',
        sans-serif;
    color: #0f172a;
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
    padding: 2rem 1.75rem 1.85rem;
    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.9);
    box-shadow:
        0 1px 2px rgba(15, 23, 42, 0.04),
        0 24px 48px -12px rgba(15, 23, 42, 0.12),
        0 0 0 1px rgba(15, 23, 42, 0.03);
}

.login-card__head {
    text-align: center;
    margin-bottom: 1.65rem;
}

.login-brand {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1.1rem;
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

.login-card__sub {
    margin: 0;
    font-size: 0.8125rem;
    color: #64748b;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.login-field {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.login-field__label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #475569;
}

.login-field__input {
    width: 100%;
    box-sizing: border-box;
    padding: 0.65rem 0.85rem;
    font-size: 0.9375rem;
    color: #0f172a;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    outline: none;
    transition:
        border-color 0.15s ease,
        box-shadow 0.15s ease;
}

.login-field__input::placeholder {
    color: #94a3b8;
}

.login-field__input:hover {
    border-color: #cbd5e1;
}

.login-field__input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

.login-field__input:-webkit-autofill,
.login-field__input:-webkit-autofill:hover,
.login-field__input:-webkit-autofill:focus {
    -webkit-text-fill-color: #0f172a;
    box-shadow: 0 0 0 1000px #fff inset;
    transition: background-color 9999s ease-out;
}

.login-row {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-top: -0.15rem;
}

.login-remember {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.8125rem;
    color: #475569;
    cursor: pointer;
    user-select: none;
}

.login-remember__input {
    width: 1rem;
    height: 1rem;
    accent-color: #4f46e5;
    border-radius: 4px;
    cursor: pointer;
}

.login-alert {
    padding: 0.55rem 0.75rem;
    font-size: 0.8125rem;
    line-height: 1.45;
    color: #991b1b;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
}

.login-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    margin-top: 0.25rem;
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #fff;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    background: linear-gradient(135deg, #4f46e5 0%, #2563eb 50%, #1d4ed8 100%);
    box-shadow:
        0 1px 2px rgba(15, 23, 42, 0.08),
        0 8px 20px -4px rgba(37, 99, 235, 0.45);
    transition:
        transform 0.12s ease,
        box-shadow 0.12s ease,
        opacity 0.12s ease;
}

.login-submit:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow:
        0 2px 4px rgba(15, 23, 42, 0.1),
        0 12px 28px -4px rgba(37, 99, 235, 0.5);
}

.login-submit:active:not(:disabled) {
    transform: translateY(0);
}

.login-submit:disabled {
    opacity: 0.72;
    cursor: not-allowed;
    transform: none;
}

.login-submit__spinner {
    width: 1rem;
    height: 1rem;
    border: 2px solid rgba(255, 255, 255, 0.35);
    border-top-color: #fff;
    border-radius: 50%;
    animation: login-spin 0.65s linear infinite;
}

@keyframes login-spin {
    to {
        transform: rotate(360deg);
    }
}

.login-page__hint {
    margin: 0;
    font-size: 0.75rem;
    color: #64748b;
    text-align: center;
    max-width: 22rem;
    line-height: 1.5;
}

@media (max-width: 380px) {
    .login-card {
        padding: 1.5rem 1.25rem 1.4rem;
    }

    .login-card__title {
        font-size: 1.25rem;
    }
}
</style>
