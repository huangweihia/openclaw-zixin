<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = ProjectResource::class;
}
