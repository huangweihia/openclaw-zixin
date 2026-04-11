<?php

namespace App\Filament\Resources\PersonalityQuestionResource\Pages;

use App\Filament\Resources\PersonalityQuestionResource;
use Filament\Resources\Pages\ListRecords;

class ListPersonalityQuestions extends ListRecords
{
    protected static string $resource = PersonalityQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
