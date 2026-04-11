<?php

namespace App\Filament\Resources\AdminNavItemResource\Pages;

use App\Filament\Resources\AdminNavItemResource;
use Filament\Resources\Pages\ListRecords;

class ListAdminNavItems extends ListRecords
{
    protected static string $resource = AdminNavItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
