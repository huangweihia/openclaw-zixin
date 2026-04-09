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
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->comment('通知标题');
            $table->text('content')->comment('通知内容');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->comment('优先级');
            $table->enum('type', ['system', 'announcement', 'maintenance'])->default('system')->comment('通知类型');
            $table->string('action_url', 255)->nullable()->comment('跳转链接');
            $table->boolean('is_published')->default(false)->comment('是否已发布');
            $table->timestamp('published_at')->nullable()->comment('发布时间');
            $table->timestamp('expires_at')->nullable()->comment('过期时间');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建人 ID');
            $table->timestamps();
            
            // Foreign key
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('priority');
            $table->index('type');
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};
