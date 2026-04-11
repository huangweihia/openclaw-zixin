<?php

namespace App\Filament\Resources\MemberPremiumResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\MemberPremiumResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMemberPremium extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = MemberPremiumResource::class;
}
