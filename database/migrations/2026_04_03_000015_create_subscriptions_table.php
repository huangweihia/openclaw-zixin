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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->enum('plan', ['monthly', 'yearly', 'lifetime'])->comment('套餐类型');
            $table->decimal('amount', 10, 2)->comment('支付金额');
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending')->comment('订阅状态');
            $table->timestamp('started_at')->nullable()->comment('开始时间');
            $table->timestamp('expires_at')->nullable()->comment('到期时间');
            $table->string('payment_id', 255)->nullable()->comment('支付 ID');
            $table->string('payment_method', 50)->nullable()->comment('支付方式');
            $table->timestamps();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
