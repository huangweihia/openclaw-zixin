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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('操作用户 ID');
            $table->string('action', 100)->comment('操作类型');
            $table->string('model_type', 255)->comment('模型类型');
            $table->unsignedBigInteger('model_id')->nullable()->comment('模型 ID');
            $table->json('old_values')->nullable()->comment('修改前的值');
            $table->json('new_values')->nullable()->comment('修改后的值');
            $table->string('ip', 45)->nullable()->comment('IP 地址');
            $table->string('user_agent', 500)->nullable()->comment('User Agent');
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('user_id');
            $table->index('action');
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
