<?php

namespace App\Filament\Resources\UserPostResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\UserPostResource;
use Filament\Resources\Pages\EditRecord;

class EditUserPost extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = UserPostResource::class;
}
