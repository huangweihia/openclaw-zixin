<?php

namespace App\Filament\Resources\ContentBoostResource\Pages;

use App\Filament\Resources\ContentBoostResource;
use Filament\Resources\Pages\ListRecords;

class ListContentBoosts extends ListRecords
{
    protected static string $resource = ContentBoostResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
