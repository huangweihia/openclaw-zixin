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
        Schema::create('user_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->string('actionable_type', 255)->comment('行为类型（多态）');
            $table->unsignedBigInteger('actionable_id')->comment('行为对象 ID');
            $table->string('type', 50)->comment('行为类型');
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index(['actionable_type', 'actionable_id']);
            $table->index('type');
            $table->unique(['user_id', 'actionable_type', 'actionable_id', 'type'], 'idx_unique_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_actions');
    }
};
