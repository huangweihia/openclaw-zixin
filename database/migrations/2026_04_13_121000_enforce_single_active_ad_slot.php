<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ad_slots')) {
            return;
        }

        $activeIds = DB::table('ad_slots')
            ->where('is_active', true)
            ->orderByDesc('sort')
            ->orderBy('id')
            ->pluck('id');

        if ($activeIds->count() <= 1) {
            return;
        }

        $keepId = (int) $activeIds->first();
        DB::table('ad_slots')
            ->where('id', '!=', $keepId)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        // no-op
    }
};

