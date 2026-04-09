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
        Schema::create('svip_custom_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->string('plan_name', 255)->comment('套餐名称');
            $table->text('description')->nullable()->comment('套餐描述');
            $table->decimal('amount', 10, 2)->comment('支付金额');
            $table->integer('duration_days')->comment('服务天数');
            $table->json('services')->nullable()->comment('服务内容（JSON 数组）');
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending')->comment('订阅状态');
            $table->timestamp('started_at')->nullable()->comment('开始时间');
            $table->timestamp('expires_at')->nullable()->comment('到期时间');
            $table->timestamps();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('svip_custom_subscriptions');
    }
};
