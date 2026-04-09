<?php

use App\Http\Controllers\Api\Admin\AdSlotController;
use App\Http\Controllers\Api\Admin\AiToolMonetizationController;
use App\Http\Controllers\Api\Admin\AnnouncementController;
use App\Http\Controllers\Api\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Api\Admin\AuditLogController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Api\Admin\CommentReportController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\EmailLogController;
use App\Http\Controllers\Api\Admin\EmailSettingController;
use App\Http\Controllers\Api\Admin\EmailSubscriptionController;
use App\Http\Controllers\Api\Admin\EmailTemplateController;
use App\Http\Controllers\Api\Admin\InvoiceRequestController;
use App\Http\Controllers\Api\Admin\OrderController;
use App\Http\Controllers\Api\Admin\PremiumResourceController;
use App\Http\Controllers\Api\Admin\PrivateTrafficSopController;
use App\Http\Controllers\Api\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Api\Admin\PublishAuditController;
use App\Http\Controllers\Api\Admin\PushNotificationController;
use App\Http\Controllers\Api\Admin\RefundRequestController;
use App\Http\Controllers\Api\Admin\SideHustleCaseController;
use App\Http\Controllers\Api\Admin\SiteSettingController;
use App\Http\Controllers\Api\Admin\SiteTestimonialController;
use App\Http\Controllers\Api\Admin\SkinConfigController;
use App\Http\Controllers\Api\Admin\SubscriptionController;
use App\Http\Controllers\Api\Admin\ViewHistoryAdminController;
use App\Http\Controllers\Api\Admin\SvipCustomSubscriptionAdminController;
use App\Http\Controllers\Api\Admin\SystemNotificationController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\PointLedgerController;
use App\Http\Controllers\Api\Admin\OpenclawTaskLogAdminController;
use App\Http\Controllers\Api\Admin\UploadController;
use App\Http\Controllers\Api\Admin\UserPostModerationController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('dashboard/stats', [DashboardController::class, 'stats'])->middleware('perm:admin:dashboard:read');
    Route::post('uploads/image', [UploadController::class, 'image']);
    Route::middleware('perm:admin:moderation:read')->group(function () {
        Route::get('user-posts/pending', [UserPostModerationController::class, 'index']);
        Route::post('user-posts/{userPost}/approve', [UserPostModerationController::class, 'approve']);
        Route::post('user-posts/{userPost}/reject', [UserPostModerationController::class, 'reject']);
        Route::post('user-posts/batch-approve', [UserPostModerationController::class, 'batchApprove']);
        Route::post('user-posts/batch-reject', [UserPostModerationController::class, 'batchReject']);
    });

    Route::middleware('perm:admin:users:read')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::post('users/{user}/disable', [UserController::class, 'disable']);
        Route::post('users/{user}/enable', [UserController::class, 'enable']);
        Route::post('users/{user}/clear-enterprise-wechat', [UserController::class, 'clearEnterpriseWechat']);
        Route::post('users/{user}/membership', [UserController::class, 'updateMembership']);
        Route::post('users/{user}/admin-profile', [UserController::class, 'updateAdminProfile']);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword']);
    });

    Route::get('points-ledger', [PointLedgerController::class, 'index'])->middleware('perm:admin:points-ledger:read');

    Route::middleware('perm:admin:orders:read')->group(function () {
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::put('orders/{order}', [OrderController::class, 'update']);
    });

    Route::middleware('perm:admin:articles:read')->group(function () {
        Route::get('articles', [AdminArticleController::class, 'index']);
        Route::post('articles', [AdminArticleController::class, 'store']);
        Route::get('articles/{articleId}', [AdminArticleController::class, 'show'])->whereNumber('articleId');
        Route::put('articles/{articleId}', [AdminArticleController::class, 'update'])->whereNumber('articleId');
        Route::delete('articles/{articleId}', [AdminArticleController::class, 'destroy'])->whereNumber('articleId');
    });

    Route::middleware('perm:admin:projects:read')->group(function () {
        Route::get('projects', [AdminProjectController::class, 'index']);
        Route::post('projects', [AdminProjectController::class, 'store']);
        Route::get('projects/{projectId}', [AdminProjectController::class, 'show'])->whereNumber('projectId');
        Route::put('projects/{projectId}', [AdminProjectController::class, 'update'])->whereNumber('projectId');
        Route::delete('projects/{projectId}', [AdminProjectController::class, 'destroy'])->whereNumber('projectId');
    });

    Route::middleware('perm:admin:categories:read')->group(function () {
        Route::get('categories', [AdminCategoryController::class, 'index']);
        Route::post('categories', [AdminCategoryController::class, 'store']);
        Route::get('categories/{categoryId}', [AdminCategoryController::class, 'show'])->whereNumber('categoryId');
        Route::put('categories/{categoryId}', [AdminCategoryController::class, 'update'])->whereNumber('categoryId');
        Route::delete('categories/{categoryId}', [AdminCategoryController::class, 'destroy'])->whereNumber('categoryId');
    });

    Route::middleware('perm:admin:comments:read')->group(function () {
        Route::get('comments', [AdminCommentController::class, 'index']);
        Route::patch('comments/{commentId}', [AdminCommentController::class, 'update'])->whereNumber('commentId');
        Route::delete('comments/{commentId}', [AdminCommentController::class, 'destroy'])->whereNumber('commentId');
    });

    Route::middleware('perm:admin:settings:read')->group(function () {
        Route::get('settings', [SiteSettingController::class, 'index']);
        Route::put('settings', [SiteSettingController::class, 'update']);
    });

    Route::middleware('perm:admin:site-testimonials:read')->group(function () {
        Route::get('site-testimonials', [SiteTestimonialController::class, 'index']);
        Route::post('site-testimonials', [SiteTestimonialController::class, 'store']);
        Route::put('site-testimonials/{siteTestimonial}', [SiteTestimonialController::class, 'update']);
        Route::delete('site-testimonials/{siteTestimonial}', [SiteTestimonialController::class, 'destroy']);
    });

    Route::middleware('perm:admin:email-templates:read')->group(function () {
        Route::get('email-templates', [EmailTemplateController::class, 'index']);
        Route::post('email-templates', [EmailTemplateController::class, 'store']);
        Route::put('email-templates/{id}', [EmailTemplateController::class, 'update'])->whereNumber('id');
        Route::post('email-templates/{id}/toggle', [EmailTemplateController::class, 'toggle'])->whereNumber('id');
        Route::post('email-templates/{id}/preview', [EmailTemplateController::class, 'preview'])->whereNumber('id');
        Route::delete('email-templates/{id}', [EmailTemplateController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware('perm:admin:email-logs:read')->group(function () {
        Route::get('email-logs', [EmailLogController::class, 'index']);
        Route::get('email-logs/{id}', [EmailLogController::class, 'show'])->whereNumber('id');
    });

    Route::middleware('perm:admin:email-subscriptions:read')->group(function () {
        Route::get('email-subscriptions', [EmailSubscriptionController::class, 'index']);
        Route::post('email-subscriptions', [EmailSubscriptionController::class, 'store']);
        Route::put('email-subscriptions/{emailSubscription}', [EmailSubscriptionController::class, 'update']);
        Route::delete('email-subscriptions/{emailSubscription}', [EmailSubscriptionController::class, 'destroy']);
        Route::post('email-subscriptions/{emailSubscription}/regenerate-token', [EmailSubscriptionController::class, 'regenerateToken']);
    });

    Route::middleware('perm:admin:email-settings:read')->group(function () {
        Route::get('email-settings', [EmailSettingController::class, 'index']);
        Route::post('email-settings', [EmailSettingController::class, 'store']);
        Route::put('email-settings/{id}', [EmailSettingController::class, 'update'])->whereNumber('id');
        Route::delete('email-settings/{id}', [EmailSettingController::class, 'destroy'])->whereNumber('id');
        Route::post('email-settings/test-send', [EmailSettingController::class, 'testSend']);
    });

    Route::middleware('perm:admin:announcements:read')->group(function () {
        Route::get('announcements', [AnnouncementController::class, 'index']);
        Route::post('announcements', [AnnouncementController::class, 'store']);
        Route::put('announcements/{id}', [AnnouncementController::class, 'update'])->whereNumber('id');
        Route::post('announcements/{id}/toggle-publish', [AnnouncementController::class, 'togglePublish'])->whereNumber('id');
        Route::delete('announcements/{id}', [AnnouncementController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware('perm:admin:ad-slots:read')->group(function () {
        Route::get('ad-slots', [AdSlotController::class, 'index']);
        Route::post('ad-slots', [AdSlotController::class, 'store']);
        Route::put('ad-slots/{id}', [AdSlotController::class, 'update'])->whereNumber('id');
        Route::post('ad-slots/{id}/toggle', [AdSlotController::class, 'toggle'])->whereNumber('id');
    });

    Route::middleware('perm:admin:openclaw-task-logs:read')->group(function () {
        Route::get('openclaw-task-logs', [OpenclawTaskLogAdminController::class, 'index']);
        Route::get('openclaw-task-logs/stats', [OpenclawTaskLogAdminController::class, 'stats']);
        Route::get('openclaw-task-logs/{id}', [OpenclawTaskLogAdminController::class, 'show'])->whereNumber('id');
        Route::delete('openclaw-task-logs/{id}', [OpenclawTaskLogAdminController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware('perm:admin:premium-resources:read')->group(function () {
        Route::get('premium-resources', [PremiumResourceController::class, 'index']);
        Route::post('premium-resources', [PremiumResourceController::class, 'store']);
        Route::put('premium-resources/{id}', [PremiumResourceController::class, 'update'])->whereNumber('id');
        Route::delete('premium-resources/{id}', [PremiumResourceController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware('perm:admin:side-hustle-cases:read')->group(function () {
        Route::get('side-hustle-cases', [SideHustleCaseController::class, 'index']);
        Route::post('side-hustle-cases', [SideHustleCaseController::class, 'store']);
        Route::put('side-hustle-cases/{id}', [SideHustleCaseController::class, 'update'])->whereNumber('id');
        Route::delete('side-hustle-cases/{id}', [SideHustleCaseController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware('perm:admin:private-traffic-sops:read')->group(function () {
        Route::get('private-traffic-sops', [PrivateTrafficSopController::class, 'index']);
        Route::post('private-traffic-sops', [PrivateTrafficSopController::class, 'store']);
        Route::put('private-traffic-sops/{id}', [PrivateTrafficSopController::class, 'update'])->whereNumber('id');
        Route::delete('private-traffic-sops/{id}', [PrivateTrafficSopController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware('perm:admin:ai-tool-monetization:read')->group(function () {
        Route::get('ai-tool-monetization', [AiToolMonetizationController::class, 'index']);
        Route::post('ai-tool-monetization', [AiToolMonetizationController::class, 'store']);
        Route::put('ai-tool-monetization/{id}', [AiToolMonetizationController::class, 'update'])->whereNumber('id');
        Route::delete('ai-tool-monetization/{id}', [AiToolMonetizationController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware('perm:admin:system-notifications:read')->group(function () {
        Route::get('system-notifications', [SystemNotificationController::class, 'index']);
        Route::post('system-notifications', [SystemNotificationController::class, 'store']);
        Route::put('system-notifications/{id}', [SystemNotificationController::class, 'update'])->whereNumber('id');
        Route::post('system-notifications/{id}/toggle-publish', [SystemNotificationController::class, 'togglePublish'])->whereNumber('id');
        Route::delete('system-notifications/{id}', [SystemNotificationController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware('perm:admin:skin-configs:read')->group(function () {
        Route::get('skin-configs', [SkinConfigController::class, 'index']);
        Route::post('skin-configs', [SkinConfigController::class, 'store']);
        Route::put('skin-configs/{id}', [SkinConfigController::class, 'update'])->whereNumber('id');
        Route::delete('skin-configs/{id}', [SkinConfigController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware('perm:admin:push-notifications:read')->group(function () {
        Route::get('push-notifications', [PushNotificationController::class, 'index']);
        Route::post('push-notifications', [PushNotificationController::class, 'store']);
        Route::put('push-notifications/{id}', [PushNotificationController::class, 'update'])->whereNumber('id');
        Route::delete('push-notifications/{id}', [PushNotificationController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware('perm:admin:refund-requests:read')->group(function () {
        Route::get('refund-requests', [RefundRequestController::class, 'index']);
        Route::put('refund-requests/{id}', [RefundRequestController::class, 'update'])->whereNumber('id');
    });

    Route::middleware('perm:admin:invoice-requests:read')->group(function () {
        Route::get('invoice-requests', [InvoiceRequestController::class, 'index']);
        Route::put('invoice-requests/{id}', [InvoiceRequestController::class, 'update'])->whereNumber('id');
    });

    Route::middleware('perm:admin:comment-reports:read')->group(function () {
        Route::get('comment-reports', [CommentReportController::class, 'index']);
        Route::put('comment-reports/{id}', [CommentReportController::class, 'update'])->whereNumber('id');
    });

    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('perm:admin:audit-logs:read');

    Route::middleware('perm:admin:publish-audits:read')->group(function () {
        Route::get('publish-audits', [PublishAuditController::class, 'index']);
        Route::put('publish-audits/{id}', [PublishAuditController::class, 'update'])->whereNumber('id');
    });

    Route::get('subscriptions', [SubscriptionController::class, 'index'])->middleware('perm:admin:subscriptions:read');
    Route::get('view-histories', [ViewHistoryAdminController::class, 'index'])->middleware('perm:admin:view-histories:read');
    Route::get('svip-custom-subscriptions', [SvipCustomSubscriptionAdminController::class, 'index'])->middleware('perm:admin:svip-custom-subscriptions:read');
});
