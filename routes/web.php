<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PersonalityQuizManageController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\VipController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardOrderController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ArticleEngagementController;
use App\Http\Controllers\ArticleCommentController;
use App\Http\Controllers\ProjectEngagementController;
use App\Http\Controllers\ProjectCommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ViewHistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserPostController;
use App\Http\Controllers\PublishedUserPostController;
use App\Http\Controllers\InboxNotificationController;
use App\Http\Controllers\AnnouncementWebController;
use App\Http\Controllers\SvipCustomSubscriptionWebController;
use App\Http\Controllers\WeComOAuthController;
use App\Http\Controllers\SideHustleCaseWebController;
use App\Http\Controllers\SideHustleCaseEngagementController;
use App\Http\Controllers\SideHustleCaseCommentController;
use App\Http\Controllers\AiToolWebController;
use App\Http\Controllers\PrivateTrafficSopWebController;
use App\Http\Controllers\SopCommentController;
use App\Http\Controllers\UserPostEngagementController;
use App\Http\Controllers\UserPostCommentController;
use App\Http\Controllers\UserPostBoostController;
use App\Http\Controllers\UserRichUploadController;
use App\Http\Controllers\DashboardPointsController;
use App\Http\Controllers\DashboardPointOrderController;
use App\Http\Controllers\PublicUserController;
use App\Http\Controllers\UserMessageController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::redirect('/home', '/');

Route::get('/personality-quiz/manage', PersonalityQuizManageController::class)
    ->middleware('personality.quiz.admin')
    ->name('personality-quiz.manage');

Route::get('/cases', [SideHustleCaseWebController::class, 'index'])->name('cases.index');
Route::get('/cases/{sideHustleCase}', [SideHustleCaseWebController::class, 'show'])->name('cases.show');

Route::get('/tools', [AiToolWebController::class, 'index'])->name('tools.index');
Route::get('/tools/{aiToolMonetization}', [AiToolWebController::class, 'show'])->name('tools.show');

Route::get('/sops', [PrivateTrafficSopWebController::class, 'index'])->name('sops.index');
Route::get('/sops/{privateTrafficSop:slug}', [PrivateTrafficSopWebController::class, 'show'])->name('sops.show');

Route::view('/privacy', 'placeholders.simple', [
    'title' => '隐私政策',
    'description' => '隐私政策正文待法务定稿后发布。',
])->name('privacy');

Route::view('/terms', 'placeholders.simple', [
    'title' => '服务条款',
    'description' => '服务条款正文待法务定稿后发布。',
])->name('terms');

Route::get('/search', SearchController::class)->name('search');

Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

Route::get('/posts', [PublishedUserPostController::class, 'index'])->name('posts.index');
Route::get('/posts/{userPost}', [PublishedUserPostController::class, 'show'])->name('posts.show');

Route::get('/announcements/{announcement}', [AnnouncementWebController::class, 'show'])->name('announcements.show');

Route::get('/users/{user}/snippet', [PublicUserController::class, 'snippet'])->name('users.snippet');

Route::get('/vip', [VipController::class, 'page'])->name('vip');

Route::get('/max/pricing', [VipController::class, 'pricing'])->name('pricing');
Route::get('/payments/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/edit', [DashboardController::class, 'edit'])->name('dashboard.edit');
    Route::get('/dashboard/comments', [DashboardController::class, 'comments'])->name('dashboard.comments');
    Route::get('/dashboard/orders', [DashboardOrderController::class, 'index'])->name('dashboard.orders');
    Route::get('/dashboard/points', [DashboardPointsController::class, 'index'])->name('dashboard.points');
    Route::post('/dashboard/point-orders', [DashboardPointOrderController::class, 'store'])->name('dashboard.point-orders.store');
    Route::post('/dashboard/orders/{order}/refund', [DashboardOrderController::class, 'requestRefund'])->name('dashboard.orders.refund');
    Route::get('/dashboard/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::delete('/dashboard/favorites/{userAction}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    Route::post('/dashboard/favorites/bulk-delete', [FavoriteController::class, 'bulkDestroy'])->name('favorites.bulk-delete');
    Route::get('/dashboard/history', [ViewHistoryController::class, 'index'])->name('history.index');
    Route::delete('/dashboard/history/{viewHistory}', [ViewHistoryController::class, 'destroy'])->name('history.destroy');
    Route::post('/dashboard/history/clear', [ViewHistoryController::class, 'clear'])->name('history.clear');

    Route::get('/dashboard/svip-subscriptions', [SvipCustomSubscriptionWebController::class, 'index'])->name('svip-subscriptions.index');
    Route::post('/dashboard/svip-subscriptions', [SvipCustomSubscriptionWebController::class, 'store'])->name('svip-subscriptions.store');
    Route::post('/dashboard/svip-subscriptions/custom-skin', [SvipCustomSubscriptionWebController::class, 'customizeSkin'])->name('svip-subscriptions.custom-skin');
    Route::delete('/dashboard/svip-subscriptions/{svipCustomSubscription}', [SvipCustomSubscriptionWebController::class, 'destroy'])->name('svip-subscriptions.destroy');

    Route::post('/payments/orders', [PaymentController::class, 'store'])->name('payments.orders.store');
    Route::get('/payments/result', [PaymentController::class, 'result'])->name('payments.result');
    Route::post('/payments/orders/{order}/simulate-paid', [PaymentController::class, 'simulatePaid'])->name('payments.simulate-paid');

    Route::get('/notifications', [InboxNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{inboxNotification}/open', [InboxNotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/{inboxNotification}/read', [InboxNotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [InboxNotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::delete('/notifications/{inboxNotification}', [InboxNotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::post('/profile/name', [ProfileController::class, 'updateName'])->name('profile.name');
    Route::post('/profile/bio', [ProfileController::class, 'updateBio'])->name('profile.bio');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/email/send-code', [ProfileController::class, 'sendEmailChangeCode'])->name('profile.email.send-code');
    Route::post('/profile/email', [ProfileController::class, 'updateEmail'])->name('profile.email');
    Route::post('/profile/wecom', [ProfileController::class, 'updateWecom'])->name('profile.wecom');
    Route::get('/wecom/oauth/start', [WeComOAuthController::class, 'redirectToProvider'])->name('wecom.oauth.start');
    Route::get('/wecom/oauth/callback', [WeComOAuthController::class, 'callback'])->name('wecom.oauth.callback');
    Route::post('/profile/privacy-mode', [ProfileController::class, 'updatePrivacyMode'])->name('profile.privacy');

    Route::get('/dashboard/posts', [UserPostController::class, 'index'])->name('user-posts.index');
    Route::get('/dashboard/publishes', [UserPostController::class, 'index'])->name('dashboard.publishes');
    Route::get('/dashboard/posts/create', [UserPostController::class, 'create'])->name('user-posts.create');
    Route::get('/publish/create', [UserPostController::class, 'create'])->name('publish.create');
    Route::post('/dashboard/posts', [UserPostController::class, 'store'])->name('user-posts.store');
    Route::post('/dashboard/rich-upload/image', [UserRichUploadController::class, 'image'])->name('user.rich-upload.image');

    Route::get('/dashboard/subscriptions', [SvipCustomSubscriptionWebController::class, 'index'])->name('dashboard.subscriptions');

    Route::post('/users/{user}/message', [UserMessageController::class, 'store'])->name('users.message');

    Route::post('/articles/{article}/like', [ArticleEngagementController::class, 'toggleLike'])->name('articles.like');
    Route::post('/articles/{article}/favorite', [ArticleEngagementController::class, 'toggleFavorite'])->name('articles.favorite');
    Route::post('/articles/{article}/comments', [ArticleCommentController::class, 'store'])->name('articles.comments.store');

    Route::post('/projects/{project}/favorite', [ProjectEngagementController::class, 'toggleFavorite'])->name('projects.favorite');

    Route::post('/cases/{sideHustleCase}/like', [SideHustleCaseEngagementController::class, 'toggleLike'])->name('cases.like');
    Route::post('/cases/{sideHustleCase}/favorite', [SideHustleCaseEngagementController::class, 'toggleFavorite'])->name('cases.favorite');
    Route::post('/projects/{project}/comments', [ProjectCommentController::class, 'store'])->name('projects.comments.store');

    Route::post('/posts/{userPost}/like', [UserPostEngagementController::class, 'toggleLike'])->name('posts.like');
    Route::post('/posts/{userPost}/favorite', [UserPostEngagementController::class, 'toggleFavorite'])->name('posts.favorite');
    Route::post('/posts/{userPost}/comments', [UserPostCommentController::class, 'store'])->name('posts.comments.store');
    Route::post('/posts/{userPost}/boost', [UserPostBoostController::class, 'store'])->name('posts.boost');

    Route::post('/sops/{privateTrafficSop:slug}/comments', [SopCommentController::class, 'store'])->name('sops.comments.store');
    Route::post('/cases/{sideHustleCase}/comments', [SideHustleCaseCommentController::class, 'store'])->name('cases.comments.store');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register/send-code', [RegisterController::class, 'sendCode']);
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// 评论互动路由
Route::middleware('auth')->group(function () {
    Route::post('/articles/comments/{comment}/reply', [ArticleCommentController::class, 'reply'])->name('articles.comments.reply');
    Route::post('/articles/comments/{comment}/like', [ArticleCommentController::class, 'like'])->name('articles.comments.like');
    Route::post('/articles/comments/{comment}/report', [ArticleCommentController::class, 'report'])->name('articles.comments.report');
    Route::post('/projects/comments/{comment}/reply', [ProjectCommentController::class, 'reply'])->name('projects.comments.reply');
    Route::post('/projects/comments/{comment}/like', [ProjectCommentController::class, 'like'])->name('projects.comments.like');
    Route::post('/projects/comments/{comment}/report', [ProjectCommentController::class, 'report'])->name('projects.comments.report');

    Route::post('/posts/comments/{comment}/reply', [UserPostCommentController::class, 'reply'])->name('posts.comments.reply');
    Route::post('/posts/comments/{comment}/like', [UserPostCommentController::class, 'like'])->name('posts.comments.like');
    Route::post('/posts/comments/{comment}/report', [UserPostCommentController::class, 'report'])->name('posts.comments.report');

    Route::post('/sops/comments/{comment}/reply', [SopCommentController::class, 'reply'])->name('sops.comments.reply');
    Route::post('/sops/comments/{comment}/like', [SopCommentController::class, 'like'])->name('sops.comments.like');
    Route::post('/sops/comments/{comment}/report', [SopCommentController::class, 'report'])->name('sops.comments.report');
    Route::post('/cases/comments/{comment}/reply', [SideHustleCaseCommentController::class, 'reply'])->name('cases.comments.reply');
    Route::post('/cases/comments/{comment}/like', [SideHustleCaseCommentController::class, 'like'])->name('cases.comments.like');
    Route::post('/cases/comments/{comment}/report', [SideHustleCaseCommentController::class, 'report'])->name('cases.comments.report');
});

