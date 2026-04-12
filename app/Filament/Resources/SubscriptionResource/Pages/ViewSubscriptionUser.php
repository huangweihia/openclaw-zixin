<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSubscriptionUser extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    public function getTitle(): string
    {
        $r = $this->getRecord();

        return '会员订阅：'.($r->name ?: $r->email ?: ('#'.$r->getKey()));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
