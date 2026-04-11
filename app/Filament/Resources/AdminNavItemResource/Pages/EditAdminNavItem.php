<?php

namespace App\Filament\Resources\AdminNavItemResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\AdminNavItemResource;
use Filament\Resources\Pages\EditRecord;

class EditAdminNavItem extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = AdminNavItemResource::class;
}
