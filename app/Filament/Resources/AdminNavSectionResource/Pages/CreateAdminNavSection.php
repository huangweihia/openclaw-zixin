<?php

namespace App\Filament\Resources\AdminNavSectionResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\AdminNavSectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminNavSection extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = AdminNavSectionResource::class;
}
