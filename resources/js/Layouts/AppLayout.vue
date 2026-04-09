<script setup>
import { ref } from 'vue';
import SkinSwitcher from '../Components/SkinSwitcher.vue';

const props = defineProps({
    title: {
        type: String,
        default: 'OpenClaw 智信',
    },
    showNav: {
        type: Boolean,
        default: true,
    },
});

const mobileMenuOpen = ref(false);

function toggleMobileMenu() {
    mobileMenuOpen.value = !mobileMenuOpen.value;
}
</script>

<template>
    <div class="app-layout">
        <!-- 导航栏 -->
        <header v-if="showNav" class="navbar">
            <div class="navbar__container">
                <!-- 品牌 Logo -->
                <div class="navbar__brand">
                    <a href="/" class="navbar__logo">
                        <span class="navbar__logo-icon">🦀</span>
                        <span class="navbar__logo-text">OpenClaw 智信</span>
                    </a>
                </div>

                <!-- 导航链接 -->
                <nav class="navbar__menu">
                    <a href="/" class="navbar__link">首页</a>
                    <a href="/articles" class="navbar__link">文章</a>
                    <a href="/projects" class="navbar__link">项目</a>
                    <a href="/vip" class="navbar__link">VIP</a>
                </nav>

                <!-- 右侧操作 -->
                <div class="navbar__actions">
                    <!-- 搜索 -->
                    <button class="navbar__action-btn" title="搜索">
                        <span>🔍</span>
                    </button>

                    <!-- 皮肤切换 -->
                    <SkinSwitcher variant="navbar" />

                    <!-- 登录/注册 -->
                    <div class="navbar__auth">
                        <a href="/login" class="btn btn--outline">登录</a>
                        <a href="/register" class="btn btn--primary">注册</a>
                    </div>

                    <!-- 移动端菜单按钮 -->
                    <button class="navbar__menu-btn" @click="toggleMobileMenu">
                        <span>☰</span>
                    </button>
                </div>
            </div>

            <!-- 移动端菜单 -->
            <transition name="slide-down">
                <div v-if="mobileMenuOpen" class="navbar__mobile">
                    <nav class="navbar__mobile-menu">
                        <a href="/" class="navbar__mobile-link">首页</a>
                        <a href="/articles" class="navbar__mobile-link">文章</a>
                        <a href="/projects" class="navbar__mobile-link">项目</a>
                        <a href="/vip" class="navbar__mobile-link">VIP</a>
                        <div class="navbar__mobile-divider"></div>
                        <a href="/login" class="navbar__mobile-link">登录</a>
                        <a href="/register" class="navbar__mobile-link btn--primary">注册</a>
                    </nav>
                </div>
            </transition>
        </header>

        <!-- 主内容区 -->
        <main class="main-content">
            <slot />
        </main>

        <!-- 页脚 -->
        <footer class="footer">
            <div class="footer__container">
                <div class="footer__content">
                    <div class="footer__section">
                        <h4 class="footer__title">OpenClaw 智信</h4>
                        <p class="footer__desc">AI 资讯 + 资源分享 + UGC 内容平台</p>
                    </div>
                    <div class="footer__section">
                        <h4 class="footer__title">快速链接</h4>
                        <ul class="footer__links">
                            <li><a href="/about">关于我们</a></li>
                            <li><a href="/contact">联系我们</a></li>
                            <li><a href="/privacy">隐私政策</a></li>
                            <li><a href="/terms">服务条款</a></li>
                        </ul>
                    </div>
                    <div class="footer__section">
                        <h4 class="footer__title">关注我们</h4>
                        <div class="footer__social">
                            <a href="#" class="social-link">微信</a>
                            <a href="#" class="social-link">微博</a>
                            <a href="#" class="social-link">GitHub</a>
                        </div>
                    </div>
                </div>
                <div class="footer__bottom">
                    <p>&copy; 2026 OpenClaw 智信。All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.app-layout {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* 导航栏 */
.navbar {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(148, 163, 184, 0.15);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.navbar__container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
}

.navbar__brand {
    flex-shrink: 0;
}

.navbar__logo {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: inherit;
}

.navbar__logo-icon {
    font-size: 28px;
}

.navbar__logo-text {
    font-size: 18px;
    font-weight: 700;
    background: var(--gradient-primary, linear-gradient(135deg, #6366f1, #ec4899));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.navbar__menu {
    display: none;
    align-items: center;
    gap: 8px;
}

@media (min-width: 768px) {
    .navbar__menu {
        display: flex;
    }
}

.navbar__link {
    padding: 8px 16px;
    text-decoration: none;
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.navbar__link:hover {
    color: var(--primary, #6366f1);
    background: rgba(99, 102, 241, 0.05);
}

.navbar__actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.navbar__action-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 8px;
    cursor: pointer;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.navbar__action-btn:hover {
    background: #fff;
    transform: scale(1.05);
}

.navbar__auth {
    display: none;
    align-items: center;
    gap: 8px;
}

@media (min-width: 768px) {
    .navbar__auth {
        display: flex;
    }
}

.navbar__menu-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    font-size: 24px;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.2s;
}

@media (min-width: 768px) {
    .navbar__menu-btn {
        display: none;
    }
}

.navbar__menu-btn:hover {
    background: rgba(148, 163, 184, 0.1);
}

/* 移动端菜单 */
.navbar__mobile {
    border-top: 1px solid rgba(148, 163, 184, 0.15);
    background: #fff;
}

.navbar__mobile-menu {
    padding: 16px 24px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.navbar__mobile-link {
    padding: 12px 16px;
    text-decoration: none;
    color: #64748b;
    font-size: 15px;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.navbar__mobile-link:hover {
    color: var(--primary, #6366f1);
    background: rgba(99, 102, 241, 0.05);
}

.navbar__mobile-link.btn--primary {
    background: var(--gradient-primary, linear-gradient(135deg, #6366f1, #ec4899));
    color: #fff;
    text-align: center;
    margin-top: 8px;
}

.navbar__mobile-divider {
    height: 1px;
    background: rgba(148, 163, 184, 0.15);
    margin: 8px 0;
}

/* 主内容区 */
.main-content {
    flex: 1;
    width: 100%;
    max-width: 1280px;
    margin: 0 auto;
    padding: 24px;
}

/* 页脚 */
.footer {
    background: var(--dark, #0f172a);
    color: #94a3b8;
    padding: 48px 24px 24px;
    margin-top: auto;
}

.footer__container {
    max-width: 1280px;
    margin: 0 auto;
}

.footer__content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 32px;
    margin-bottom: 32px;
}

.footer__title {
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 16px;
}

.footer__desc {
    font-size: 13px;
    line-height: 1.6;
    margin: 0;
}

.footer__links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.footer__links a {
    color: #94a3b8;
    text-decoration: none;
    font-size: 13px;
    transition: color 0.2s;
}

.footer__links a:hover {
    color: var(--primary-light, #818cf8);
}

.footer__social {
    display: flex;
    gap: 12px;
}

.social-link {
    padding: 6px 12px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    color: #94a3b8;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s;
}

.social-link:hover {
    background: var(--primary, #6366f1);
    color: #fff;
}

.footer__bottom {
    padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
    font-size: 13px;
}

/* 动画 */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.2s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
