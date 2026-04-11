<?php

namespace App\Filament\Resources\PersonalityQuestionResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\PersonalityQuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePersonalityQuestion extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = PersonalityQuestionResource::class;
}
