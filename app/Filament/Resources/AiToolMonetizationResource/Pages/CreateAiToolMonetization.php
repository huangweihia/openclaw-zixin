<?php

namespace App\Filament\Resources\AiToolMonetizationResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\AiToolMonetizationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAiToolMonetization extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = AiToolMonetizationResource::class;
}
