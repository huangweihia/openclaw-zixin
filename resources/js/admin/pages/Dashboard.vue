<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { enumLabel } from '../constants/labels';

const err = ref('');
const data = ref(null);
const loading = ref(true);

const shortcuts = [
    { to: '/moderation', label: '投稿审核', desc: '待处理队列', icon: '📋' },
    { to: '/orders', label: '订单', desc: '待支付 / 履约', icon: '💳' },
    { to: { path: '/articles', query: { published: '0' } }, label: '草稿文章', desc: '未发布内容', icon: '📝' },
    { to: '/comments', label: '评论', desc: '隐藏 / 删除', icon: '💬' },
    { to: '/users', label: '用户', desc: '角色与封禁', icon: '👤' },
    { to: '/subscriptions', label: '订阅', desc: 'VIP 记录', icon: '👑' },
    { to: '/view-histories', label: '浏览历史', desc: '全站审计', icon: '👁️' },
    { to: '/svip-custom-subscriptions', label: 'SVIP 定制', desc: '订阅表只读', icon: '💠' },
    { to: '/email-templates', label: '邮件模板', desc: '注册/通知', icon: '✉️' },
    { to: '/announcements', label: '公告', desc: '系统通知', icon: '📣' },
    { to: '/skin-configs', label: '皮肤主题', desc: '主题色与变量', icon: '🎨' },
    { to: '/ad-slots', label: '广告位', desc: '版位管理', icon: '📢' },
    { to: '/projects', label: '项目库', desc: 'GitHub 卡片', icon: '📦' },
    { to: '/categories', label: '分类', desc: '内容 taxonomy', icon: '🗂️' },
    { to: '/shared-components', label: '公共组件', desc: 'Vue 组件索引与演示', icon: '🧩' },
    { to: '/settings', label: '系统与站点', desc: '策略与文案', icon: '⚙️' },
];

async function loadDashboard() {
    loading.value = true;
    err.value = '';
    try {
        const { data: d } = await axios.get('/api/admin/dashboard/stats');
        data.value = d;
    } catch (e) {
        const status = e.response?.status;
        const msg =
            e.response?.data?.message ||
            (typeof e.response?.data === 'string' ? e.response.data : '') ||
            e.message ||
            '';
        err.value = status
            ? `无法连接仪表盘接口（HTTP ${status}）${msg ? `：${msg}` : ''}`
            : `无法加载仪表盘：${msg || '网络或服务异常'}`;
        data.value = null;
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadDashboard();
});

const s = computed(() => data.value?.summary ?? null);
const degraded = computed(() => Boolean(data.value?.degraded));
const loadWarn = computed(() => (typeof data.value?.load_error === 'string' ? data.value.load_error : ''));
const todo = computed(() => data.value?.todo ?? null);
const runtime = computed(() => data.value?.runtime ?? null);
const recentPosts = computed(() => data.value?.recent_pending_posts ?? []);
const recentOrders = computed(() => data.value?.recent_pending_orders ?? []);

function roleLabel(role) {
    return enumLabel('userRole', role);
}
</script>

<template>
    <div class="dash">
        <header class="dash__hero">
            <div>
                <h1 class="dash__title">仪表盘</h1>
                <p class="dash__sub">OpenClaw 智信 · 管理端总览（对齐功能清单与 Docker 本地栈）</p>
            </div>
            <div v-if="loading" class="dash__env" aria-hidden="true">
                <div class="skel skel--pill" />
                <div class="skel skel--pill skel--pill-wide" />
            </div>
            <div v-else-if="runtime" class="dash__env">
                <span class="pill">{{ runtime.app_env }}</span>
                <span class="pill pill--muted">{{ runtime.php_version }}</span>
            </div>
        </header>

        <template v-if="loading">
            <section class="dash__todo" aria-busy="true" aria-label="加载中">
                <div class="skel skel--section-label" />
                <div class="todo-grid">
                    <div v-for="n in 4" :key="n" class="todo-card skel-block" />
                </div>
            </section>
            <section class="dash__kpi" aria-label="加载中">
                <div class="skel skel--section-label" />
                <div class="kpi-grid">
                    <div v-for="n in 6" :key="n" class="kpi skel-block" />
                </div>
            </section>
            <div class="dash__split">
                <div class="panel skel-panel" v-for="n in 2" :key="n">
                    <div class="skel skel--panel-head" />
                    <div class="skel skel--line" />
                    <div class="skel skel--line skel--short" />
                </div>
            </div>
            <p class="loading-hint">正在加载仪表盘数据…</p>
        </template>

        <template v-else>
        <p v-if="err" class="err">{{ err }}</p>
        <p v-if="err" class="retry-row">
            <button type="button" class="btn-retry" @click="loadDashboard">重试</button>
        </p>

        <p v-if="!err && degraded && loadWarn" class="warn-banner" role="status">{{ loadWarn }}</p>

        <template v-if="s && !err">
            <section class="dash__todo" aria-label="待办">
                <h2 class="section-title">今日待办</h2>
                <div class="todo-grid">
                    <router-link to="/moderation" class="todo-card todo-card--warn">
                        <span class="todo-card__n">{{ todo?.moderation ?? 0 }}</span>
                        <span class="todo-card__t">待审投稿</span>
                    </router-link>
                    <router-link to="/orders" class="todo-card todo-card--warn">
                        <span class="todo-card__n">{{ todo?.orders ?? 0 }}</span>
                        <span class="todo-card__t">待处理订单</span>
                    </router-link>
                    <router-link to="/articles" class="todo-card">
                        <span class="todo-card__n">{{ todo?.draft_articles ?? 0 }}</span>
                        <span class="todo-card__t">未发布文章</span>
                    </router-link>
                    <router-link to="/comments" class="todo-card">
                        <span class="todo-card__n">{{ todo?.hidden_comments ?? 0 }}</span>
                        <span class="todo-card__t">已隐藏评论</span>
                    </router-link>
                </div>
            </section>

            <section class="dash__kpi" aria-label="核心指标">
                <h2 class="section-title">核心指标</h2>
                <div class="kpi-grid">
                    <div class="kpi">
                        <div class="kpi__v">{{ s.users_total }}</div>
                        <div class="kpi__l">用户总数</div>
                        <div class="kpi__hint">今日 +{{ s.users_new_today }} · 禁用 {{ s.users_banned }}</div>
                    </div>
                    <div class="kpi">
                        <div class="kpi__v">{{ s.articles_published }}</div>
                        <div class="kpi__l">已发布文章</div>
                        <div class="kpi__hint">共 {{ s.articles_total }} · 草稿 {{ s.articles_draft }}</div>
                    </div>
                    <div class="kpi">
                        <div class="kpi__v">{{ s.projects_total }}</div>
                        <div class="kpi__l">项目</div>
                        <div class="kpi__hint">推荐位 {{ s.projects_featured }}</div>
                    </div>
                    <div class="kpi">
                        <div class="kpi__v">{{ s.comments_total }}</div>
                        <div class="kpi__l">评论</div>
                        <div class="kpi__hint">隐藏 {{ s.comments_hidden }}</div>
                    </div>
                    <div class="kpi">
                        <div class="kpi__v">{{ s.orders_paid_revenue?.toFixed?.(2) ?? s.orders_paid_revenue }}</div>
                        <div class="kpi__l">已支付订单金额</div>
                        <div class="kpi__hint">笔数 {{ s.orders_paid_count }}</div>
                    </div>
                    <div class="kpi">
                        <div class="kpi__v">{{ s.subscriptions_active }}</div>
                        <div class="kpi__l">有效订阅</div>
                        <div class="kpi__hint">会员体系</div>
                    </div>
                </div>
            </section>

            <div class="dash__split">
                <section class="panel">
                    <div class="panel__head">
                        <h2 class="panel__title">最新待审投稿</h2>
                        <router-link to="/moderation" class="panel__link">进入审核 →</router-link>
                    </div>
                    <ul v-if="recentPosts.length" class="feed">
                        <li v-for="p in recentPosts" :key="p.id" class="feed__item">
                            <div>
                                <span class="feed__title">{{ p.title }}</span>
                                <span class="feed__meta">
                                    {{ enumLabel('userPostType', p.type) }} · {{ p.author?.name || p.author?.email || '—' }}
                                </span>
                            </div>
                            <router-link to="/moderation" class="feed__act">处理</router-link>
                        </li>
                    </ul>
                    <p v-else class="empty">暂无待审</p>
                </section>

                <section class="panel">
                    <div class="panel__head">
                        <h2 class="panel__title">待处理订单</h2>
                        <router-link to="/orders" class="panel__link">订单管理 →</router-link>
                    </div>
                    <ul v-if="recentOrders.length" class="feed">
                        <li v-for="o in recentOrders" :key="o.id" class="feed__item">
                            <div>
                                <span class="feed__title mono">{{ o.order_no }}</span>
                                <span class="feed__meta">{{ o.amount }} · {{ o.user?.name || '—' }}</span>
                            </div>
                            <router-link to="/orders" class="feed__act">查看</router-link>
                        </li>
                    </ul>
                    <p v-else class="empty">暂无待处理订单</p>
                </section>
            </div>

            <section class="dash__roles" v-if="s.users_by_role && Object.keys(s.users_by_role).length">
                <h2 class="section-title">用户角色分布</h2>
                <div class="role-bars">
                    <div v-for="(n, role) in s.users_by_role" :key="role" class="role-bar">
                        <span class="role-bar__l">{{ roleLabel(role) }}</span>
                        <div class="role-bar__track">
                            <div
                                class="role-bar__fill"
                                :style="{
                                    width: `${Math.min(100, (n / (s.users_total || 1)) * 100)}%`,
                                }"
                            />
                        </div>
                        <span class="role-bar__n">{{ n }}</span>
                    </div>
                </div>
            </section>

            <section class="dash__shortcuts">
                <h2 class="section-title">快捷入口</h2>
                <div class="short-grid">
                    <router-link v-for="x in shortcuts" :key="x.label" :to="x.to" class="short-card">
                        <span class="short-card__icon">{{ x.icon }}</span>
                        <span class="short-card__label">{{ x.label }}</span>
                        <span class="short-card__desc">{{ x.desc }}</span>
                    </router-link>
                </div>
            </section>

            <section v-if="runtime" class="dash__docker panel panel--flat">
                <h2 class="panel__title">运行环境 · Docker（compose）</h2>
                <p class="docker-line"><strong>Laravel</strong> {{ runtime.laravel_version }} · <strong>应用</strong> {{ runtime.app_name }}</p>
                <p class="docker-hint">{{ runtime.docker_compose_hint }}</p>
            </section>
        </template>
        </template>
    </div>
</template>

<style scoped>
.dash {
    max-width: 1120px;
}
.dash__hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.75rem;
}
.dash__title {
    margin: 0 0 0.35rem;
    font-size: 1.65rem;
    font-weight: 700;
    letter-spacing: -0.02em;
}
.dash__sub {
    margin: 0;
    color: #64748b;
    font-size: 0.92rem;
    max-width: 36rem;
    line-height: 1.5;
}
.dash__env {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.pill {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
    border-radius: 999px;
    background: #1e293b;
    color: #f8fafc;
    font-weight: 600;
}
.pill--muted {
    background: #e2e8f0;
    color: #475569;
}
.loading-hint {
    margin: 0.5rem 0 0;
    font-size: 0.85rem;
    color: #94a3b8;
}
.skel {
    border-radius: 8px;
    background: linear-gradient(90deg, #f1f5f9 0%, #e2e8f0 50%, #f1f5f9 100%);
    background-size: 200% 100%;
    animation: skel-shimmer 1.1s ease-in-out infinite;
}
.skel--pill {
    height: 1.75rem;
    width: 5.5rem;
    border-radius: 999px;
}
.skel--pill-wide {
    width: 7.5rem;
}
.skel--section-label {
    height: 0.65rem;
    width: 5rem;
    margin-bottom: 0.85rem;
    border-radius: 4px;
}
.skel-block {
    min-height: 4.25rem;
    border: 1px solid #e2e8f0;
    box-shadow: none;
    background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 50%, #f8fafc 100%);
    background-size: 200% 100%;
    animation: skel-shimmer 1.1s ease-in-out infinite;
}
.skel-panel {
    min-height: 8rem;
}
.skel--panel-head {
    height: 1rem;
    width: 40%;
    margin-bottom: 1rem;
}
.skel--line {
    height: 0.75rem;
    width: 100%;
    margin-bottom: 0.5rem;
    border-radius: 4px;
}
.skel--short {
    width: 70%;
}
@keyframes skel-shimmer {
    0% {
        background-position: 100% 0;
    }
    100% {
        background-position: -100% 0;
    }
}
.retry-row {
    margin: 0.35rem 0 1rem;
}
.btn-retry {
    font-size: 0.85rem;
    padding: 0.45rem 1rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #334155;
    cursor: pointer;
    font-weight: 600;
}
.btn-retry:hover {
    border-color: #94a3b8;
    background: #f8fafc;
}
.err {
    color: #b91c1c;
}
.warn-banner {
    margin: 0 0 1rem;
    padding: 0.75rem 1rem;
    font-size: 0.88rem;
    line-height: 1.5;
    color: #92400e;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border: 1px solid #fcd34d;
    border-radius: 10px;
}
.section-title {
    margin: 0 0 0.85rem;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
}
.dash__todo {
    margin-bottom: 1.75rem;
}
.todo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 0.75rem;
}
.todo-card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 1rem 1.1rem;
    background: #fff;
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    border: 1px solid #e2e8f0;
    transition:
        border-color 0.15s,
        box-shadow 0.15s;
}
.todo-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
}
.todo-card--warn {
    border-color: #fed7aa;
    background: linear-gradient(135deg, #fffbeb 0%, #fff 100%);
}
.todo-card__n {
    font-size: 1.75rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
}
.todo-card__t {
    margin-top: 0.4rem;
    font-size: 0.85rem;
    color: #475569;
}
.dash__kpi {
    margin-bottom: 1.75rem;
}
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0.75rem;
}
.kpi {
    background: #fff;
    border-radius: 12px;
    padding: 1rem 1.1rem;
    border: 1px solid #e2e8f0;
}
.kpi__v {
    font-size: 1.45rem;
    font-weight: 800;
    color: #0f172a;
}
.kpi__l {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.25rem;
}
.kpi__hint {
    font-size: 0.72rem;
    color: #94a3b8;
    margin-top: 0.35rem;
}
.dash__split {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
}
.panel {
    background: #fff;
    border-radius: 12px;
    padding: 1.1rem 1.2rem;
    border: 1px solid #e2e8f0;
}
.panel--flat {
    margin-bottom: 0;
}
.panel__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.85rem;
}
.panel__title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
}
.panel__link {
    font-size: 0.85rem;
    color: #2563eb;
    text-decoration: none;
}
.feed {
    list-style: none;
    margin: 0;
    padding: 0;
}
.feed__item {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.65rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.88rem;
}
.feed__item:last-child {
    border-bottom: none;
}
.feed__title {
    display: block;
    font-weight: 600;
    color: #1e293b;
}
.feed__meta {
    display: block;
    font-size: 0.78rem;
    color: #64748b;
    margin-top: 0.2rem;
}
.feed__act {
    flex-shrink: 0;
    font-size: 0.82rem;
    color: #2563eb;
    text-decoration: none;
    align-self: center;
}
.empty {
    margin: 0;
    color: #94a3b8;
    font-size: 0.9rem;
}
.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.82rem;
}
.dash__roles {
    margin-bottom: 1.75rem;
}
.role-bars {
    background: #fff;
    border-radius: 12px;
    padding: 1rem 1.2rem;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}
.role-bar {
    display: grid;
    grid-template-columns: 5rem 1fr 2.5rem;
    align-items: center;
    gap: 0.65rem;
    font-size: 0.82rem;
}
.role-bar__l {
    color: #475569;
}
.role-bar__track {
    height: 8px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
}
.role-bar__fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #6366f1);
    border-radius: 999px;
    min-width: 4px;
}
.role-bar__n {
    text-align: right;
    color: #64748b;
    font-variant-numeric: tabular-nums;
}
.dash__shortcuts {
    margin-bottom: 1.75rem;
}
.short-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 0.65rem;
}
.short-card {
    display: flex;
    flex-direction: column;
    padding: 0.85rem 0.9rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    text-decoration: none;
    color: inherit;
    transition:
        border-color 0.15s,
        background 0.15s;
}
.short-card:hover {
    border-color: #93c5fd;
    background: #eff6ff;
}
.short-card__icon {
    font-size: 1.25rem;
    margin-bottom: 0.35rem;
}
.short-card__label {
    font-size: 0.88rem;
    font-weight: 600;
    color: #1e293b;
}
.short-card__desc {
    font-size: 0.72rem;
    color: #64748b;
    margin-top: 0.15rem;
}
.dash__docker {
    margin-top: 0.5rem;
}
.docker-line {
    margin: 0 0 0.5rem;
    font-size: 0.88rem;
    color: #334155;
}
.docker-hint {
    margin: 0;
    font-size: 0.8rem;
    color: #64748b;
    line-height: 1.55;
    padding: 0.75rem 0.9rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px dashed #cbd5e1;
}
</style>
