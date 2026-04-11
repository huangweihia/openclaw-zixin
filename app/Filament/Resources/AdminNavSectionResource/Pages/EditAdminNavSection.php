<?php

namespace App\Filament\Resources\AdminNavSectionResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\AdminNavSectionResource;
use Filament\Resources\Pages\EditRecord;

class EditAdminNavSection extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = AdminNavSectionResource::class;
}
