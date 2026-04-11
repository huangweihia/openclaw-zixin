<?php

namespace App\Filament\Resources\SvipCustomSubscriptionResource\Pages;

use App\Filament\Resources\SvipCustomSubscriptionResource;
use Filament\Resources\Pages\ListRecords;

class ListSvipCustomSubscriptions extends ListRecords
{
    protected static string $resource = SvipCustomSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
