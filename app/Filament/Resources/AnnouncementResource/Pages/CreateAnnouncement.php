<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\AnnouncementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAnnouncement extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = AnnouncementResource::class;
}
