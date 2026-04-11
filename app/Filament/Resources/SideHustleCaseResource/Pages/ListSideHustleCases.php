<?php

namespace App\Filament\Resources\SideHustleCaseResource\Pages;

use App\Filament\Resources\SideHustleCaseResource;
use Filament\Resources\Pages\ListRecords;

class ListSideHustleCases extends ListRecords
{
    protected static string $resource = SideHustleCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
