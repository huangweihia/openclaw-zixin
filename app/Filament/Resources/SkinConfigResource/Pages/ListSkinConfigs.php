<?php

namespace App\Filament\Resources\SkinConfigResource\Pages;

use App\Filament\Resources\SkinConfigResource;
use Filament\Resources\Pages\ListRecords;

class ListSkinConfigs extends ListRecords
{
    protected static string $resource = SkinConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
