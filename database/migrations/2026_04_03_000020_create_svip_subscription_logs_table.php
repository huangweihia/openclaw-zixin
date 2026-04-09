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
        Schema::create('svip_subscription_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id')->comment('订阅 ID');
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->string('action', 50)->comment('操作类型');
            $table->text('description')->nullable()->comment('操作描述');
            $table->unsignedBigInteger('operator_id')->nullable()->comment('操作人 ID');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('subscription_id')->references('id')->on('svip_custom_subscriptions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('operator_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('subscription_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('svip_subscription_logs');
    }
};
