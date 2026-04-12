<?php

namespace App\Filament\Resources\AdminResourceSearchConfigResource\Pages;

use App\Filament\Resources\AdminResourceSearchConfigResource;
use Filament\Resources\Pages\EditRecord;

class EditAdminResourceSearchConfig extends EditRecord
{
    protected static string $resource = AdminResourceSearchConfigResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['search_column_names']) && $data['search_column_names'] === []) {
            $data['search_column_names'] = null;
        }

        return $data;
    }
}
