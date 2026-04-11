<?php

namespace App\Filament\Resources\AiToolMonetizationResource\Pages;

use App\Filament\Resources\AiToolMonetizationResource;
use Filament\Resources\Pages\ListRecords;

class ListAiToolMonetizations extends ListRecords
{
    protected static string $resource = AiToolMonetizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
