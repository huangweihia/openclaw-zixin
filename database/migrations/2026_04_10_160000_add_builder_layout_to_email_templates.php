<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_templates')) {
            return;
        }
        Schema::table('email_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('email_templates', 'builder_layout')) {
                $table->json('builder_layout')->nullable()->after('content')->comment('Filament Builder 拖拽布局 JSON');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('email_templates')) {
            return;
        }
        Schema::table('email_templates', function (Blueprint $table) {
            if (Schema::hasColumn('email_templates', 'builder_layout')) {
                $table->dropColumn('builder_layout');
            }
        });
    }
};
