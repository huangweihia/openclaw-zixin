<?php

namespace App\Filament\Resources\PersonalityTypeResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\PersonalityTypeResource;
use Filament\Resources\Pages\EditRecord;

class EditPersonalityType extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = PersonalityTypeResource::class;
}
