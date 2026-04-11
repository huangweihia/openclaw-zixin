<?php

namespace App\Filament\Resources\MemberPremiumResource\Pages;

use App\Filament\Resources\MemberPremiumResource;
use Filament\Resources\Pages\ListRecords;

class ListMemberPremiums extends ListRecords
{
    protected static string $resource = MemberPremiumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
