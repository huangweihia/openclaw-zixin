<?php

namespace App\Filament\Resources\AiToolMonetizationResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\AiToolMonetizationResource;
use Filament\Resources\Pages\EditRecord;

class EditAiToolMonetization extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = AiToolMonetizationResource::class;
}
