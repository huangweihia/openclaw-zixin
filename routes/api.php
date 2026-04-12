<?php

use App\Http\Controllers\Api\PublicEmailSubscriptionController;
use App\Http\Controllers\Api\SkinController;
use App\Http\Controllers\Api\SvipContentController;
use App\Http\Controllers\Api\SvipSubscriptionController;
use App\Http\Controllers\Api\OpenClawDataController;
use App\Http\Controllers\Api\OpenClawTaskLogController;
use App\Http\Controllers\Api\PersonalityQuizAdminController;
use App\Http\Controllers\Api\PersonalityQuizController;
use App\Http\Controllers\Api\PublicArticleController;
use App\Http\Controllers\Api\PublicBrowseController;
use App\Http\Controllers\Api\PublicAnnouncementController;
use App\Http\Controllers\Api\PublicArticleCommentController;
use App\Http\Controllers\Api\WeChatMiniArticleCommentController;
use App\Http\Controllers\Api\WeChatMiniAuthController;
use App\Http\Controllers\Api\WeChatMiniProfileController;
use App\Http\Controllers\Api\WeChatMiniEmailSubscriptionController;
use App\Http\Controllers\Api\WeChatMiniSvipSubscriptionController;
use App\Http\Controllers\Api\WeChatMiniSubscriptionFeedController;
use App\Http\Controllers\Api\WeChatMiniSubscribeController;
use App\Http\Controllers\Api\WeChatMiniInboxController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| 部署后若 /api/public/* 返回 404：请在服务器执行 php artisan route:clear
| 并确认 Web 根目录指向 public/，且已发布含 PublicBrowseController 的代码。
|
*/

/** 健康检查：浏览器访问 GET /api/ping 应返回 JSON，用于确认路由与 Laravel 生效 */
Route::get('/ping', static fn () => response()->json([
    'ok' => true,
    'service' => 'openclaw-zixin',
    'time' => now()->toIso8601String(),
]));

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 微信小程序：code 换 Sanctum Token（限流）
Route::post('/wechat/mini/login', [WeChatMiniAuthController::class, 'login'])
    ->middleware('throttle:30,1');
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/wechat/mini/me', [WeChatMiniAuthController::class, 'me']);
    Route::post('/wechat/mini/logout', [WeChatMiniAuthController::class, 'logout']);
    Route::post('/wechat/mini/bind-email/send-code', [WeChatMiniAuthController::class, 'bindEmailSendCode'])
        ->middleware('throttle:8,1');
    Route::post('/wechat/mini/bind-email/verify', [WeChatMiniAuthController::class, 'bindEmailVerify'])
        ->middleware('throttle:20,1');
    Route::get('/wechat/mini/stats', [WeChatMiniProfileController::class, 'stats']);
    Route::get('/wechat/mini/orders', [WeChatMiniProfileController::class, 'orders']);
    Route::get('/wechat/mini/favorites', [WeChatMiniProfileController::class, 'favorites']);
    Route::get('/wechat/mini/posts', [WeChatMiniProfileController::class, 'posts']);
    Route::get('/wechat/mini/svip-subscriptions', [WeChatMiniSvipSubscriptionController::class, 'index']);
    Route::get('/wechat/mini/subscription-feed', [WeChatMiniSubscriptionFeedController::class, 'index']);
    Route::get('/wechat/mini/subscribe-config', [WeChatMiniSubscribeController::class, 'config'])
        ->middleware('throttle:60,1');
    Route::get('/wechat/mini/inbox/unread-count', [WeChatMiniInboxController::class, 'unreadCount']);
    Route::get('/wechat/mini/inbox', [WeChatMiniInboxController::class, 'index']);
    Route::post('/wechat/mini/inbox/read-all', [WeChatMiniInboxController::class, 'readAll']);
    Route::post('/wechat/mini/inbox/{id}/read', [WeChatMiniInboxController::class, 'markRead'])
        ->where('id', '[0-9]+');
    Route::get('/wechat/mini/email-subscription', [WeChatMiniEmailSubscriptionController::class, 'show']);
    /** @deprecated 请使用 POST /api/public/articles/{slug}/comments；保留以兼容旧包 */
    Route::post('/wechat/mini/articles/{slug}/comments', [WeChatMiniArticleCommentController::class, 'store']);
});

Route::post('/email-subscriptions', [PublicEmailSubscriptionController::class, 'store']);
Route::get('/email-unsubscribe/{token}', [PublicEmailSubscriptionController::class, 'unsubscribe'])
    ->where('token', '[A-Za-z0-9]+');

// 皮肤 API：GET 走可选 Bearer；PUT/DELETE 仅用 auth:sanctum（避免与 sanctum.optional 叠加导致部分环境无法识别登录态）
Route::prefix('skins')->group(function () {
    Route::middleware('sanctum.optional')->group(function () {
        Route::get('/', [SkinController::class, 'index']);           // GET /api/skins
        Route::get('/current', [SkinController::class, 'show']);     // GET /api/skins/current
    });
    Route::put('/current', [SkinController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/current', [SkinController::class, 'destroy'])->middleware('auth:sanctum');
});

// SVIP 内容获取 - OpenClaw 定时任务调用
Route::get('/svip/content', [SvipContentController::class, 'index']);

// SVIP 订阅管理 - OpenClaw 定时任务调用
Route::get('/svip/subscriptions/list', [SvipSubscriptionController::class, 'list']);
Route::post('/svip/subscriptions/data', [SvipSubscriptionController::class, 'pushData']);

// OpenClaw Data - 接收 AI 处理后的内容
Route::post('/openclaw/data', [OpenClawDataController::class, 'store']);

// OpenClaw Task Logs - 接收定时任务执行日志
Route::post('/openclaw/task-log', [OpenClawTaskLogController::class, 'store']);

// 发表评论：仅 Bearer 登录，不经过 sanctum.optional（与换肤 PUT 同理，避免中间件链导致未认证）
Route::prefix('public')->middleware(['throttle:120,1', 'auth:sanctum'])->group(function () {
    Route::post('articles/{slug}/comments', [WeChatMiniArticleCommentController::class, 'store']);
});

// 前台公开内容（小程序 / 第三方 JSON）；sanctum.optional 用于带 Token 时识别 VIP 可读全文
Route::prefix('public')->middleware(['throttle:120,1', 'sanctum.optional'])->group(function () {
    Route::get('categories', [PublicArticleController::class, 'categories']);
    Route::get('announcements', [PublicAnnouncementController::class, 'index']);
    Route::get('announcements/{id}', [PublicAnnouncementController::class, 'show'])->whereNumber('id');
    Route::get('articles', [PublicArticleController::class, 'index']);
    Route::get('articles/{slug}/comments', [PublicArticleCommentController::class, 'index']);
    Route::get('articles/{slug}', [PublicArticleController::class, 'show']);

    Route::get('browse/projects', [PublicBrowseController::class, 'projectsIndex']);
    Route::get('browse/projects/{project}', [PublicBrowseController::class, 'projectsShow']);
    Route::get('browse/cases', [PublicBrowseController::class, 'casesIndex']);
    Route::get('browse/cases/{slug}', [PublicBrowseController::class, 'casesShow']);
    Route::get('browse/tools', [PublicBrowseController::class, 'toolsIndex']);
    Route::get('browse/tools/{slug}', [PublicBrowseController::class, 'toolsShow']);
    Route::get('browse/sops', [PublicBrowseController::class, 'sopsIndex']);
    Route::get('browse/sops/{slug}', [PublicBrowseController::class, 'sopsShow']);
});

// SBTI（公开）
Route::get('/personality-quiz', [PersonalityQuizController::class, 'show']);
Route::post('/personality-quiz/submit', [PersonalityQuizController::class, 'submit'])
    ->middleware('throttle:40,1');

// SBTI 管理（无登录，凭 PERSONALITY_QUIZ_ADMIN_TOKEN）
Route::prefix('personality-quiz/admin')->middleware('personality.quiz.admin')->group(function () {
    Route::get('/bootstrap', [PersonalityQuizAdminController::class, 'bootstrap']);
    Route::post('/dimensions', [PersonalityQuizAdminController::class, 'storeDimension']);
    Route::patch('/dimensions/{dimension}', [PersonalityQuizAdminController::class, 'updateDimension']);
    Route::delete('/dimensions/{dimension}', [PersonalityQuizAdminController::class, 'destroyDimension']);
    Route::post('/questions', [PersonalityQuizAdminController::class, 'storeQuestion']);
    Route::patch('/questions/{question}', [PersonalityQuizAdminController::class, 'updateQuestion']);
    Route::delete('/questions/{question}', [PersonalityQuizAdminController::class, 'destroyQuestion']);
    Route::post('/options', [PersonalityQuizAdminController::class, 'storeOption']);
    Route::patch('/options/{option}', [PersonalityQuizAdminController::class, 'updateOption']);
    Route::delete('/options/{option}', [PersonalityQuizAdminController::class, 'destroyOption']);
    Route::post('/types', [PersonalityQuizAdminController::class, 'storeType']);
    Route::patch('/types/{type}', [PersonalityQuizAdminController::class, 'updateType']);
    Route::delete('/types/{type}', [PersonalityQuizAdminController::class, 'destroyType']);
    Route::put('/settings', [PersonalityQuizAdminController::class, 'putSetting']);
});
