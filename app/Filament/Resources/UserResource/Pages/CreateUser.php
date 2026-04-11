<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = UserResource::class;
}
