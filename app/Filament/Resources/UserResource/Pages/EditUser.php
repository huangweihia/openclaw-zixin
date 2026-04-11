<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = UserResource::class;
}
