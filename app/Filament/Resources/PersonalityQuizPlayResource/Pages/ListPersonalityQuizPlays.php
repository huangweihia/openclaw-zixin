<?php

namespace App\Filament\Resources\PersonalityQuizPlayResource\Pages;

use App\Filament\Resources\PersonalityQuizPlayResource;
use Filament\Resources\Pages\ListRecords;

class ListPersonalityQuizPlays extends ListRecords
{
    protected static string $resource = PersonalityQuizPlayResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
