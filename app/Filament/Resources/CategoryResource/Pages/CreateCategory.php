<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = CategoryResource::class;
}
