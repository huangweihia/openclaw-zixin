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
        Schema::create('private_traffic_sops', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->comment('SOP 标题');
            $table->string('slug', 255)->unique()->comment('URL 标识');
            $table->string('summary', 500)->nullable()->comment('SOP 摘要');
            $table->longText('content')->nullable()->comment('SOP 内容（Markdown）');
            $table->enum('platform', ['wechat', 'xiaohongshu', 'douyin', 'other'])->default('wechat')->comment('平台');
            $table->enum('type', ['traffic', 'operation', 'conversion', 'retention'])->default('operation')->comment('SOP 类型');
            $table->json('checklist')->nullable()->comment('检查清单（JSON 数组）');
            $table->json('templates')->nullable()->comment('话术模板（JSON 数组）');
            $table->json('metrics')->nullable()->comment('关键指标（JSON 数组）');
            $table->json('tools')->nullable()->comment('推荐工具（JSON 数组）');
            $table->enum('visibility', ['public', 'vip'])->default('vip')->comment('可见性');
            $table->unsignedInteger('view_count')->default(0)->comment('浏览数');
            $table->unsignedInteger('like_count')->default(0)->comment('点赞数');
            $table->unsignedInteger('favorite_count')->default(0)->comment('收藏数');
            $table->timestamps();
            
            // Indexes
            $table->index('slug');
            $table->index('platform');
            $table->index('visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('private_traffic_sops');
    }
};
