<?php

namespace App\Filament\Resources\EmailTemplateResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\EmailTemplateResource;
use Filament\Resources\Pages\EditRecord;

class EditEmailTemplate extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = EmailTemplateResource::class;
}
