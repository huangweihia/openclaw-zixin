/**
 * 数据库存英文枚举 / 字段值 → 后台界面中文说明（仅展示层，不改 API 传参）。
 */
export const ENUM_LABELS = {
    /** email_logs.status */
    emailLogStatus: {
        pending: '待发送',
        sent: '已发送',
        failed: '发送失败',
    },
    /** subscriptions.plan */
    subscriptionPlan: {
        monthly: '月度',
        yearly: '年度',
        lifetime: '终身',
    },
    /** subscriptions.status */
    subscriptionStatus: {
        pending: '待支付',
        active: '生效中',
        expired: '已过期',
        cancelled: '已取消',
    },
    /** svip_custom_subscriptions.status */
    svipCustomSubscriptionStatus: {
        pending: '待处理',
        active: '生效中',
        completed: '已完成',
        cancelled: '已取消',
    },
    /** skin_configs.type */
    skinType: {
        free: '免费',
        vip: 'VIP',
        svip: 'SVIP',
    },
    /** projects.difficulty */
    projectDifficulty: {
        easy: '简单',
        medium: '中等',
        hard: '困难',
    },
    /** ad_slots.type */
    adSlotType: {
        banner: '横幅（图片）',
        banner_video: '横幅（视频）',
        sidebar: '侧栏',
        inline: '信息流',
        popup: '弹窗',
        float: '浮动角标',
    },
    /** ad_slots.audience */
    adAudience: {
        all: '所有人',
        guest: '仅游客',
        user: '仅登录用户',
        vip: 'VIP（含管理员）',
        svip: 'SVIP（含管理员）',
        admin: '仅管理员',
        member: '会员（VIP/SVIP/管理员）',
        non_member: '非会员（游客/普通用户）',
    },
    /** openclaw_task_logs.task_type */
    openclawTaskType: {
        ai_content: 'AI 内容采集',
        svip_subscription: 'SVIP 订阅',
        svip_content: 'SVIP 内容',
        daily_news: '日报',
    },
    /** openclaw_task_logs.status */
    openclawTaskStatus: {
        success: '成功',
        error: '失败',
        timeout: '超时',
        skipped: '跳过',
    },
    /** openclaw_task_logs.push_status */
    openclawPushStatus: {
        success: '成功',
        failed: '失败',
        not_attempted: '未推送',
    },
    /** orders.status */
    orderStatus: {
        pending: '待支付',
        paid: '已支付',
        failed: '支付失败',
        refunded: '已退款',
    },
    /** orders.product_type（字符串，常见取值） */
    orderProductType: {
        vip: 'VIP 会员',
        svip: 'SVIP 会员',
        subscription_plan: '订阅套餐',
    },
    /** refund_requests.status */
    refundStatus: {
        pending: '待处理',
        approved: '已通过',
        rejected: '已拒绝',
        completed: '已完成',
    },
    /** comment_reports.status */
    commentReportStatus: {
        pending: '待处理',
        processed: '已处理',
        rejected: '已驳回',
    },
    /** invoice_requests.status */
    invoiceStatus: {
        pending: '待开票',
        issued: '已开票',
        rejected: '已拒绝',
    },
    /** invoice_requests.invoice_type（常见取值） */
    invoiceRequestType: {
        personal: '个人发票',
        company: '企业发票',
        vat: '增值税专票',
        electronic: '电子发票',
    },
    /** publish_audits.status */
    publishAuditStatus: {
        pending: '待审核',
        approved: '已通过',
        rejected: '已拒绝',
    },
    /** side_hustle_cases.category */
    sideHustleCategory: {
        online: '线上',
        offline: '线下',
        hybrid: '混合',
    },
    /** side_hustle_cases.type */
    sideHustleType: {
        ecommerce: '电商',
        content: '内容',
        service: '服务',
        other: '其他',
    },
    /** side_hustle_cases.visibility */
    resourceVisibility: {
        public: '公开',
        vip: '会员可见',
        private: '仅本人',
    },
    /** side_hustle_cases.status */
    sideHustleStatus: {
        pending: '待审核',
        approved: '已发布',
        rejected: '已拒绝',
    },
    /** user_posts.type */
    userPostType: {
        case: '案例',
        tool: '工具',
        experience: '经验',
        resource: '资源',
        question: '问答',
    },
    /** system_notifications.priority */
    systemNotifPriority: {
        low: '低',
        medium: '中',
        high: '高',
    },
    /** system_notifications.type */
    systemNotifType: {
        system: '系统',
        announcement: '公告',
        maintenance: '维护',
    },
    /** ai_tool_monetization.category */
    aiToolCategory: {
        image: '图像',
        text: '文本',
        video: '视频',
        audio: '音频',
        code: '代码',
    },
    /** ai_tool_monetization.monetization_type */
    aiToolMonetization: {
        free: '免费',
        subscription: '订阅',
        pay_as_you_go: '按次付费',
    },
    /** premium_resources.type */
    premiumResourceType: {
        pdf: 'PDF',
        video: '视频',
        cloud_drive: '网盘',
        ebook: '电子书',
    },
    /** private_traffic_sops.platform */
    privateTrafficPlatform: {
        wechat: '微信',
        xiaohongshu: '小红书',
        douyin: '抖音',
        other: '其他',
    },
    /** private_traffic_sops.category */
    privateTrafficCategory: {
        traffic: '引流',
        operation: '运营',
        conversion: '转化',
        retention: '留存',
    },
    /**
     * users.role
     * （注册赠送等场景若只需 VIP/SVIP，在页面里对 enumOptions('userRole') 做 filter 即可）
     */
    userRole: {
        user: '普通用户',
        vip: 'VIP',
        svip: 'SVIP',
        admin: '管理员',
    },
    /** audit_logs.action（常见取值） */
    auditAction: {
        create: '创建',
        update: '更新',
        delete: '删除',
        login: '登录',
        logout: '退出',
    },
};

/**
 * @param {keyof typeof ENUM_LABELS} group
 * @param {string|null|undefined} value
 */
export function enumLabel(group, value) {
    if (value == null || value === '') {
        return '—';
    }
    const map = ENUM_LABELS[group];
    if (!map) {
        return String(value);
    }
    return map[value] ?? String(value);
}

/** 用于 &lt;select&gt;：value 仍为库内枚举，文案中文 */
export function enumOptions(group) {
    const map = ENUM_LABELS[group];
    if (!map) {
        return [];
    }
    return Object.entries(map).map(([value, label]) => ({ value, label }));
}
