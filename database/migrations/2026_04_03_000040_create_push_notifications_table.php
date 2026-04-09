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
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->string('title', 255)->comment('推送标题');
            $table->text('content')->comment('推送内容');
            $table->string('action_url', 255)->nullable()->comment('跳转链接');
            $table->json('data')->nullable()->comment('附加数据');
            $table->boolean('is_sent')->default(false)->comment('是否已发送');
            $table->boolean('is_read')->default(false)->comment('是否已读');
            $table->timestamp('sent_at')->nullable()->comment('发送时间');
            $table->timestamp('read_at')->nullable()->comment('阅读时间');
            $table->timestamps();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('is_sent');
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_notifications');
    }
};
