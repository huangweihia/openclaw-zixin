<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('system_notifications', 'audience')) {
                $table->string('audience', 30)->default('all')->after('type')->comment('可见/派发人群：all/user/vip/svip/admin/member/non_member');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('system_notifications', 'audience')) {
                $table->dropColumn('audience');
            }
        });
    }
};
