<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = CategoryResource::class;
}
