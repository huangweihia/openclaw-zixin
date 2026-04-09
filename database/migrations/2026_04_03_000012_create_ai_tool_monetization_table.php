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
        Schema::create('ai_tool_monetization', function (Blueprint $table) {
            $table->id();
            $table->string('tool_name', 255)->comment('工具名称');
            $table->string('slug', 255)->unique()->comment('URL 标识');
            $table->string('tool_url', 255)->nullable()->comment('工具链接');
            $table->enum('category', ['image', 'text', 'video', 'audio', 'code'])->default('text')->comment('工具分类');
            $table->boolean('available_in_china')->default(false)->comment('国内是否可用');
            $table->enum('pricing_model', ['free', 'subscription', 'pay_as_you_go'])->default('free')->comment('定价模式');
            $table->longText('content')->nullable()->comment('变现指南（HTML）');
            $table->json('monetization_scenes')->nullable()->comment('变现场景（JSON 数组）');
            $table->json('prompt_templates')->nullable()->comment('提示词模板（JSON 数组）');
            $table->json('pricing_reference')->nullable()->comment('定价参考（JSON 数组）');
            $table->json('channels')->nullable()->comment('接单渠道（JSON 数组）');
            $table->json('delivery_standards')->nullable()->comment('交付标准（JSON 数组）');
            $table->enum('visibility', ['public', 'vip'])->default('public')->comment('可见性');
            $table->unsignedInteger('view_count')->default(0)->comment('浏览数');
            $table->unsignedInteger('like_count')->default(0)->comment('点赞数');
            $table->unsignedInteger('favorite_count')->default(0)->comment('收藏数');
            $table->timestamps();
            
            // Indexes
            $table->index('slug');
            $table->index('category');
            $table->index('visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_tool_monetization');
    }
};
