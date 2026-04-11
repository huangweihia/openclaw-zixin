<?php

namespace App\Filament\Resources\PersonalityTypeResource\Pages;

use App\Filament\Resources\PersonalityTypeResource;
use Filament\Resources\Pages\ListRecords;

class ListPersonalityTypes extends ListRecords
{
    protected static string $resource = PersonalityTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
