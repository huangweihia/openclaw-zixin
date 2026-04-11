<?php

namespace App\Filament\Resources\SkinConfigResource\Pages;

use App\Filament\Resources\SkinConfigResource;
use App\Filament\Resources\SkinConfigResource\Pages\Concerns\ManagesSkinCssRepeater;
use App\Models\SkinConfig;
use App\Support\AdminUniqueCode;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSkinConfig extends CreateRecord
{
    use ManagesSkinCssRepeater;

    protected static string $resource = SkinConfigResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->hydrateCssRepeater(array_merge([
            'type' => 'free',
            'sort' => 0,
            'is_active' => true,
            'is_private' => false,
        ], $data));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->dehydrateCssRepeater($data);
        $name = (string) ($data['name'] ?? '');
        $data['code'] = AdminUniqueCode::slug($name !== '' ? $name : 'theme', SkinConfig::class, 'code', null, 'theme');

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('已创建')
            ->body('皮肤配置已保存。');
    }
}
