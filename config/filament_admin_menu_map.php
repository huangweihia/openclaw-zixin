<?php

/**
 * Filament Resource 与 admin_nav_items.menu_key 对应。
 * 有映射的 Resource 会从数据库读取侧边栏分组、排序、标题，并受 RBAC + 菜单白名单控制。
 */
return [
    \App\Filament\Resources\ArticleResource::class => 'articles',
    \App\Filament\Resources\CategoryResource::class => 'categories',
    \App\Filament\Resources\ProjectResource::class => 'projects',
    \App\Filament\Resources\UserPostResource::class => 'moderation',
    \App\Filament\Resources\CommentResource::class => 'comments',
    \App\Filament\Resources\OrderResource::class => 'orders',
    \App\Filament\Resources\SubscriptionResource::class => 'subscriptions',
    \App\Filament\Resources\SvipCustomSubscriptionResource::class => 'svip-custom-subscriptions',
    \App\Filament\Resources\ViewHistoryResource::class => 'view-histories',
    \App\Filament\Resources\UserResource::class => 'users',
    \App\Filament\Resources\PointResource::class => 'points-ledger',
    \App\Filament\Resources\RefundRequestResource::class => 'refund-requests',
    \App\Filament\Resources\InvoiceRequestResource::class => 'invoice-requests',
    \App\Filament\Resources\CommentReportResource::class => 'comment-reports',
    \App\Filament\Resources\EmailTemplateResource::class => 'email-templates',
    \App\Filament\Resources\EmailLogResource::class => 'email-logs',
    \App\Filament\Resources\EmailSubscriptionResource::class => 'email-subscriptions',
    \App\Filament\Resources\SiteTestimonialResource::class => 'site-testimonials',
    \App\Filament\Resources\AnnouncementResource::class => 'announcements',
    \App\Filament\Resources\SystemNotificationResource::class => 'system-notifications',
    \App\Filament\Resources\PushNotificationResource::class => 'push-notifications',
    \App\Filament\Resources\SkinConfigResource::class => 'skin-configs',
    \App\Filament\Resources\AdSlotResource::class => 'ad-slots',
    \App\Filament\Resources\OpenclawTaskLogResource::class => 'openclaw-task-logs',
    \App\Filament\Resources\MemberPremiumResource::class => 'premium-resources',
    \App\Filament\Resources\SideHustleCaseResource::class => 'side-hustle-cases',
    \App\Filament\Resources\PrivateTrafficSopResource::class => 'private-traffic-sops',
    \App\Filament\Resources\AiToolMonetizationResource::class => 'ai-tool-monetization',
    \App\Filament\Resources\SiteSettingResource::class => 'settings',
    \App\Filament\Resources\EmailSettingResource::class => 'email-settings',
    \App\Filament\Resources\AuditLogResource::class => 'audit-logs',
    \App\Filament\Resources\PublishAuditResource::class => 'publish-audits',
    \App\Filament\Resources\AdminRoleResource::class => 'admin-roles',
    \App\Filament\Resources\AdminNavItemResource::class => 'nav-menus',
];
