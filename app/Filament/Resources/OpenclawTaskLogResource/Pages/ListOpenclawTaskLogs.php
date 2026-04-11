<?php

namespace App\Filament\Resources\OpenclawTaskLogResource\Pages;

use App\Filament\Resources\OpenclawTaskLogResource;
use Filament\Resources\Pages\ListRecords;

class ListOpenclawTaskLogs extends ListRecords
{
    protected static string $resource = OpenclawTaskLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
