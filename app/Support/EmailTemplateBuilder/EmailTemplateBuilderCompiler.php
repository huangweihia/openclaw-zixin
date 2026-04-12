<?php

namespace App\Support\EmailTemplateBuilder;

use Illuminate\Support\Str;

/**
 * 将 Filament Builder 存盘的 layout 编译为带 {{占位符}} 的 HTML，并收集变量名列表。
 */
final class EmailTemplateBuilderCompiler
{
    /**
     * @param  array<int, array<string, mixed>>|null  $layout
     * @return array{html: string, plain: string, variables: array<int, string>}
     */
    public static function compile(?array $layout): array
    {
        if ($layout === null || $layout === []) {
            return ['html' => '', 'plain' => '', 'variables' => []];
        }

        $vars = [];
        $html = '';
        foreach ($layout as $block) {
            $type = $block['type'] ?? null;
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
            $piece = match ($type) {
                'heading' => self::heading($data),
                'paragraph' => self::paragraph($data),
                'divider' => '<hr style="border:none;border-top:1px solid #e2e8f0;margin:16px 0;" />',
                'button' => self::button($data),
                'merge' => self::merge($data, $vars),
                'db_field' => self::dbField($data, $vars),
                default => '',
            };
            $html .= $piece;
        }

        $plain = trim(preg_replace('/\s+/', ' ', strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html))));

        return [
            'html' => $html,
            'plain' => $plain,
            'variables' => array_values(array_unique($vars)),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $layout
     */
    public static function previewHtml(?array $layout): string
    {
        $compiled = self::compile($layout);
        $html = $compiled['html'];
        $samples = EmailTemplatePlaceholderSamples::all();

        return self::applySamples($html, $samples);
    }

    /**
     * @param  array<string, string>  $samples
     */
    public static function applySamples(string $html, array $samples): string
    {
        foreach ($samples as $k => $v) {
            $html = str_replace('{{'.$k.'}}', $v, $html);
        }

        return preg_replace('/\{\{[a-z0-9_]+\}\}/i', '<span style="background:#fef3c7;padding:0 4px;border-radius:4px;">未定义占位符</span>', $html);
    }

    /** @param  array<string, mixed>  $data */
    private static function heading(array $data): string
    {
        $text = e((string) ($data['text'] ?? ''));
        $level = in_array($data['level'] ?? 'h2', ['h1', 'h2', 'h3'], true) ? $data['level'] : 'h2';
        $tag = $level;

        return '<'.$tag.' style="margin:12px 0 8px;font-size:'.($tag === 'h1' ? '22px' : ($tag === 'h2' ? '18px' : '16px')).';color:#0f172a;">'.$text.'</'.$tag.'>';
    }

    /** @param  array<string, mixed>  $data */
    private static function paragraph(array $data): string
    {
        $body = nl2br(e((string) ($data['body'] ?? '')));

        return '<p style="margin:0 0 12px;line-height:1.6;color:#334155;">'.$body.'</p>';
    }

    /** @param  array<string, mixed>  $data */
    private static function button(array $data): string
    {
        $label = e((string) ($data['label'] ?? '按钮'));
        $url = (string) ($data['url'] ?? '#');

        return '<table role="presentation" cellspacing="0" cellpadding="0" style="margin:12px 0;"><tr><td style="border-radius:8px;background:#ea580c;"><a href="'
            .e($url).'" style="display:inline-block;padding:10px 18px;color:#fff;text-decoration:none;font-weight:600;">'.$label.'</a></td></tr></table>';
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $vars
     */
    private static function merge(array $data, array &$vars): string
    {
        $key = trim((string) ($data['key'] ?? ''));
        if ($key === '' || ! preg_match('/^[a-z][a-z0-9_]*$/', $key)) {
            return '';
        }
        $vars[] = $key;

        return '{{'.$key.'}}';
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $vars
     */
    private static function dbField(array $data, array &$vars): string
    {
        $entity = (string) ($data['entity'] ?? '');
        $field = (string) ($data['field'] ?? '');
        if ($entity === '' || $field === '') {
            return '';
        }
        $token = 'db_'.$entity.'_'.$field;
        if (! preg_match('/^[a-z][a-z0-9_]*$/', $token)) {
            return '';
        }
        $vars[] = $token;

        return '<span style="display:inline-block;margin:2px 0;padding:2px 6px;background:#e0f2fe;border-radius:4px;font-size:12px;color:#0369a1;">{{'.$token.'}}</span>';
    }
}
