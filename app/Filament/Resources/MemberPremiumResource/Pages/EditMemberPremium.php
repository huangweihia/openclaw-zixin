<?php

namespace App\Filament\Resources\MemberPremiumResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\MemberPremiumResource;
use Filament\Resources\Pages\EditRecord;

class EditMemberPremium extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = MemberPremiumResource::class;
}
