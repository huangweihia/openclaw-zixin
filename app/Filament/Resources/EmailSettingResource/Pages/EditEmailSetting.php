<?php

namespace App\Filament\Resources\EmailSettingResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\EmailSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditEmailSetting extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = EmailSettingResource::class;
}
