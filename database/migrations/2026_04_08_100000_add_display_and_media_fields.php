<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('display_position', 20)->default('top')->after('priority')->comment('展示位置：top/bottom/left/right');
            $table->boolean('is_floating')->default(false)->after('display_position')->comment('浮动展示（角标/侧栏）');
            $table->string('cover_image', 500)->nullable()->after('is_floating')->comment('公告配图 URL');
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'enterprise_wechat_id')) {
                $table->boolean('privacy_mode')->default(false)->after('enterprise_wechat_id')->comment('隐私模式：不记录浏览历史');
            } else {
                $table->boolean('privacy_mode')->default(false)->comment('隐私模式：不记录浏览历史');
            }
        });

        DB::table('ad_slots')->where('position', 'like', '%sidebar%')->update(['position' => 'right']);
        DB::table('ad_slots')->whereNotIn('position', ['top', 'bottom', 'left', 'right'])
            ->update(['position' => 'top']);

    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['display_position', 'is_floating', 'cover_image']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('privacy_mode');
        });
    }
};
