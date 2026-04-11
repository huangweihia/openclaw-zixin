<?php

namespace App\Filament\Resources\UserSkinResource\Pages;

use App\Filament\Resources\UserSkinResource;
use Filament\Resources\Pages\ListRecords;

class ListUserSkins extends ListRecords
{
    protected static string $resource = UserSkinResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
