<?php

namespace App\Filament\Resources\RefundRequestResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\RefundRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditRefundRequest extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = RefundRequestResource::class;
}
