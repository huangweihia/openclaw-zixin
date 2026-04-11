<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function getNavigationLabel(): string
    {
        return \App\Support\AdminNavRegistry::navigationLabel('dashboard') ?? parent::getNavigationLabel();
    }

    public static function getNavigationSort(): ?int
    {
        return \App\Support\AdminNavRegistry::navigationSort('dashboard') ?? parent::getNavigationSort();
    }

    public static function getNavigationGroup(): ?string
    {
        return \App\Support\AdminNavRegistry::navigationGroupTitle('dashboard') ?? parent::getNavigationGroup();
    }

    public static function shouldRegisterNavigation(): bool
    {
        $u = auth()->user();
        if (! $u instanceof User) {
            return false;
        }

        return $u->allowsAdminMenuKey('dashboard');
    }
}
