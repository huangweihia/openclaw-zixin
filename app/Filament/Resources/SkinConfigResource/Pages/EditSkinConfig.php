<?php

namespace App\Filament\Resources\SkinConfigResource\Pages;

use App\Filament\Resources\SkinConfigResource;
use App\Filament\Resources\SkinConfigResource\Pages\Concerns\ManagesSkinCssRepeater;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSkinConfig extends EditRecord
{
    use ManagesSkinCssRepeater;

    protected static string $resource = SkinConfigResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->hydrateCssRepeater($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->dehydrateCssRepeater($data);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('已保存')
            ->body('皮肤配置已更新。');
    }
}
