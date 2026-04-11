<?php

namespace App\Filament\Resources\PrivateTrafficSopResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\PrivateTrafficSopResource;
use Filament\Resources\Pages\EditRecord;

class EditPrivateTrafficSop extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = PrivateTrafficSopResource::class;
}
