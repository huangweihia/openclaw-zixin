<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailSetting;
use App\Support\EmailLogWriter;
use App\Support\AdminUniqueCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailSettingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'settings' => EmailSetting::query()->orderBy('key')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
        $data['key'] = AdminUniqueCode::emailKey($data['name'], EmailSetting::class, 'key');
        $row = EmailSetting::query()->create($data);

        return response()->json(['message' => '已创建', 'setting' => $row], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = EmailSetting::query()->findOrFail($id);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'value' => ['sometimes', 'string'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
        unset($data['key']);
        $row->fill($data)->save();

        return response()->json(['message' => '已更新', 'setting' => $row->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        EmailSetting::query()->whereKey($id)->delete();

        return response()->json(['message' => '已删除']);
    }

    /**
     * 使用当前 .env / config 中的 mail 配置发送测试信（与 RegisterController 等发信共用同一套 Mail）。
     */
    public function testSend(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
        ]);

        $appName = config('app.name', 'OpenClaw');
        $subject = $data['subject'] ?: '['.$appName.'] 邮件连通性测试';
        $body = '<p>这是一封来自 <strong>'.e($appName).'</strong> 的后台邮件测试。</p>'
            .'<p>发送时间：'.e(now()->toDateTimeString()).'</p>'
            .'<p>若收到说明当前 <code>MAIL_*</code> 配置可用；键值表 <code>email_settings</code> 可与业务代码组合使用。</p>';

        try {
            Mail::html($body, function ($message) use ($data, $subject) {
                $message->to($data['to'])->subject($subject);
            });
            EmailLogWriter::sent(null, (string) $data['to'], $subject, 'admin_mail_test');
        } catch (Throwable $e) {
            EmailLogWriter::failed(null, (string) $data['to'], $subject, $e->getMessage(), 'admin_mail_test');
            return response()->json([
                'message' => '发送失败：'.$e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => '已提交发送，请查收收件箱或垃圾箱（部分邮箱有延迟）。',
        ]);
    }
}
