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
        Schema::create('publish_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publish_id')->comment('发布 ID');
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->unsignedBigInteger('auditor_id')->nullable()->comment('审核人 ID');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('审核状态');
            $table->text('reject_reason')->nullable()->comment('拒绝原因');
            $table->text('suggest')->nullable()->comment('修改建议');
            $table->integer('priority')->default(0)->comment('审核优先级');
            $table->timestamp('audited_at')->nullable()->comment('审核时间');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('publish_id')->references('id')->on('user_posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('auditor_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('publish_id');
            $table->index('user_id');
            $table->index('auditor_id');
            $table->index('status');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publish_audits');
    }
};
