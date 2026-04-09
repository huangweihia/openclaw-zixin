<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Support\AdminUniqueCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        $templates = EmailTemplate::query()->orderBy('key')->get();

        return response()->json(['templates' => $templates]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'plain_text' => ['nullable', 'string'],
            'variables' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['key'] = AdminUniqueCode::emailKey($data['name'], EmailTemplate::class, 'key');
        $template = EmailTemplate::query()->create($data);

        return response()->json([
            'message' => '模板已创建',
            'template' => $template,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $template = EmailTemplate::query()->findOrFail($id);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'subject' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'plain_text' => ['nullable', 'string'],
            'variables' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        unset($data['key']);
        $template->fill($data)->save();

        return response()->json([
            'message' => '模板已更新',
            'template' => $template->fresh(),
        ]);
    }

    public function toggle(int $id): JsonResponse
    {
        $template = EmailTemplate::query()->findOrFail($id);
        $template->forceFill(['is_active' => ! $template->is_active])->save();

        return response()->json([
            'message' => $template->is_active ? '已启用' : '已禁用',
            'template' => $template->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        EmailTemplate::query()->whereKey($id)->delete();

        return response()->json(['message' => '已删除']);
    }

    /**
     * 预览渲染后的主题与 HTML（占位符 {{var}} 未提供时用 [var]）。
     *
     * @param  array<string, string>|null  $variables
     */
    public function preview(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'variables' => ['nullable', 'array'],
        ]);
        $template = EmailTemplate::query()->findOrFail($id);
        $variables = $validated['variables'] ?? [];
        if (! is_array($variables)) {
            $variables = [];
        }
        $variables = array_map(static fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $variables);

        foreach ($template->variables ?? [] as $item) {
            $name = is_array($item) ? ($item['name'] ?? null) : $item;
            if (is_string($name) && $name !== '' && ! array_key_exists($name, $variables)) {
                $variables[$name] = '['.$name.']';
            }
        }

        return response()->json([
            'subject' => $this->interpolateTemplate((string) $template->subject, $variables),
            'html' => $this->interpolateTemplate((string) $template->content, $variables),
            'plain_text' => $template->plain_text
                ? $this->interpolateTemplate((string) $template->plain_text, $variables)
                : null,
        ]);
    }

    /**
     * @param  array<string, string>  $variables
     */
    private function interpolateTemplate(string $text, array $variables): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*([\w.]+)\s*\}\}/',
            static function (array $m) use ($variables): string {
                $key = $m[1];

                return e($variables[$key] ?? '['.$key.']');
            },
            $text
        );
    }
}
