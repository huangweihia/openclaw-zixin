<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'wechat_openid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('wechat_openid', 64)->nullable()->unique()->after('email')->comment('微信 openid（小程序 jscode2session，与 wechat_mini_openid 同义备份）');
            });
        }

        if (Schema::hasColumn('users', 'wechat_mini_openid') && Schema::hasColumn('users', 'wechat_openid')) {
            DB::statement('UPDATE users SET wechat_openid = wechat_mini_openid WHERE wechat_openid IS NULL AND wechat_mini_openid IS NOT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'wechat_openid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('wechat_openid');
            });
        }
    }
};
