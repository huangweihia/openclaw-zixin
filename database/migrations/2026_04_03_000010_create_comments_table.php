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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->string('commentable_type', 255)->comment('评论类型（多态）');
            $table->unsignedBigInteger('commentable_id')->comment('评论对象 ID');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父评论 ID');
            $table->text('content')->comment('评论内容');
            $table->boolean('is_hidden')->default(false)->comment('是否隐藏');
            $table->unsignedInteger('like_count')->default(0)->comment('点赞数');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index(['commentable_type', 'commentable_id']);
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
