<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('push_notification_id')->nullable()->after('user_id')->comment('后台站内推送源 ID');
            $table->index('push_notification_id');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['push_notification_id']);
            $table->dropColumn('push_notification_id');
        });
    }
};
