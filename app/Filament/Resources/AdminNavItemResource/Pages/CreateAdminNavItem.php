<?php

namespace App\Filament\Resources\AdminNavItemResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\AdminNavItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminNavItem extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = AdminNavItemResource::class;
}
