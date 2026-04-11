<?php

namespace App\Filament\Resources\UserSkinResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\UserSkinResource;
use Filament\Resources\Pages\EditRecord;

class EditUserSkin extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = UserSkinResource::class;
}
