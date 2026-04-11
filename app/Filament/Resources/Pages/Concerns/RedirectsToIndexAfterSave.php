<?php

namespace App\Filament\Resources\Pages\Concerns;

/**
 * 保存/创建成功后跳转到资源列表页（对齐常见后台习惯）。
 */
trait RedirectsToIndexAfterSave
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
