<?php

namespace App\Filament\Resources\SiteTestimonialResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\SiteTestimonialResource;
use Filament\Resources\Pages\EditRecord;

class EditSiteTestimonial extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = SiteTestimonialResource::class;
}
