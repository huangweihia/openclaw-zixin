<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class SkinCssVariables
{
    /**
     * 创建/编辑表单 Repeater 的默认行（与 ManagesSkinCssRepeater 逻辑一致）。
     *
     * @return array<int, array{k: string, v: string}>
     */
    public static function defaultCssVarRepeaterRows(): array
    {
        return [
            ['k' => 'primary', 'v' => '#3b82f6'],
            ['k' => 'primary-dark', 'v' => '#2563eb'],
            ['k' => 'primary-light', 'v' => '#60a5fa'],
            ['k' => 'secondary', 'v' => '#0ea5e9'],
            ['k' => 'bg-primary', 'v' => '#f0f9ff'],
            ['k' => 'bg-secondary', 'v' => '#ffffff'],
            ['k' => 'text-primary', 'v' => '#0c4a6e'],
            ['k' => 'text-secondary', 'v' => '#334155'],
            ['k' => 'border-color', 'v' => '#bae6fd'],
        ];
    }

    /**
     * 须由运营填写且非空；gradient-primary 不在此列，保存时由程序根据 primary + secondary 自动生成并写入 JSON。
     */
    public const REQUIRED_KEYS = [
        'primary',
        'secondary',
        'bg-primary',
        'text-primary',
    ];

    /**
     * @param  array<string, mixed>  $vars
     * @return array<string, string>
     */
    public static function normalize(array $vars): array
    {
        $out = [];
        foreach ($vars as $k => $v) {
            if (! is_string($k) || $k === '') {
                continue;
            }
            if ($k === 'gradient-primary' || $k === 'gradient_primary') {
                continue;
            }
            if (is_string($v)) {
                $out[$k] = $v;
            }
        }
        $out['gradient-primary'] = self::synthesizeGradientPrimary($out);

        return $out;
    }

    /**
     * @param  array<string, string>  $vars
     */
    public static function synthesizeGradientPrimary(array $vars): string
    {
        $p = trim((string) ($vars['primary'] ?? ''));
        $s = trim((string) ($vars['secondary'] ?? ''));

        if ($p !== '' && $s !== '') {
            return "linear-gradient(135deg, {$p} 0%, {$s} 100%)";
        }

        if ($p !== '') {
            return "linear-gradient(135deg, {$p} 0%, {$p} 100%)";
        }

        return 'linear-gradient(135deg, #6366f1 0%, #ec4899 100%)';
    }

    /**
     * @param  array<string, mixed>  $vars
     *
     * @throws ValidationException
     */
    public static function assertRequiredPresent(array $vars): void
    {
        $missing = [];
        foreach (self::REQUIRED_KEYS as $key) {
            $v = $vars[$key] ?? null;
            if (! is_string($v) || trim($v) === '') {
                $missing[] = $key;
            }
        }

        if ($missing === []) {
            return;
        }

        $required = implode('、', self::REQUIRED_KEYS);
        $lack = implode('、', $missing);

        throw ValidationException::withMessages([
            'css_variables' => "主题变量须包含必填项且不可删除：{$required}。当前缺少或为空：{$lack}。",
        ]);
    }
}
