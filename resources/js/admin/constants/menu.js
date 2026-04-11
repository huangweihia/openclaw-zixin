/**
 * 后台侧边栏（固定）；已合并分组减少层级，业务闭环见「系统与站点」
 */
export const ADMIN_MENU = [
    {
        section: '总览',
        items: [{ to: '/', label: '仪表盘', icon: '📊', match: 'exact', perm: 'admin:dashboard:read' }],
    },
    {
        section: '内容中心',
        items: [
            { to: '/articles', label: '文章管理', icon: '📝', perm: 'admin:articles:read' },
            { to: '/projects', label: '项目管理', icon: '📦', perm: 'admin:projects:read' },
            { to: '/categories', label: '分类管理', icon: '🗂️', perm: 'admin:categories:read' },
            { to: '/premium-resources', label: '会员资源', icon: '💎', perm: 'admin:premium-resources:read' },
            { to: '/side-hustle-cases', label: '副业案例', icon: '💼', perm: 'admin:side-hustle-cases:read' },
            { to: '/private-traffic-sops', label: '私域 SOP', icon: '📱', perm: 'admin:private-traffic-sops:read' },
            { to: '/ai-tool-monetization', label: 'AI 工具变现', icon: '🤖', perm: 'admin:ai-tool-monetization:read' },
        ],
    },
    {
        section: '审核与社区',
        items: [
            { to: '/moderation', label: '投稿审核', icon: '✅', perm: 'admin:moderation:read' },
            { to: '/comments', label: '评论管理', icon: '💬', perm: 'admin:comments:read' },
        ],
    },
    {
        section: '交易 · 用户 · 售后',
        items: [
            { to: '/orders', label: '订单管理', icon: '💳', perm: 'admin:orders:read' },
            { to: '/subscriptions', label: '会员订阅', icon: '👑', perm: 'admin:subscriptions:read' },
            { to: '/svip-custom-subscriptions', label: 'SVIP 定制', icon: '💠', perm: 'admin:svip-custom-subscriptions:read' },
            { to: '/view-histories', label: '浏览历史', icon: '👁️', perm: 'admin:view-histories:read' },
            { to: '/users', label: '用户管理', icon: '👤', perm: 'admin:users:read' },
            { to: '/points-ledger', label: '积分流水', icon: '🪙', perm: 'admin:points-ledger:read' },
            { to: '/refund-requests', label: '退款申请', icon: '↩️', perm: 'admin:refund-requests:read' },
            { to: '/invoice-requests', label: '发票申请', icon: '🧾', perm: 'admin:invoice-requests:read' },
            { to: '/comment-reports', label: '评论举报', icon: '🚨', perm: 'admin:comment-reports:read' },
        ],
    },
    {
        section: '运营与触达',
        items: [
            { to: '/email-templates', label: '邮件模板', icon: '✉️', perm: 'admin:email-templates:read' },
            { to: '/email-logs', label: '邮件记录', icon: '📨', perm: 'admin:email-logs:read' },
            { to: '/email-subscriptions', label: '邮箱订阅', icon: '📬', perm: 'admin:email-subscriptions:read' },
            { to: '/site-testimonials', label: '首页评价', icon: '⭐', perm: 'admin:site-testimonials:read' },
            { to: '/announcements', label: '公告管理', icon: '📣', perm: 'admin:announcements:read' },
            { to: '/system-notifications', label: '系统通知', icon: '🔔', perm: 'admin:system-notifications:read' },
            { to: '/push-notifications', label: '站内推送', icon: '📲', perm: 'admin:push-notifications:read' },
            { to: '/skin-configs', label: '皮肤主题', icon: '🎨', perm: 'admin:skin-configs:read' },
            { to: '/ad-slots', label: '广告位', icon: '📢', perm: 'admin:ad-slots:read' },
            { to: '/openclaw-task-logs', label: '任务日志', icon: '🧾', perm: 'admin:openclaw-task-logs:read' },
        ],
    },
    {
        section: '小游戏',
        items: [
            { to: '/personality-quiz', label: 'SBTI 配置', icon: '🧠', perm: 'admin:settings:read' },
        ],
    },
    {
        section: '系统',
        items: [
            { to: '/settings', label: '系统与站点', icon: '⚙️', perm: 'admin:settings:read' },
            { to: '/email-settings', label: '邮件 SMTP 配置', icon: '🔧', perm: 'admin:email-settings:read' },
            { to: '/shared-components', label: '公共组件', icon: '🧩', perm: 'admin:shared-components:read' },
            { to: '/audit-logs', label: '操作审计', icon: '📋', perm: 'admin:audit-logs:read' },
            { to: '/publish-audits', label: '发布审核记录', icon: '📄', perm: 'admin:publish-audits:read' },
        ],
    },
];

export function isMenuItemActive(routePath, item) {
    const p = routePath;
    if (item.match === 'exact') {
        return p === '/' || p === '';
    }
    if (item.to === '/') {
        return p === '/' || p === '';
    }
    return p === item.to || p.startsWith(`${item.to}/`);
}
