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
        Schema::create('premium_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->comment('资源标题');
            $table->string('slug', 255)->unique()->comment('URL 标识');
            $table->string('summary', 500)->nullable()->comment('资源描述');
            $table->enum('type', ['pdf', 'video', 'cloud_drive', 'ebook'])->default('pdf')->comment('资源类型');
            $table->longText('content')->nullable()->comment('资源详情（HTML）');
            $table->string('download_link', 500)->nullable()->comment('下载链接');
            $table->string('extract_code', 20)->nullable()->comment('提取码');
            $table->decimal('original_price', 10, 2)->nullable()->comment('原价');
            $table->json('tags')->nullable()->comment('标签（JSON 数组）');
            $table->enum('visibility', ['public', 'vip'])->default('vip')->comment('可见性');
            $table->unsignedInteger('download_count')->default(0)->comment('下载次数');
            $table->unsignedInteger('view_count')->default(0)->comment('浏览数');
            $table->unsignedInteger('like_count')->default(0)->comment('点赞数');
            $table->unsignedInteger('favorite_count')->default(0)->comment('收藏数');
            $table->timestamps();
            
            // Indexes
            $table->index('slug');
            $table->index('type');
            $table->index('visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_resources');
    }
};
