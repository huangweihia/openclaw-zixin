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
        Schema::create('user_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('发布用户 ID');
            $table->enum('type', ['case', 'tool', 'experience', 'resource', 'question'])->comment('发布类型');
            $table->string('title', 255)->comment('标题');
            $table->text('content')->comment('内容（Markdown）');
            $table->string('category', 100)->nullable()->comment('分类');
            $table->json('tags')->nullable()->comment('标签（JSON 数组）');
            $table->string('cover_image', 255)->nullable()->comment('封面图 URL');
            $table->json('attachments')->nullable()->comment('附件（JSON 数组）');
            $table->enum('visibility', ['public', 'vip', 'private'])->default('public')->comment('可见性');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('审核状态');
            $table->text('audit_note')->nullable()->comment('审核备注');
            $table->unsignedBigInteger('audited_by')->nullable()->comment('审核人 ID');
            $table->timestamp('audited_at')->nullable()->comment('审核时间');
            $table->unsignedInteger('view_count')->default(0)->comment('浏览数');
            $table->unsignedInteger('like_count')->default(0)->comment('点赞数');
            $table->unsignedInteger('comment_count')->default(0)->comment('评论数');
            $table->unsignedInteger('favorite_count')->default(0)->comment('收藏数');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('audited_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('user_id');
            $table->index('type');
            $table->index('status');
            $table->index('visibility');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_posts');
    }
};
