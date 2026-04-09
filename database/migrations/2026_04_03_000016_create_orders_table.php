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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->string('order_no', 64)->unique()->comment('订单号');
            $table->string('product_type', 50)->comment('商品类型');
            $table->unsignedBigInteger('product_id')->comment('商品 ID');
            $table->decimal('amount', 10, 2)->comment('订单金额');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->comment('订单状态');
            $table->string('payment_id', 255)->nullable()->comment('支付 ID');
            $table->string('payment_method', 50)->nullable()->comment('支付方式');
            $table->timestamp('paid_at')->nullable()->comment('支付时间');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('order_no');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
