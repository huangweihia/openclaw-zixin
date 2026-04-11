<?php

namespace App\Observers;

use App\Models\AdminNavSection;
use App\Support\AdminNavRegistry;

class AdminNavSectionObserver
{
    public function saved(AdminNavSection $adminNavSection): void
    {
        AdminNavRegistry::forgetCache();
    }

    public function deleted(AdminNavSection $adminNavSection): void
    {
        AdminNavRegistry::forgetCache();
    }
}
