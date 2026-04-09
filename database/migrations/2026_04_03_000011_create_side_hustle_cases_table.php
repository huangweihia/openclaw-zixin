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
        Schema::create('side_hustle_cases', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->comment('案例标题');
            $table->string('slug', 255)->unique()->comment('URL 标识');
            $table->string('summary', 500)->nullable()->comment('案例摘要');
            $table->longText('content')->nullable()->comment('案例内容（Markdown）');
            $table->enum('category', ['online', 'offline', 'hybrid'])->default('online')->comment('案例类型');
            $table->enum('type', ['ecommerce', 'content', 'service', 'other'])->default('other')->comment('副业类型');
            $table->string('startup_cost', 50)->default('0')->comment('启动成本');
            $table->string('time_investment', 100)->comment('时间投入');
            $table->decimal('estimated_income', 10, 2)->default(0)->comment('预估月收入');
            $table->decimal('actual_income', 10, 2)->nullable()->comment('实际月收入（已验证）');
            $table->json('income_screenshots')->nullable()->comment('收入截图（JSON 数组）');
            $table->longText('steps')->nullable()->comment('操作步骤（Markdown）');
            $table->json('tools')->nullable()->comment('所需工具（JSON 数组）');
            $table->json('pitfalls')->nullable()->comment('常见坑（JSON 数组）');
            $table->boolean('willing_to_consult')->default(false)->comment('是否愿意接受咨询');
            $table->string('contact_info', 255)->nullable()->comment('联系方式');
            $table->enum('visibility', ['public', 'vip', 'private'])->default('vip')->comment('可见性');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('审核状态');
            $table->text('audit_note')->nullable()->comment('审核备注');
            $table->unsignedBigInteger('audited_by')->nullable()->comment('审核人 ID');
            $table->timestamp('audited_at')->nullable()->comment('审核时间');
            $table->unsignedInteger('view_count')->default(0)->comment('浏览数');
            $table->unsignedInteger('like_count')->default(0)->comment('点赞数');
            $table->unsignedInteger('comment_count')->default(0)->comment('评论数');
            $table->unsignedInteger('favorite_count')->default(0)->comment('收藏数');
            $table->unsignedBigInteger('user_id')->nullable()->comment('发布用户 ID');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('audited_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('slug');
            $table->index('category');
            $table->index('status');
            $table->index('visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('side_hustle_cases');
    }
};
