<?php

namespace App\Filament\Resources\PersonalityDimensionResource\Pages;

use App\Filament\Resources\PersonalityDimensionResource;
use Filament\Resources\Pages\ListRecords;

class ListPersonalityDimensions extends ListRecords
{
    protected static string $resource = PersonalityDimensionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
