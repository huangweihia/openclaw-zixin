<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_slots', function (Blueprint $table) {
            if (! Schema::hasColumn('ad_slots', 'audience')) {
                $table->string('audience', 30)->default('all')->after('type')->comment('可见人群：all/guest/user/vip/svip/admin/member/non_member');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ad_slots', function (Blueprint $table) {
            if (Schema::hasColumn('ad_slots', 'audience')) {
                $table->dropColumn('audience');
            }
        });
    }
};
