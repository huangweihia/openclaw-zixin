<?php

namespace App\Filament\Resources\PrivateTrafficSopResource\Pages;

use App\Filament\Resources\PrivateTrafficSopResource;
use Filament\Resources\Pages\ListRecords;

class ListPrivateTrafficSops extends ListRecords
{
    protected static string $resource = PrivateTrafficSopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
