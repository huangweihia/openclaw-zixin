<?php

namespace App\Filament\Resources\EmailSubscriptionTopicTemplateResource\Pages;

use App\Filament\Resources\EmailSubscriptionTopicTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailSubscriptionTopicTemplates extends ListRecords
{
    protected static string $resource = EmailSubscriptionTopicTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
