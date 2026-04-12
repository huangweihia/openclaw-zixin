<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * 个人中心 iframe 内嵌页（oc_embed=1）需在全站链接、分页、重定向中保留参数，否则会再次套上完整 layouts.site（套娃整站）。
 */
final class OcEmbed
{
    public static function queryParams(?Request $request = null): array
    {
        $r = $request ?? request();

        return $r->boolean('oc_embed') ? ['oc_embed' => 1] : [];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public static function mergeQuery(array $query, ?Request $request = null): array
    {
        return array_merge($query, self::queryParams($request));
    }
}
