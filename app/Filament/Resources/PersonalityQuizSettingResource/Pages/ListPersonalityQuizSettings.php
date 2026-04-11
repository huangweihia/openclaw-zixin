<?php

namespace App\Filament\Resources\PersonalityQuizSettingResource\Pages;

use App\Filament\Resources\PersonalityQuizSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListPersonalityQuizSettings extends ListRecords
{
    protected static string $resource = PersonalityQuizSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
