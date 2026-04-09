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
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->unsignedBigInteger('order_id')->comment('订单 ID');
            $table->enum('reason', ['not_as_described', 'technical_issue', 'changed_mind', 'other'])->comment('退款原因');
            $table->text('description')->comment('退款说明');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending')->comment('退款状态');
            $table->decimal('refund_amount', 10, 2)->comment('退款金额');
            $table->text('admin_note')->nullable()->comment('管理员备注');
            $table->timestamp('processed_at')->nullable()->comment('处理时间');
            $table->unsignedBigInteger('processed_by')->nullable()->comment('处理人 ID');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('user_id');
            $table->index('order_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
