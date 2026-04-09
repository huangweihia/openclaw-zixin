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
        Schema::create('user_theme_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->comment('用户 ID');
            $table->string('theme', 50)->default('default')->comment('主题名称');
            $table->boolean('dark_mode')->default(false)->comment('深色模式');
            $table->string('font_size', 20)->default('medium')->comment('字体大小');
            $table->boolean('follow_system')->default(false)->comment('跟随系统');
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
        Schema::dropIfExists('user_theme_preferences');
    }
};
