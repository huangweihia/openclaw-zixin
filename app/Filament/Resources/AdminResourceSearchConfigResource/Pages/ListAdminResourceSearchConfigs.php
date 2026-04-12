<?php

namespace App\Filament\Resources\AdminResourceSearchConfigResource\Pages;

use App\Filament\Resources\AdminResourceSearchConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdminResourceSearchConfigs extends ListRecords
{
    protected static string $resource = AdminResourceSearchConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
