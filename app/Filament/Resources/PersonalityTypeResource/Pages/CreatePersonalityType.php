<?php

namespace App\Filament\Resources\PersonalityTypeResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\PersonalityTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePersonalityType extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = PersonalityTypeResource::class;
}
