<?php

use App\Http\Controllers\Api\PublicEmailSubscriptionController;
use App\Http\Controllers\Api\SkinController;
use App\Http\Controllers\Api\SvipContentController;
use App\Http\Controllers\Api\SvipSubscriptionController;
use App\Http\Controllers\Api\OpenClawDataController;
use App\Http\Controllers\Api\OpenClawTaskLogController;
use App\Http\Controllers\Api\PersonalityQuizAdminController;
use App\Http\Controllers\Api\PersonalityQuizController;
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
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/email-subscriptions', [PublicEmailSubscriptionController::class, 'store']);
Route::get('/email-unsubscribe/{token}', [PublicEmailSubscriptionController::class, 'unsubscribe'])
    ->where('token', '[A-Za-z0-9]+');

// 皮肤 API
Route::prefix('skins')->group(function () {
    Route::get('/', [SkinController::class, 'index']);           // GET /api/skins - 获取所有皮肤
    Route::get('/current', [SkinController::class, 'show']);     // GET /api/skins/current - 获取当前用户皮肤
    Route::put('/current', [SkinController::class, 'update'])->middleware('auth:sanctum'); // PUT /api/skins/current - 更新用户皮肤
    Route::delete('/current', [SkinController::class, 'destroy'])->middleware('auth:sanctum'); // DELETE /api/skins/current - 重置为默认
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

// 趣味人格测试（公开）
Route::get('/personality-quiz', [PersonalityQuizController::class, 'show']);
Route::post('/personality-quiz/submit', [PersonalityQuizController::class, 'submit'])
    ->middleware('throttle:40,1');

// 趣味人格测试管理（无登录，凭 PERSONALITY_QUIZ_ADMIN_TOKEN）
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
