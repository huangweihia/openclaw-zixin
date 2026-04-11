<?php

namespace App\Filament\Resources\PersonalityQuizSettingResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\PersonalityQuizSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditPersonalityQuizSetting extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = PersonalityQuizSettingResource::class;
}
