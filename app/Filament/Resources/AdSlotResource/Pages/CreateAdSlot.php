<?php

namespace App\Filament\Resources\AdSlotResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\AdSlotResource;
use App\Filament\Resources\AdSlotResource\Pages\Concerns\ManagesAdSlotFallbackImage;
use Filament\Resources\Pages\CreateRecord;

class CreateAdSlot extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    use ManagesAdSlotFallbackImage;

    protected static string $resource = AdSlotResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->hydrateAdSlotFallbackImage(array_merge([
            'position' => 'top',
            'type' => 'banner',
            'audience' => 'all',
            'sort' => 0,
            'is_active' => false,
            'show_default_when_empty' => true,
        ], $data));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->mergeAdSlotFallbackImage($data);
    }
}
