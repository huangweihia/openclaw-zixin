<?php

namespace App\Filament\Resources\SiteTestimonialResource\Pages;

use App\Filament\Resources\SiteTestimonialResource;
use Filament\Resources\Pages\ListRecords;

class ListSiteTestimonials extends ListRecords
{
    protected static string $resource = SiteTestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
