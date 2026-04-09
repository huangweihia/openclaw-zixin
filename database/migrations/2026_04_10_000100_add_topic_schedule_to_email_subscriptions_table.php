<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('email_subscriptions', 'topic_schedule')) {
                $table->json('topic_schedule')->nullable()->after('subscribed_to')->comment('每个订阅主题的发送时段，格式 {"daily":"09:00"}');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('email_subscriptions', 'topic_schedule')) {
                $table->dropColumn('topic_schedule');
            }
        });
    }
};
