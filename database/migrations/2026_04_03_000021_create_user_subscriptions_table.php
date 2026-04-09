<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->comment('用户 ID');
            $table->boolean('email_digest')->default(true)->comment('邮件日报');
            $table->boolean('email_weekly')->default(true)->comment('邮件周报');
            $table->boolean('email_notification')->default(true)->comment('邮件通知');
            $table->boolean('push_digest')->default(true)->comment('推送日报');
            $table->boolean('push_notification')->default(true)->comment('推送通知');
            $table->json('category_subscriptions')->nullable()->comment('订阅分类（JSON 数组）');
            $table->timestamps();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Index
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
