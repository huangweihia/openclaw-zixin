<?php

namespace App\Filament\Resources\EmailSubscriptionResource\Pages;

use App\Filament\Resources\EmailSubscriptionResource;
use Filament\Resources\Pages\ListRecords;

class ListEmailSubscriptions extends ListRecords
{
    protected static string $resource = EmailSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
