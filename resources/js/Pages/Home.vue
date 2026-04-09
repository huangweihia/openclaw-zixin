<script setup>
import { ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';

const featuredArticles = ref([
    {
        id: 1,
        title: 'AI 驱动的未来：2026 年技术趋势预测',
        excerpt: '深入探讨人工智能在 2026 年的发展方向，包括大模型、多模态、Agent 等前沿技术...',
        image: 'https://via.placeholder.com/400x240',
        category: 'AI 技术',
        views: 1234,
        date: '2026-04-05',
    },
    {
        id: 2,
        title: 'Vue 3 + Vite 最佳实践指南',
        excerpt: '从零开始构建现代化前端项目，掌握组件化开发、状态管理、性能优化等核心技能...',
        image: 'https://via.placeholder.com/400x240',
        category: '前端开发',
        views: 892,
        date: '2026-04-04',
    },
    {
        id: 3,
        title: 'Laravel 11 新特性详解',
        excerpt: '全面解析 Laravel 11 的重大更新，包括新的目录结构、性能改进、新中间件等...',
        image: 'https://via.placeholder.com/400x240',
        category: '后端开发',
        views: 756,
        date: '2026-04-03',
    },
    {
        id: 4,
        title: 'Docker 容器化部署实战',
        excerpt: '手把手教你使用 Docker 部署 Laravel + Vue 全栈应用，包含 CI/CD 自动化流程...',
        image: 'https://via.placeholder.com/400x240',
        category: 'DevOps',
        views: 634,
        date: '2026-04-02',
    },
]);

const categories = ref([
    { name: 'AI 技术', icon: '🤖', count: 128 },
    { name: '前端开发', icon: '🎨', count: 96 },
    { name: '后端开发', icon: '⚙️', count: 84 },
    { name: 'DevOps', icon: '🚀', count: 52 },
    { name: '产品思维', icon: '💡', count: 38 },
    { name: '职场成长', icon: '📈', count: 45 },
]);

const stats = ref([
    { label: '文章总数', value: '443', suffix: '篇' },
    { label: '项目资源', value: '89', suffix: '个' },
    { label: '注册用户', value: '1.2', suffix: '万' },
    { label: 'VIP 会员', value: '380', suffix: '人' },
]);
</script>

<template>
    <AppLayout title="首页">
        <!-- 英雄区域 -->
        <section class="hero">
            <div class="hero__content">
                <h1 class="hero__title">
                    <span class="hero__title-gradient">OpenClaw 智信</span>
                </h1>
                <p class="hero__subtitle">
                    AI 资讯 + 资源分享 + UGC 内容平台
                </p>
                <div class="hero__actions">
                    <a href="/register" class="btn btn--primary btn--lg">
                        立即注册
                    </a>
                    <a href="/articles" class="btn btn--outline btn--lg">
                        浏览文章
                    </a>
                </div>
            </div>
            
            <!-- 统计数据 -->
            <div class="hero__stats">
                <div v-for="stat in stats" :key="stat.label" class="stat-card">
                    <div class="stat-card__value">
                        {{ stat.value }}<span class="stat-card__suffix">{{ stat.suffix }}</span>
                    </div>
                    <div class="stat-card__label">{{ stat.label }}</div>
                </div>
            </div>
        </section>

        <!-- 分类导航 -->
        <section class="categories">
            <h2 class="section-title">探索分类</h2>
            <div class="categories__grid">
                <a 
                    v-for="category in categories" 
                    :key="category.name"
                    href="/articles?category={{ category.name }}"
                    class="category-card"
                >
                    <span class="category-card__icon">{{ category.icon }}</span>
                    <span class="category-card__name">{{ category.name }}</span>
                    <span class="category-card__count">{{ category.count }} 篇</span>
                </a>
            </div>
        </section>

        <!-- 精选文章 -->
        <section class="featured">
            <div class="section-header">
                <h2 class="section-title">精选文章</h2>
                <a href="/articles" class="section-more">查看全部 →</a>
            </div>
            
            <div class="articles__grid">
                <article 
                    v-for="article in featuredArticles" 
                    :key="article.id"
                    class="article-card"
                >
                    <a :href="`/articles/${article.id}`" class="article-card__link">
                        <div class="article-card__image">
                            <img :src="article.image" :alt="article.title" />
                        </div>
                        <div class="article-card__content">
                            <div class="article-card__meta">
                                <span class="article-card__category">{{ article.category }}</span>
                                <span class="article-card__date">{{ article.date }}</span>
                            </div>
                            <h3 class="article-card__title">{{ article.title }}</h3>
                            <p class="article-card__excerpt">{{ article.excerpt }}</p>
                            <div class="article-card__footer">
                                <span class="article-card__views">👁 {{ article.views }}</span>
                                <span class="article-card__read">阅读全文 →</span>
                            </div>
                        </div>
                    </a>
                </article>
            </div>
        </section>

        <!-- CTA 区域 -->
        <section class="cta">
            <div class="cta__content">
                <h2 class="cta__title">成为 VIP 会员</h2>
                <p class="cta__subtitle">
                    解锁全部内容，获取独家资源，加入精英社群
                </p>
                <div class="cta__features">
                    <span class="cta__feature">✓ 100% 内容访问</span>
                    <span class="cta__feature">✓ 独家资源包</span>
                    <span class="cta__feature">✓ 优先答疑</span>
                    <span class="cta__feature">✓ 社群交流</span>
                </div>
                <a href="/vip" class="btn btn--primary btn--lg">
                    了解详情
                </a>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
/* 英雄区域 */
.hero {
    padding: 64px 24px;
    text-align: center;
    background: linear-gradient(180deg, rgba(99, 102, 241, 0.05) 0%, transparent 100%);
    border-radius: 16px;
    margin-bottom: 48px;
}

.hero__title {
    font-size: 48px;
    font-weight: 800;
    margin: 0 0 16px;
    letter-spacing: -0.02em;
}

@media (max-width: 768px) {
    .hero__title {
        font-size: 32px;
    }
}

.hero__title-gradient {
    background: var(--gradient-primary, linear-gradient(135deg, #6366f1, #ec4899));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero__subtitle {
    font-size: 18px;
    color: #64748b;
    margin: 0 0 32px;
}

.hero__actions {
    display: flex;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
}

.hero__stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 24px;
    margin-top: 48px;
    padding-top: 32px;
    border-top: 1px solid rgba(148, 163, 184, 0.15);
}

.stat-card {
    text-align: center;
}

.stat-card__value {
    font-size: 36px;
    font-weight: 700;
    background: var(--gradient-primary, linear-gradient(135deg, #6366f1, #ec4899));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stat-card__suffix {
    font-size: 16px;
    -webkit-text-fill-color: initial;
    color: #64748b;
}

.stat-card__label {
    font-size: 13px;
    color: #94a3b8;
    margin-top: 4px;
}

/* 分类 */
.categories {
    margin-bottom: 48px;
}

.section-title {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 24px;
    color: #1e293b;
}

.categories__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 16px;
}

.category-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 24px 16px;
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.15);
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.category-card:hover {
    border-color: var(--primary, #6366f1);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
    transform: translateY(-2px);
}

.category-card__icon {
    font-size: 32px;
    margin-bottom: 8px;
}

.category-card__name {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
}

.category-card__count {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 4px;
}

/* 精选文章 */
.featured {
    margin-bottom: 48px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.section-more {
    color: var(--primary, #6366f1);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.articles__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
}

.article-card {
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.15);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s ease;
}

.article-card:hover {
    border-color: var(--primary, #6366f1);
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.1);
    transform: translateY(-4px);
}

.article-card__link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.article-card__image {
    width: 100%;
    height: 180px;
    overflow: hidden;
    background: #f1f5f9;
}

.article-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.article-card:hover .article-card__image img {
    transform: scale(1.05);
}

.article-card__content {
    padding: 16px;
}

.article-card__meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.article-card__category {
    font-size: 12px;
    font-weight: 600;
    color: var(--primary, #6366f1);
    background: rgba(99, 102, 241, 0.08);
    padding: 4px 8px;
    border-radius: 4px;
}

.article-card__date {
    font-size: 12px;
    color: #94a3b8;
}

.article-card__title {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 8px;
    line-height: 1.4;
}

.article-card__excerpt {
    font-size: 13px;
    color: #64748b;
    line-height: 1.6;
    margin: 0 0 12px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.article-card__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 12px;
    border-top: 1px solid rgba(148, 163, 184, 0.1);
}

.article-card__views {
    font-size: 12px;
    color: #94a3b8;
}

.article-card__read {
    font-size: 12px;
    color: var(--primary, #6366f1);
    font-weight: 500;
}

/* CTA 区域 */
.cta {
    padding: 64px 24px;
    background: var(--gradient-primary, linear-gradient(135deg, #6366f1, #ec4899));
    border-radius: 16px;
    text-align: center;
    color: #fff;
}

.cta__content {
    max-width: 600px;
    margin: 0 auto;
}

.cta__title {
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 12px;
}

.cta__subtitle {
    font-size: 16px;
    opacity: 0.9;
    margin: 0 0 24px;
}

.cta__features {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 32px;
}

.cta__feature {
    font-size: 14px;
    background: rgba(255, 255, 255, 0.15);
    padding: 8px 16px;
    border-radius: 20px;
}

/* 按钮 */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 24px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: none;
}

.btn--lg {
    padding: 14px 32px;
    font-size: 16px;
}

.btn--primary {
    background: var(--gradient-primary, linear-gradient(135deg, #6366f1, #ec4899));
    color: #fff;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
}

.btn--primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
}

.btn--outline {
    background: transparent;
    color: #64748b;
    border: 2px solid rgba(148, 163, 184, 0.3);
}

.btn--outline:hover {
    border-color: var(--primary, #6366f1);
    color: var(--primary, #6366f1);
}
</style>
