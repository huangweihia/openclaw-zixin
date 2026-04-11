<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\SubscriptionResource;
use Filament\Resources\Pages\EditRecord;

class EditSubscription extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = SubscriptionResource::class;
}
