<?php

namespace App\Filament\Resources\InvoiceRequestResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\InvoiceRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditInvoiceRequest extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = InvoiceRequestResource::class;
}
