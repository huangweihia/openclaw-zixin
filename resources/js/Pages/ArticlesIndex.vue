<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppLayout from '../Layouts/AppLayout.vue';

const route = useRoute();
const router = useRouter();

const articles = ref([]);
const categories = ref([]);
const loading = ref(true);
const currentPage = ref(1);
const totalPages = ref(1);

// 筛选条件
const filters = ref({
    category: route.query.category || '',
    sort: route.query.sort || 'latest',
    q: route.query.q || '',
});

// 加载文章列表
async function loadArticles(page = 1) {
    loading.value = true;
    try {
        // TODO: 替换为真实 API
        // const { data } = await axios.get('/api/articles', { params: { ...filters.value, page } });
        
        // 模拟数据
        articles.value = [
            {
                id: 1,
                title: 'AI 驱动的未来：2026 年技术趋势预测',
                excerpt: '深入探讨人工智能在 2026 年的发展方向，包括大模型、多模态、Agent 等前沿技术...',
                cover_image: 'https://via.placeholder.com/800x450',
                category: { name: 'AI 技术', color: '#6366f1' },
                author: { name: '张三', avatar: 'https://via.placeholder.com/40' },
                views: 1234,
                likes: 89,
                published_at: '2026-04-05 10:00',
                slug: 'ai-trends-2026',
            },
            {
                id: 2,
                title: 'Vue 3 + Vite 最佳实践指南',
                excerpt: '从零开始构建现代化前端项目，掌握组件化开发、状态管理、性能优化等核心技能...',
                cover_image: 'https://via.placeholder.com/800x450',
                category: { name: '前端开发', color: '#10b981' },
                author: { name: '李四', avatar: 'https://via.placeholder.com/40' },
                views: 892,
                likes: 67,
                published_at: '2026-04-04 15:30',
                slug: 'vue3-vite-best-practices',
            },
            {
                id: 3,
                title: 'Laravel 11 新特性详解',
                excerpt: '全面解析 Laravel 11 的重大更新，包括新的目录结构、性能改进、新中间件等...',
                cover_image: 'https://via.placeholder.com/800x450',
                category: { name: '后端开发', color: '#f59e0b' },
                author: { name: '王五', avatar: 'https://via.placeholder.com/40' },
                views: 756,
                likes: 54,
                published_at: '2026-04-03 09:15',
                slug: 'laravel-11-features',
            },
            {
                id: 4,
                title: 'Docker 容器化部署实战',
                excerpt: '手把手教你使用 Docker 部署 Laravel + Vue 全栈应用，包含 CI/CD 自动化流程...',
                cover_image: 'https://via.placeholder.com/800x450',
                category: { name: 'DevOps', color: '#ec4899' },
                author: { name: '赵六', avatar: 'https://via.placeholder.com/40' },
                views: 634,
                likes: 42,
                published_at: '2026-04-02 14:20',
                slug: 'docker-deployment-guide',
            },
        ];
        
        categories.value = [
            { id: 1, name: '全部', slug: '', count: 443 },
            { id: 2, name: 'AI 技术', slug: 'ai', count: 128 },
            { id: 3, name: '前端开发', slug: 'frontend', count: 96 },
            { id: 4, name: '后端开发', slug: 'backend', count: 84 },
            { id: 5, name: 'DevOps', slug: 'devops', count: 52 },
            { id: 6, name: '产品思维', slug: 'product', count: 38 },
            { id: 7, name: '职场成长', slug: 'career', count: 45 },
        ];
    } catch (error) {
        console.error('加载文章失败:', error);
    } finally {
        loading.value = false;
    }
}

// 切换分类
function selectCategory(category) {
    filters.value.category = category.slug;
    updateQuery();
    loadArticles(1);
}

// 切换排序
function changeSort(sort) {
    filters.value.sort = sort;
    updateQuery();
    loadArticles(1);
}

// 更新 URL 参数
function updateQuery() {
    router.push({ query: filters.value });
}

// 切换页码
function changePage(page) {
    if (page < 1 || page > totalPages.value) return;
    currentPage.value = page;
    loadArticles(page);
}

onMounted(() => {
    loadArticles();
});
</script>

<template>
    <AppLayout title="文章列表">
        <!-- 页面头部 -->
        <section class="page-header">
            <h1 class="page-title">📚 文章库</h1>
            <p class="page-subtitle">探索 {{ articles.length }}+ 篇精选技术文章</p>
        </section>

        <!-- 筛选栏 -->
        <section class="filter-bar">
            <!-- 分类筛选 -->
            <div class="filter-section">
                <span class="filter-label">分类：</span>
                <div class="category-tags">
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        :class="['category-tag', filters.category === cat.slug ? 'is-active' : '']"
                        :style="filters.category === cat.slug ? { background: cat.color } : {}"
                        @click="selectCategory(cat)"
                    >
                        {{ cat.name }}
                        <span class="category-tag__count">{{ cat.count }}</span>
                    </button>
                </div>
            </div>

            <!-- 排序选项 -->
            <div class="filter-section">
                <span class="filter-label">排序：</span>
                <div class="sort-options">
                    <button
                        :class="['sort-btn', filters.sort === 'latest' ? 'is-active' : '']"
                        @click="changeSort('latest')"
                    >
                        最新
                    </button>
                    <button
                        :class="['sort-btn', filters.sort === 'popular' ? 'is-active' : '']"
                        @click="changeSort('popular')"
                    >
                        最热
                    </button>
                    <button
                        :class="['sort-btn', filters.sort === 'likes' ? 'is-active' : '']"
                        @click="changeSort('likes')"
                    >
                        最多点赞
                    </button>
                </div>
            </div>
        </section>

        <!-- 文章列表 -->
        <section class="articles-section">
            <div v-if="loading" class="loading-state">
                <div class="loading-spinner"></div>
                <p>加载中...</p>
            </div>

            <div v-else-if="articles.length === 0" class="empty-state">
                <span class="empty-icon">📭</span>
                <p>暂无文章</p>
            </div>

            <div v-else class="articles-list">
                <article
                    v-for="article in articles"
                    :key="article.id"
                    class="article-card"
                >
                    <a :href="`/articles/${article.slug}`" class="article-card__link">
                        <div class="article-card__cover">
                            <img :src="article.cover_image" :alt="article.title" />
                            <span class="article-card__category" :style="{ background: article.category.color }">
                                {{ article.category.name }}
                            </span>
                        </div>
                        <div class="article-card__content">
                            <h2 class="article-card__title">{{ article.title }}</h2>
                            <p class="article-card__excerpt">{{ article.excerpt }}</p>
                            <div class="article-card__meta">
                                <div class="article-card__author">
                                    <img :src="article.author.avatar" :alt="article.author.name" class="author-avatar" />
                                    <span class="author-name">{{ article.author.name }}</span>
                                </div>
                                <div class="article-card__stats">
                                    <span class="stat">👁 {{ article.views }}</span>
                                    <span class="stat">❤️ {{ article.likes }}</span>
                                    <span class="stat">📅 {{ article.published_at }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </article>
            </div>
        </section>

        <!-- 分页 -->
        <section v-if="totalPages > 1" class="pagination">
            <button
                :disabled="currentPage === 1"
                class="page-btn"
                @click="changePage(currentPage - 1)"
            >
                ← 上一页
            </button>
            <span class="page-info">第 {{ currentPage }} / {{ totalPages }} 页</span>
            <button
                :disabled="currentPage === totalPages"
                class="page-btn"
                @click="changePage(currentPage + 1)"
            >
                下一页 →
            </button>
        </section>
    </AppLayout>
</template>

<style scoped>
/* 页面头部 */
.page-header {
    text-align: center;
    padding: 48px 24px;
    background: linear-gradient(180deg, rgba(99, 102, 241, 0.05) 0%, transparent 100%);
    border-radius: 16px;
    margin-bottom: 32px;
}

.page-title {
    font-size: 36px;
    font-weight: 800;
    margin: 0 0 8px;
    background: var(--gradient-primary, linear-gradient(135deg, #6366f1, #ec4899));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-subtitle {
    font-size: 16px;
    color: #64748b;
    margin: 0;
}

/* 筛选栏 */
.filter-bar {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 32px;
    border: 1px solid rgba(148, 163, 184, 0.15);
}

.filter-section {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.filter-section:last-child {
    margin-bottom: 0;
}

.filter-label {
    font-size: 14px;
    font-weight: 600;
    color: #64748b;
    white-space: nowrap;
}

.category-tags {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.category-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: #f1f5f9;
    border: none;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.category-tag:hover {
    background: rgba(99, 102, 241, 0.1);
    color: var(--primary, #6366f1);
}

.category-tag.is-active {
    color: #fff;
}

.category-tag__count {
    font-size: 11px;
    opacity: 0.8;
}

.sort-options {
    display: flex;
    gap: 8px;
}

.sort-btn {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid rgba(148, 163, 184, 0.3);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.sort-btn:hover {
    border-color: var(--primary, #6366f1);
    color: var(--primary, #6366f1);
}

.sort-btn.is-active {
    background: var(--primary, #6366f1);
    border-color: var(--primary, #6366f1);
    color: #fff;
}

/* 文章列表 */
.articles-section {
    margin-bottom: 32px;
}

.articles-list {
    display: grid;
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
    transform: translateY(-2px);
}

.article-card__link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.article-card__cover {
    position: relative;
    height: 240px;
    overflow: hidden;
    background: #f1f5f9;
}

.article-card__cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.article-card:hover .article-card__cover img {
    transform: scale(1.05);
}

.article-card__category {
    position: absolute;
    top: 16px;
    left: 16px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.article-card__content {
    padding: 24px;
}

.article-card__title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 12px;
    line-height: 1.4;
}

.article-card__excerpt {
    font-size: 14px;
    color: #64748b;
    line-height: 1.7;
    margin: 0 0 20px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.article-card__meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid rgba(148, 163, 184, 0.1);
}

.article-card__author {
    display: flex;
    align-items: center;
    gap: 10px;
}

.author-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.author-name {
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
}

.article-card__stats {
    display: flex;
    gap: 16px;
}

.stat {
    font-size: 12px;
    color: #94a3b8;
}

/* 加载/空状态 */
.loading-state,
.empty-state {
    text-align: center;
    padding: 64px 24px;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(99, 102, 241, 0.1);
    border-top-color: var(--primary, #6366f1);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.empty-icon {
    font-size: 64px;
    display: block;
    margin-bottom: 16px;
}

.loading-state p,
.empty-state p {
    font-size: 16px;
    color: #64748b;
    margin: 0;
}

/* 分页 */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 16px;
    padding: 32px 24px;
}

.page-btn {
    padding: 10px 20px;
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.3);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.page-btn:hover:not(:disabled) {
    border-color: var(--primary, #6366f1);
    color: var(--primary, #6366f1);
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-info {
    font-size: 14px;
    color: #64748b;
}

/* 响应式 */
@media (max-width: 768px) {
    .page-title {
        font-size: 28px;
    }

    .filter-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .article-card__cover {
        height: 180px;
    }

    .article-card__title {
        font-size: 18px;
    }

    .article-card__meta {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }
}
</style>
