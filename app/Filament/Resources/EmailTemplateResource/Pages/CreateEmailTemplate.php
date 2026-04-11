<?php

namespace App\Filament\Resources\EmailTemplateResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\EmailTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailTemplate extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = EmailTemplateResource::class;
}
