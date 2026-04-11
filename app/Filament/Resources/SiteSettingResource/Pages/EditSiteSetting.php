<?php

namespace App\Filament\Resources\SiteSettingResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\SiteSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditSiteSetting extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = SiteSettingResource::class;
}
