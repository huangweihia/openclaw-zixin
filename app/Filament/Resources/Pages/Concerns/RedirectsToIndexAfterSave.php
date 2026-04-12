<?php

namespace App\Filament\Resources\Pages\Concerns;

/**
 * 保存/创建成功后留在当前记录的编辑页，仅用 Filament 通知提示结果。
 */
trait RedirectsToIndexAfterSave
{
    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        if (method_exists($this, 'getRecord')) {
            $record = $this->getRecord();
            if ($record !== null) {
                return $resource::getUrl('edit', ['record' => $record]);
            }
        }

        return $resource::getUrl('index');
    }
}
