<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\AnnouncementResource;
use Filament\Resources\Pages\EditRecord;

class EditAnnouncement extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = AnnouncementResource::class;
}
