<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '../Layouts/AppLayout.vue';
import SkinSwitcher from '../Components/SkinSwitcher.vue';

const route = useRoute();
const article = ref(null);
const loading = ref(true);
const isLiked = ref(false);
const isFavorited = ref(false);

// 模拟数据
onMounted(async () => {
    try {
        // TODO: 替换为真实 API
        // const { data } = await axios.get(`/api/articles/${route.params.slug}`);
        
        article.value = {
            id: 1,
            title: 'AI 驱动的未来：2026 年技术趋势预测',
            content: `
                <p>人工智能正在以前所未有的速度改变着我们的世界。站在 2026 年的门槛上，让我们一起展望 AI 技术的未来发展趋势。</p>
                
                <h2>一、大模型的演进方向</h2>
                <p>2023-2025 年，我们见证了大语言模型的爆发式增长。从 GPT-4 到 Claude，从 Gemini 到国内的文心一言、通义千问，模型能力不断提升。展望未来，大模型将朝着以下方向发展：</p>
                <ul>
                    <li><strong>多模态融合</strong>：文本、图像、音频、视频的深度融合理解</li>
                    <li><strong>长上下文</strong>：从 128K 向 1M+ token 演进，实现真正的"过目不忘"</li>
                    <li><strong>推理能力</strong>：从模式匹配向真正的逻辑推理跃迁</li>
                    <li><strong>专业化</strong>：垂直领域模型的精细化发展</li>
                </ul>

                <h2>二、Agent 智能体的崛起</h2>
                <p>2024 年被视为 AI Agent 元年。智能体不再局限于被动回答问题，而是能够主动规划、执行复杂任务。未来趋势包括：</p>
                <ul>
                    <li><strong>自主规划</strong>：分解复杂目标，自主制定执行计划</li>
                    <li><strong>工具使用</strong>：熟练操作各种软件和 API</li>
                    <li><strong>多 Agent 协作</strong>：多个智能体分工合作完成大型项目</li>
                    <li><strong>人机协同</strong>：人类设定目标，AI 负责执行</li>
                </ul>

                <h2>三、边缘 AI 与端侧部署</h2>
                <p>随着模型压缩和硬件进步，AI 将更多地在本地设备运行：</p>
                <ul>
                    <li><strong>隐私保护</strong>：敏感数据无需上传云端</li>
                    <li><strong>低延迟</strong>：实时响应，无需网络等待</li>
                    <li><strong>离线可用</strong>：无网络环境也能使用 AI 功能</li>
                </ul>

                <h2>四、AI 安全与治理</h2>
                <p>随着 AI 能力增强，安全和治理问题日益重要：</p>
                <ul>
                    <li><strong>对齐研究</strong>：确保 AI 目标与人类价值观一致</li>
                    <li><strong>可解释性</strong>：理解 AI 决策过程</li>
                    <li><strong>监管框架</strong>：建立全球协同的 AI 治理体系</li>
                </ul>

                <h2>结语</h2>
                <p>AI 的未来充满机遇与挑战。作为技术从业者，我们需要持续学习，把握趋势，在这个变革时代找到自己的位置。</p>
            `,
            cover_image: 'https://via.placeholder.com/1200x630',
            category: { name: 'AI 技术', color: '#6366f1' },
            author: { 
                name: '张三', 
                avatar: 'https://via.placeholder.com/80',
                bio: 'AI 研究者，专注大模型应用',
                articles_count: 42,
                followers_count: 1234,
            },
            views: 1234,
            likes: 89,
            favorites: 56,
            comments_count: 23,
            published_at: '2026-04-05 10:00',
            updated_at: '2026-04-05 12:30',
            tags: ['AI', '大模型', '技术趋势', '2026'],
            related_articles: [
                {
                    id: 2,
                    title: 'Vue 3 + Vite 最佳实践指南',
                    cover_image: 'https://via.placeholder.com/400x240',
                    views: 892,
                },
                {
                    id: 3,
                    title: 'Laravel 11 新特性详解',
                    cover_image: 'https://via.placeholder.com/400x240',
                    views: 756,
                },
            ],
        };
        
        // 检查用户是否已点赞/收藏
        // const { data } = await axios.get(`/api/articles/${article.value.id}/status`);
        // isLiked.value = data.liked;
        // isFavorited.value = data.favorited;
    } catch (error) {
        console.error('加载文章失败:', error);
    } finally {
        loading.value = false;
    }
});

// 点赞
async function toggleLike() {
    // TODO: API 调用
    isLiked.value = !isLiked.value;
    article.value.likes += isLiked.value ? 1 : -1;
}

// 收藏
async function toggleFavorite() {
    // TODO: API 调用
    isFavorited.value = !isFavorited.value;
    article.value.favorites += isFavorited.value ? 1 : -1;
}

// 分享
function share() {
    if (navigator.share) {
        navigator.share({
            title: article.value.title,
            url: window.location.href,
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('链接已复制到剪贴板');
    }
}
</script>

<template>
    <AppLayout :show-nav="false" title="文章详情">
        <!-- 简化导航 -->
        <header class="simple-nav">
            <a href="/" class="nav-logo">🦀 OpenClaw 智信</a>
            <div class="nav-actions">
                <a href="/" class="nav-link">首页</a>
                <a href="/articles" class="nav-link">文章</a>
                <SkinSwitcher variant="navbar" />
            </div>
        </header>

        <div v-if="loading" class="loading-state">
            <div class="loading-spinner"></div>
            <p>加载中...</p>
        </div>

        <article v-else-if="article" class="article-detail">
            <!-- 文章头部 -->
            <header class="article-header">
                <span class="article-category" :style="{ background: article.category.color }">
                    {{ article.category.name }}
                </span>
                <h1 class="article-title">{{ article.title }}</h1>
                <div class="article-meta">
                    <div class="author-info">
                        <img :src="article.author.avatar" :alt="article.author.name" class="author-avatar" />
                        <div class="author-details">
                            <span class="author-name">{{ article.author.name }}</span>
                            <span class="author-bio">{{ article.author.bio }}</span>
                        </div>
                    </div>
                    <div class="meta-stats">
                        <span class="stat">📅 {{ article.published_at }}</span>
                        <span class="stat">👁 {{ article.views }} 阅读</span>
                        <span class="stat">💬 {{ article.comments_count }} 评论</span>
                    </div>
                </div>
            </header>

            <!-- 封面图 -->
            <figure class="article-cover">
                <img :src="article.cover_image" :alt="article.title" />
            </figure>

            <!-- 文章内容 -->
            <div class="article-content" v-html="article.content"></div>

            <!-- 标签 -->
            <div class="article-tags">
                <span v-for="tag in article.tags" :key="tag" class="tag">
                    #{{ tag }}
                </span>
            </div>

            <!-- 互动栏 -->
            <div class="article-actions">
                <button 
                    :class="['action-btn', isLiked ? 'is-active' : '']"
                    @click="toggleLike"
                >
                    <span class="action-icon">{{ isLiked ? '❤️' : '🤍' }}</span>
                    <span class="action-text">{{ article.likes }}</span>
                </button>
                <button 
                    :class="['action-btn', isFavorited ? 'is-active' : '']"
                    @click="toggleFavorite"
                >
                    <span class="action-icon">{{ isFavorited ? '⭐' : '☆' }}</span>
                    <span class="action-text">{{ article.favorites }}</span>
                </button>
                <button class="action-btn" @click="share">
                    <span class="action-icon">📤</span>
                    <span class="action-text">分享</span>
                </button>
            </div>

            <!-- 作者信息卡片 -->
            <aside class="author-card">
                <img :src="article.author.avatar" :alt="article.author.name" class="author-card__avatar" />
                <div class="author-card__info">
                    <h3 class="author-card__name">{{ article.author.name }}</h3>
                    <p class="author-card__bio">{{ article.author.bio }}</p>
                    <div class="author-card__stats">
                        <span>{{ article.author.articles_count }} 篇文章</span>
                        <span>{{ article.author.followers_count }} 关注</span>
                    </div>
                </div>
                <button class="btn-follow">+ 关注</button>
            </aside>

            <!-- 相关文章 -->
            <section class="related-articles">
                <h2 class="section-title">相关文章</h2>
                <div class="related-grid">
                    <a
                        v-for="related in article.related_articles"
                        :key="related.id"
                        :href="`/articles/${related.id}`"
                        class="related-card"
                    >
                        <img :src="related.cover_image" :alt="related.title" />
                        <div class="related-card__info">
                            <h3 class="related-card__title">{{ related.title }}</h3>
                            <span class="related-card__views">👁 {{ related.views }}</span>
                        </div>
                    </a>
                </div>
            </section>
        </article>

        <div v-else class="empty-state">
            <span class="empty-icon">📭</span>
            <p>文章不存在</p>
            <a href="/articles" class="btn-back">返回文章列表</a>
        </div>
    </AppLayout>
</template>

<style scoped>
/* 简化导航 */
.simple-nav {
    position: sticky;
    top: 0;
    z-index: 100;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 24px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(148, 163, 184, 0.15);
}

.nav-logo {
    font-size: 18px;
    font-weight: 700;
    text-decoration: none;
    background: var(--gradient-primary, linear-gradient(135deg, #6366f1, #ec4899));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.nav-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.nav-link {
    text-decoration: none;
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
    transition: color 0.2s;
}

.nav-link:hover {
    color: var(--primary, #6366f1);
}

/* 加载状态 */
.loading-state {
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

/* 文章详情 */
.article-detail {
    max-width: 800px;
    margin: 0 auto;
    padding: 32px 24px;
}

/* 文章头部 */
.article-header {
    margin-bottom: 32px;
}

.article-category {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 16px;
}

.article-title {
    font-size: 32px;
    font-weight: 800;
    color: #1e293b;
    margin: 0 0 24px;
    line-height: 1.3;
}

.article-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.author-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.author-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.author-details {
    display: flex;
    flex-direction: column;
}

.author-name {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
}

.author-bio {
    font-size: 13px;
    color: #64748b;
}

.meta-stats {
    display: flex;
    gap: 16px;
}

.stat {
    font-size: 13px;
    color: #64748b;
}

/* 封面图 */
.article-cover {
    margin: 0 0 40px;
    border-radius: 16px;
    overflow: hidden;
}

.article-cover img {
    width: 100%;
    height: auto;
    display: block;
}

/* 文章内容 */
.article-content {
    font-size: 17px;
    line-height: 1.8;
    color: #334155;
}

.article-content :deep(h2) {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin: 40px 0 20px;
}

.article-content :deep(p) {
    margin: 0 0 20px;
}

.article-content :deep(ul) {
    margin: 0 0 20px;
    padding-left: 24px;
}

.article-content :deep(li) {
    margin: 0 0 10px;
}

.article-content :deep(strong) {
    font-weight: 600;
    color: #1e293b;
}

/* 标签 */
.article-tags {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin: 32px 0;
    padding: 20px 0;
    border-top: 1px solid rgba(148, 163, 184, 0.15);
    border-bottom: 1px solid rgba(148, 163, 184, 0.15);
}

.tag {
    padding: 6px 14px;
    background: rgba(99, 102, 241, 0.08);
    border-radius: 20px;
    font-size: 13px;
    color: var(--primary, #6366f1);
    font-weight: 500;
}

/* 互动栏 */
.article-actions {
    display: flex;
    gap: 12px;
    margin: 32px 0;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.3);
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.action-btn:hover {
    border-color: var(--primary, #6366f1);
    color: var(--primary, #6366f1);
}

.action-btn.is-active {
    background: rgba(236, 72, 153, 0.1);
    border-color: #ec4899;
    color: #ec4899;
}

.action-icon {
    font-size: 18px;
}

/* 作者卡片 */
.author-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 24px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(236, 72, 153, 0.05));
    border: 1px solid rgba(148, 163, 184, 0.15);
    border-radius: 16px;
    margin: 40px 0;
}

.author-card__avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
}

.author-card__info {
    flex: 1;
}

.author-card__name {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px;
}

.author-card__bio {
    font-size: 14px;
    color: #64748b;
    margin: 0 0 8px;
}

.author-card__stats {
    font-size: 13px;
    color: #94a3b8;
    display: flex;
    gap: 16px;
}

.btn-follow {
    padding: 10px 24px;
    background: var(--gradient-primary, linear-gradient(135deg, #6366f1, #ec4899));
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    cursor: pointer;
    transition: transform 0.2s;
}

.btn-follow:hover {
    transform: scale(1.05);
}

/* 相关文章 */
.related-articles {
    margin-top: 48px;
    padding-top: 40px;
    border-top: 1px solid rgba(148, 163, 184, 0.15);
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 24px;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.related-card {
    display: flex;
    gap: 16px;
    text-decoration: none;
    color: inherit;
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.15);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s ease;
}

.related-card:hover {
    border-color: var(--primary, #6366f1);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
}

.related-card img {
    width: 120px;
    height: 80px;
    object-fit: cover;
}

.related-card__info {
    flex: 1;
    padding: 12px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.related-card__title {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.related-card__views {
    font-size: 12px;
    color: #94a3b8;
}

/* 空状态 */
.empty-state {
    text-align: center;
    padding: 64px 24px;
}

.empty-icon {
    font-size: 64px;
    display: block;
    margin-bottom: 16px;
}

.empty-state p {
    font-size: 16px;
    color: #64748b;
    margin: 0 0 24px;
}

.btn-back {
    display: inline-block;
    padding: 12px 24px;
    background: var(--primary, #6366f1);
    color: #fff;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    transition: opacity 0.2s;
}

.btn-back:hover {
    opacity: 0.9;
}

/* 响应式 */
@media (max-width: 768px) {
    .article-title {
        font-size: 24px;
    }

    .article-content {
        font-size: 16px;
    }

    .article-meta {
        flex-direction: column;
        align-items: flex-start;
    }

    .author-card {
        flex-direction: column;
        text-align: center;
    }

    .article-actions {
        flex-wrap: wrap;
    }
}
</style>
