<?php

namespace App\Filament\Resources\PublishAuditResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\PublishAuditResource;
use Filament\Resources\Pages\EditRecord;

class EditPublishAudit extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = PublishAuditResource::class;
}
