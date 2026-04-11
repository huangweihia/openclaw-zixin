<?php

namespace App\Filament\Resources\PersonalityQuestionOptionResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\PersonalityQuestionOptionResource;
use Filament\Resources\Pages\EditRecord;

class EditPersonalityQuestionOption extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = PersonalityQuestionOptionResource::class;
}
