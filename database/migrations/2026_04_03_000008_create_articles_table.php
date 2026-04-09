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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable()->comment('分类 ID');
            $table->string('title', 255)->comment('标题');
            $table->string('slug', 255)->unique()->comment('URL 标识');
            $table->string('summary', 500)->nullable()->comment('摘要');
            $table->longText('content')->nullable()->comment('内容（HTML）');
            $table->string('cover_image', 255)->nullable()->comment('封面图 URL');
            $table->unsignedBigInteger('author_id')->nullable()->comment('作者 ID');
            $table->unsignedBigInteger('view_count')->default(0)->comment('浏览数');
            $table->unsignedBigInteger('like_count')->default(0)->comment('点赞数');
            $table->boolean('is_vip')->default(false)->comment('是否 VIP 专属');
            $table->boolean('is_published')->default(false)->comment('是否已发布');
            $table->timestamp('published_at')->nullable()->comment('发布时间');
            $table->string('source_url', 255)->nullable()->comment('来源 URL');
            $table->string('meta_keywords', 255)->nullable()->comment('关键词');
            $table->string('meta_description', 500)->nullable()->comment('描述');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('slug');
            $table->index('category_id');
            $table->index('is_published');
            $table->index('is_vip');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
