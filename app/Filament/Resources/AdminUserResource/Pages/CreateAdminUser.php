<?php

namespace App\Filament\Resources\AdminUserResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\AdminUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminUser extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = AdminUserResource::class;
}
