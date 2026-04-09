<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 与 docs/02-数据库表字段详细设计.md 表 25 对齐。
     */
    public function up(): void
    {
        Schema::create('email_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('email', 255)->comment('订阅邮箱');
            $table->json('subscribed_to')->comment('订阅类型 JSON 数组：daily,weekly,notification,promotion');
            $table->boolean('is_unsubscribed')->default(false)->comment('是否已退订');
            $table->timestamp('unsubscribed_at')->nullable()->comment('退订时间');
            $table->string('unsubscribe_token', 100)->nullable()->unique()->comment('退订 token');
            $table->timestamps();

            $table->unique('email');
            $table->index('is_unsubscribed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_subscriptions');
    }
};
