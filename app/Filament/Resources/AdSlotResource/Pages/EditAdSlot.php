<?php

namespace App\Filament\Resources\AdSlotResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\AdSlotResource;
use App\Filament\Resources\AdSlotResource\Pages\Concerns\ManagesAdSlotFallbackImage;
use Filament\Resources\Pages\EditRecord;

class EditAdSlot extends EditRecord
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
        return $this->hydrateAdSlotFallbackImage($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->mergeAdSlotFallbackImage($data);
    }
}
