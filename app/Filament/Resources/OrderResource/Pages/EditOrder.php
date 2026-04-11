<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = OrderResource::class;
}
