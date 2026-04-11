<?php

namespace App\Filament\Resources\SvipSubscriptionResource\Pages;

use App\Filament\Resources\SvipSubscriptionResource;
use Filament\Resources\Pages\ListRecords;

class ListSvipSubscriptions extends ListRecords
{
    protected static string $resource = SvipSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
