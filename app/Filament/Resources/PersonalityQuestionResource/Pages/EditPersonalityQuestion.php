<?php

namespace App\Filament\Resources\PersonalityQuestionResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\PersonalityQuestionResource;
use Filament\Resources\Pages\EditRecord;

class EditPersonalityQuestion extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = PersonalityQuestionResource::class;
}
