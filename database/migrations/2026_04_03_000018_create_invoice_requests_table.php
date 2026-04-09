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
        Schema::create('invoice_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->unsignedBigInteger('order_id')->comment('订单 ID');
            $table->string('invoice_type', 50)->comment('发票类型');
            $table->string('company_name', 255)->nullable()->comment('公司名称');
            $table->string('tax_id', 100)->nullable()->comment('税号');
            $table->string('email', 255)->comment('接收邮箱');
            $table->enum('status', ['pending', 'issued', 'rejected'])->default('pending')->comment('发票状态');
            $table->string('invoice_file', 255)->nullable()->comment('发票文件 URL');
            $table->text('admin_note')->nullable()->comment('管理员备注');
            $table->timestamp('processed_at')->nullable()->comment('处理时间');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            
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
        Schema::dropIfExists('invoice_requests');
    }
};
