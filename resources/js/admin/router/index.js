import { nextTick } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';
import Login from '../pages/Login.vue';
import Dashboard from '../pages/Dashboard.vue';
import Moderation from '../pages/Moderation.vue';
import Users from '../pages/Users.vue';
import UserEdit from '../pages/UserEdit.vue';
import PointsLedgerIndex from '../pages/PointsLedgerIndex.vue';
import Orders from '../pages/Orders.vue';
import ArticlesIndex from '../pages/ArticlesIndex.vue';
import ArticleEditor from '../pages/ArticleEditor.vue';
import ProjectsIndex from '../pages/ProjectsIndex.vue';
import ProjectEditor from '../pages/ProjectEditor.vue';
import CategoriesIndex from '../pages/CategoriesIndex.vue';
import CommentsIndex from '../pages/CommentsIndex.vue';
import Settings from '../pages/Settings.vue';
import EmailTemplates from '../pages/EmailTemplates.vue';
import EmailLogs from '../pages/EmailLogs.vue';
import EmailSubscriptionsIndex from '../pages/EmailSubscriptionsIndex.vue';
import AnnouncementsIndex from '../pages/AnnouncementsIndex.vue';
import SiteTestimonialsIndex from '../pages/SiteTestimonialsIndex.vue';
import AdSlots from '../pages/AdSlots.vue';
import SubscriptionsIndex from '../pages/SubscriptionsIndex.vue';
import ViewHistoriesIndex from '../pages/ViewHistoriesIndex.vue';
import SvipCustomSubscriptionsIndex from '../pages/SvipCustomSubscriptionsIndex.vue';
import PremiumResourcesIndex from '../pages/PremiumResourcesIndex.vue';
import SideHustleCasesIndex from '../pages/SideHustleCasesIndex.vue';
import PrivateTrafficSopsIndex from '../pages/PrivateTrafficSopsIndex.vue';
import AiToolMonetizationIndex from '../pages/AiToolMonetizationIndex.vue';
import SystemNotificationsIndex from '../pages/SystemNotificationsIndex.vue';
import SkinConfigsIndex from '../pages/SkinConfigsIndex.vue';
import EmailSettingsIndex from '../pages/EmailSettingsIndex.vue';
import PushNotificationsIndex from '../pages/PushNotificationsIndex.vue';
import RefundRequestsIndex from '../pages/RefundRequestsIndex.vue';
import InvoiceRequestsIndex from '../pages/InvoiceRequestsIndex.vue';
import CommentReportsIndex from '../pages/CommentReportsIndex.vue';
import AuditLogsIndex from '../pages/AuditLogsIndex.vue';
import PublishAuditsIndex from '../pages/PublishAuditsIndex.vue';
import SharedComponentsShowcase from '../pages/SharedComponentsShowcase.vue';
import OpenclawTaskLogsIndex from '../pages/OpenclawTaskLogsIndex.vue';
import PersonalityQuizOps from '../pages/PersonalityQuizOps.vue';

const auth = (title) => ({ auth: true, title });

export function createAdminRouter(base) {
    const router = createRouter({
        history: createWebHistory(base),
        routes: [
            { path: '/login', name: 'login', component: Login, meta: { guest: true, title: '登录' } },
            { path: '/', name: 'dashboard', component: Dashboard, meta: auth('仪表盘') },
            { path: '/moderation', name: 'moderation', component: Moderation, meta: auth('投稿审核') },
            { path: '/users', name: 'users', component: Users, meta: auth('用户管理') },
            { path: '/users/:id', name: 'user-edit', component: UserEdit, meta: auth('编辑用户') },
            { path: '/points-ledger', name: 'points-ledger', component: PointsLedgerIndex, meta: auth('积分流水') },
            { path: '/orders', name: 'orders', component: Orders, meta: auth('订单管理') },
            { path: '/subscriptions', name: 'subscriptions', component: SubscriptionsIndex, meta: auth('会员订阅') },
            { path: '/view-histories', name: 'view-histories', component: ViewHistoriesIndex, meta: auth('浏览历史') },
            {
                path: '/svip-custom-subscriptions',
                name: 'svip-custom-subscriptions',
                component: SvipCustomSubscriptionsIndex,
                meta: auth('SVIP 定制订阅'),
            },
            { path: '/articles', name: 'articles', component: ArticlesIndex, meta: auth('文章管理') },
            { path: '/articles/create', name: 'article-create', component: ArticleEditor, meta: auth('新建文章') },
            {
                path: '/articles/:id(\\d+)/edit',
                name: 'article-edit',
                component: ArticleEditor,
                meta: auth('编辑文章'),
            },
            { path: '/projects', name: 'projects', component: ProjectsIndex, meta: auth('项目管理') },
            { path: '/projects/create', name: 'project-create', component: ProjectEditor, meta: auth('新建项目') },
            {
                path: '/projects/:id(\\d+)/edit',
                name: 'project-edit',
                component: ProjectEditor,
                meta: auth('编辑项目'),
            },
            { path: '/categories', name: 'categories', component: CategoriesIndex, meta: auth('分类管理') },
            { path: '/comments', name: 'comments', component: CommentsIndex, meta: auth('评论管理') },
            { path: '/settings', name: 'settings', component: Settings, meta: auth('系统与站点') },
            { path: '/email-templates', name: 'email-templates', component: EmailTemplates, meta: auth('邮件模板') },
            { path: '/email-logs', name: 'email-logs', component: EmailLogs, meta: auth('邮件记录') },
            {
                path: '/email-subscriptions',
                name: 'email-subscriptions',
                component: EmailSubscriptionsIndex,
                meta: auth('邮箱订阅'),
            },
            {
                path: '/site-testimonials',
                name: 'site-testimonials',
                component: SiteTestimonialsIndex,
                meta: auth('首页评价'),
            },
            { path: '/announcements', name: 'announcements', component: AnnouncementsIndex, meta: auth('公告管理') },
            { path: '/ad-slots', name: 'ad-slots', component: AdSlots, meta: auth('广告位') },
            { path: '/openclaw-task-logs', name: 'openclaw-task-logs', component: OpenclawTaskLogsIndex, meta: auth('OpenClaw 任务日志') },
            { path: '/premium-resources', name: 'premium-resources', component: PremiumResourcesIndex, meta: auth('会员资源') },
            { path: '/side-hustle-cases', name: 'side-hustle-cases', component: SideHustleCasesIndex, meta: auth('副业案例') },
            { path: '/private-traffic-sops', name: 'private-traffic-sops', component: PrivateTrafficSopsIndex, meta: auth('私域 SOP') },
            {
                path: '/ai-tool-monetization',
                name: 'ai-tool-monetization',
                component: AiToolMonetizationIndex,
                meta: auth('AI 工具变现'),
            },
            {
                path: '/system-notifications',
                name: 'system-notifications',
                component: SystemNotificationsIndex,
                meta: auth('系统通知'),
            },
            { path: '/skin-configs', name: 'skin-configs', component: SkinConfigsIndex, meta: auth('皮肤主题') },
            { path: '/email-settings', name: 'email-settings', component: EmailSettingsIndex, meta: auth('邮件配置') },
            {
                path: '/push-notifications',
                name: 'push-notifications',
                component: PushNotificationsIndex,
                meta: auth('站内推送'),
            },
            { path: '/refund-requests', name: 'refund-requests', component: RefundRequestsIndex, meta: auth('退款申请') },
            { path: '/invoice-requests', name: 'invoice-requests', component: InvoiceRequestsIndex, meta: auth('发票申请') },
            { path: '/comment-reports', name: 'comment-reports', component: CommentReportsIndex, meta: auth('评论举报') },
            { path: '/audit-logs', name: 'audit-logs', component: AuditLogsIndex, meta: auth('操作审计') },
            { path: '/publish-audits', name: 'publish-audits', component: PublishAuditsIndex, meta: auth('发布审核记录') },
            {
                path: '/shared-components',
                name: 'shared-components',
                component: SharedComponentsShowcase,
                meta: auth('公共组件'),
            },
            { path: '/personality-quiz', name: 'personality-quiz', component: PersonalityQuizOps, meta: auth('SBTI 配置') },
        ],
    });

    router.beforeEach(async (to, from, next) => {
        const needsAuth = to.matched.some((r) => r.meta.auth);
        const isGuest = to.matched.some((r) => r.meta.guest);

        if (isGuest) {
            // 强制停留在登录页（用于“退出登录”后避免被 guest 守卫立刻跳回仪表盘）
            if (to.query?.force === '1') {
                next();
                return;
            }
            try {
                await axios.get('/api/admin/me');
                next({ name: 'dashboard' });
            } catch {
                next();
            }
            return;
        }
        if (needsAuth) {
            try {
                await axios.get('/api/admin/me');
                next();
            } catch {
                next({ name: 'login', query: { redirect: to.fullPath } });
            }
            return;
        }
        next();
    });

    router.afterEach(() => {
        nextTick(() => {
            const el = document.querySelector('[data-admin-scroll-root]');
            if (el) {
                el.scrollTop = 0;
            }
        });
    });

    return router;
}
