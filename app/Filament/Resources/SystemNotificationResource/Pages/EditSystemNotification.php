<?php

namespace App\Filament\Resources\SystemNotificationResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\SystemNotificationResource;
use Filament\Resources\Pages\EditRecord;

class EditSystemNotification extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = SystemNotificationResource::class;
}
