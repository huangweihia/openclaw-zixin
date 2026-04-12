<?php

namespace App\Filament\Resources\AdminResourceSearchConfigResource\Pages;

use App\Filament\Resources\AdminResourceSearchConfigResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminResourceSearchConfig extends CreateRecord
{
    protected static string $resource = AdminResourceSearchConfigResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['search_column_names']) && $data['search_column_names'] === []) {
            $data['search_column_names'] = null;
        }

        return $data;
    }
}
