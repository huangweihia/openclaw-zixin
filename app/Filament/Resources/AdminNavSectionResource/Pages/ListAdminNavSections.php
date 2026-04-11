<?php

namespace App\Filament\Resources\AdminNavSectionResource\Pages;

use App\Filament\Resources\AdminNavSectionResource;
use Filament\Resources\Pages\ListRecords;

class ListAdminNavSections extends ListRecords
{
    protected static string $resource = AdminNavSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
