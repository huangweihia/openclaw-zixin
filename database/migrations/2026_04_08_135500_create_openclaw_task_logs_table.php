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
        Schema::create('openclaw_task_logs', function (Blueprint $table) {
            $table->id();
            
            // 任务基本信息
            $table->string('task_name')->comment('任务名称');
            $table->string('task_id')->nullable()->comment('OpenClaw 任务 ID');
            $table->string('task_type')->comment('任务类型：ai_content/svip_subscription/svip_content/daily_news');
            
            // 执行状态
            $table->enum('status', ['success', 'error', 'timeout', 'skipped'])->default('success')->comment('执行状态');
            $table->integer('duration_ms')->nullable()->comment('执行耗时（毫秒）');
            
            // 数据统计
            $table->json('data_summary')->nullable()->comment('数据统计（各类型数量）');
            $table->integer('total_items')->default(0)->comment('总数据量');
            $table->integer('success_count')->default(0)->comment('成功数量');
            $table->integer('failed_count')->default(0)->comment('失败数量');
            $table->integer('skipped_count')->default(0)->comment('跳过数量');
            
            // 推送信息
            $table->string('api_endpoint')->nullable()->comment('推送接口地址');
            $table->enum('push_status', ['success', 'failed', 'not_attempted'])->default('not_attempted')->comment('推送状态');
            $table->text('push_response')->nullable()->comment('推送响应');
            
            // 错误信息
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->text('error_details')->nullable()->comment('详细错误（堆栈/响应）');
            
            // 时间戳
            $table->timestamp('started_at')->comment('开始时间');
            $table->timestamp('finished_at')->nullable()->comment('结束时间');
            $table->timestamps();
            
            // 索引
            $table->index('task_type');
            $table->index('status');
            $table->index('started_at');
            $table->index(['task_type', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('openclaw_task_logs');
    }
};
