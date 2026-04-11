<?php

namespace App\Filament\Resources\SideHustleCaseResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\SideHustleCaseResource;
use Filament\Resources\Pages\EditRecord;

class EditSideHustleCase extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = SideHustleCaseResource::class;
}
