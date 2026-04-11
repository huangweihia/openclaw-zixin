<?php

namespace App\Observers;

use App\Models\AdminNavItem;
use App\Support\AdminNavRegistry;

class AdminNavItemObserver
{
    public function saved(AdminNavItem $adminNavItem): void
    {
        AdminNavRegistry::forgetCache();
    }

    public function deleted(AdminNavItem $adminNavItem): void
    {
        AdminNavRegistry::forgetCache();
    }
}
