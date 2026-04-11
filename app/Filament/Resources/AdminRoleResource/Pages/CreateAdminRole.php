<?php

namespace App\Filament\Resources\AdminRoleResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\AdminRoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminRole extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = AdminRoleResource::class;
}
