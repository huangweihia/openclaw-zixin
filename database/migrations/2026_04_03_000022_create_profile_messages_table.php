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
        Schema::create('profile_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id')->comment('发送者 ID');
            $table->unsignedBigInteger('receiver_id')->comment('接收者 ID');
            $table->text('content')->comment('留言内容');
            $table->boolean('is_read')->default(false)->comment('是否已读');
            $table->timestamp('read_at')->nullable()->comment('阅读时间');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('sender_id');
            $table->index('receiver_id');
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_messages');
    }
};
