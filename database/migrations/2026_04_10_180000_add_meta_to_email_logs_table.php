<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_logs')) {
            return;
        }
        Schema::table('email_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('email_logs', 'meta')) {
                $table->json('meta')->nullable()->after('error_message')->comment('扩展信息：订阅ID、计划时刻等');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('email_logs')) {
            return;
        }
        Schema::table('email_logs', function (Blueprint $table) {
            if (Schema::hasColumn('email_logs', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};
