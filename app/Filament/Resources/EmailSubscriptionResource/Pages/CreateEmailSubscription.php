<?php

namespace App\Filament\Resources\EmailSubscriptionResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\EmailSubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailSubscription extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = EmailSubscriptionResource::class;
}
