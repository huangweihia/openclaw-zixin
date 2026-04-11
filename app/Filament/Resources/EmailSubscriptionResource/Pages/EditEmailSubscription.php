<?php

namespace App\Filament\Resources\EmailSubscriptionResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\EmailSubscriptionResource;
use Filament\Resources\Pages\EditRecord;

class EditEmailSubscription extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = EmailSubscriptionResource::class;
}
