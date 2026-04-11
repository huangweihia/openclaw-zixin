<?php

namespace App\Filament\Resources\SiteSettingResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\SiteSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSiteSetting extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = SiteSettingResource::class;
}
