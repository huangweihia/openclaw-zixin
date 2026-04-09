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
        Schema::create('comment_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('举报人 ID');
            $table->unsignedBigInteger('comment_id')->comment('评论 ID');
            $table->enum('reason', ['spam', 'abuse', 'harassment', 'other'])->comment('举报原因');
            $table->text('description')->nullable()->comment('举报说明');
            $table->enum('status', ['pending', 'processed', 'rejected'])->default('pending')->comment('处理状态');
            $table->text('admin_note')->nullable()->comment('管理员备注');
            $table->unsignedBigInteger('processed_by')->nullable()->comment('处理人 ID');
            $table->timestamp('processed_at')->nullable()->comment('处理时间');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('user_id');
            $table->index('comment_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_reports');
    }
};
