<?php

namespace App\Filament\Resources\AdSlotResource\Pages\Concerns;

use Illuminate\Support\Facades\Storage;

trait ManagesAdSlotFallbackImage
{
    /**
     * 将库里的 default_image_url 拆成上传组件路径与外链输入，避免 FileUpload 与历史外链混用出错。
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function hydrateAdSlotFallbackImage(array $data): array
    {
        $cur = isset($data['default_image_url']) ? (string) $data['default_image_url'] : '';
        $data['default_image_upload'] = null;
        $data['default_image_url_manual'] = '';
        if ($cur !== '') {
            if (preg_match('#(/storage/)([^?]+)#', $cur, $m)) {
                $data['default_image_upload'] = urldecode($m[2]);
            } elseif (str_starts_with($cur, 'http://') || str_starts_with($cur, 'https://')) {
                $data['default_image_url_manual'] = $cur;
            } elseif (str_starts_with($cur, '/storage/')) {
                $data['default_image_upload'] = urldecode(ltrim(substr($cur, strlen('/storage/')), '/'));
            } elseif (! str_contains($cur, '://')) {
                $data['default_image_upload'] = ltrim($cur, '/');
            } else {
                $data['default_image_url_manual'] = $cur;
            }
        }
        unset($data['default_image_url']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mergeAdSlotFallbackImage(array $data): array
    {
        $path = $data['default_image_upload'] ?? null;
        $manual = trim((string) ($data['default_image_url_manual'] ?? ''));
        unset($data['default_image_upload'], $data['default_image_url_manual']);

        if ($manual !== '') {
            $data['default_image_url'] = $manual;

            return $data;
        }
        if (is_string($path) && $path !== '' && ! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://')) {
            $data['default_image_url'] = Storage::disk('public')->url($path);

            return $data;
        }
        $data['default_image_url'] = null;

        return $data;
    }
}
