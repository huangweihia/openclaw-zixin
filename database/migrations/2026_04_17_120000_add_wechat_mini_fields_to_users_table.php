<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('wechat_mini_openid', 64)->nullable()->unique()->after('email')->comment('微信小程序 openid');
            $table->string('wechat_unionid', 64)->nullable()->index()->after('wechat_mini_openid')->comment('微信 unionid（开放平台绑定后可能有）');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['wechat_mini_openid', 'wechat_unionid']);
        });
    }
};
