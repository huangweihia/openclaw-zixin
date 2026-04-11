<?php

namespace App\Filament\Resources\ViewHistoryResource\Pages;

use App\Filament\Resources\ViewHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListViewHistories extends ListRecords
{
    protected static string $resource = ViewHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
