<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE ad_slots MODIFY type VARCHAR(40) NOT NULL DEFAULT 'banner'");
        }
    }

    public function down(): void
    {
        //
    }
};
