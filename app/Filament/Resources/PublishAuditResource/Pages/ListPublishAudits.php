<?php

namespace App\Filament\Resources\PublishAuditResource\Pages;

use App\Filament\Resources\PublishAuditResource;
use Filament\Resources\Pages\ListRecords;

class ListPublishAudits extends ListRecords
{
    protected static string $resource = PublishAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
