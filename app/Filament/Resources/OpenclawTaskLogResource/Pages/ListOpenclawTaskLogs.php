<?php

namespace App\Filament\Resources\OpenclawTaskLogResource\Pages;

use App\Filament\Resources\OpenclawTaskLogResource;
use App\Filament\Resources\OpenclawTaskLogResource\Widgets\OpenclawTaskLogStatsWidget;
use Filament\Resources\Pages\ListRecords;

class ListOpenclawTaskLogs extends ListRecords
{
    protected static string $resource = OpenclawTaskLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OpenclawTaskLogStatsWidget::class,
        ];
    }
}
