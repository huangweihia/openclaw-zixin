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
        Schema::create('user_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->comment('用户 ID');
            $table->boolean('audit_enabled')->default(true)->comment('审核通知开关');
            $table->boolean('interaction_enabled')->default(true)->comment('互动通知开关');
            $table->boolean('vip_enabled')->default(true)->comment('会员通知开关');
            $table->boolean('system_enabled')->default(true)->comment('系统公告开关');
            $table->timestamps();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Index
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_settings');
    }
};
