<?php

namespace App\Filament\Resources\PersonalityQuestionOptionResource\Pages;

use App\Filament\Resources\PersonalityQuestionOptionResource;
use Filament\Resources\Pages\ListRecords;

class ListPersonalityQuestionOptions extends ListRecords
{
    protected static string $resource = PersonalityQuestionOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
