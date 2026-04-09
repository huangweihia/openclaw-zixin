<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * 后台创建资源时统一生成唯一 slug / code / key，避免人工填写冲突。
 */
final class AdminUniqueCode
{
    /**
     * 通用 URL 段风格（连字符），如 categories.slug、articles.slug。
     *
     * @param  class-string<Model>  $modelClass
     */
    public static function slug(string $source, string $modelClass, string $column = 'slug', ?int $ignoreId = null, string $emptyFallback = 'item'): string
    {
        $base = Str::slug($source);
        if ($base === '') {
            $base = $emptyFallback;
        }
        $base = substr($base, 0, 72);

        return self::ensureUniqueHyphen($modelClass, $column, $base, $ignoreId);
    }

    /**
     * 广告位 code、皮肤 code 等与 slug 规则一致。
     *
     * @param  class-string<Model>  $modelClass
     */
    public static function code(string $name, string $modelClass, string $column = 'code', ?int $ignoreId = null): string
    {
        return self::slug($name, $modelClass, $column, $ignoreId, 'slot');
    }

    /**
     * email_templates.key / email_settings.key：仅 a-z、0-9、下划线。
     *
     * @param  class-string<Model>  $modelClass
     */
    public static function emailKey(string $name, string $modelClass, string $column = 'key', ?int $ignoreId = null): string
    {
        $base = Str::slug($name, '_');
        $base = preg_replace('/[^a-z0-9_]/', '', (string) $base);
        $base = preg_replace('/_+/', '_', $base);
        $base = trim($base, '_');
        if ($base === '') {
            $base = 'key';
        }
        $base = substr($base, 0, 60);

        $code = $base;
        $n = 0;
        while (self::exists($modelClass, $column, $code, $ignoreId)) {
            $n++;
            $code = $base.'_'.Str::lower(Str::random(4));
            if ($n > 80) {
                return 'key_'.Str::lower(Str::random(16));
            }
        }

        return $code;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private static function ensureUniqueHyphen(string $modelClass, string $column, string $base, ?int $ignoreId): string
    {
        $code = $base;
        $n = 0;
        while (self::exists($modelClass, $column, $code, $ignoreId)) {
            $n++;
            $code = $base.'-'.Str::lower(Str::random(4));
            if ($n > 80) {
                return 'item-'.Str::lower(Str::random(12));
            }
        }

        return $code;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private static function exists(string $modelClass, string $column, string $value, ?int $ignoreId): bool
    {
        $q = $modelClass::query()->where($column, $value);
        if ($ignoreId !== null) {
            $q->whereKeyNot($ignoreId);
        }

        return $q->exists();
    }
}
