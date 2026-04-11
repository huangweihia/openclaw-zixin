<?php

namespace App\Filament\Resources\PushNotificationResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\PushNotificationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePushNotification extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = PushNotificationResource::class;
}
