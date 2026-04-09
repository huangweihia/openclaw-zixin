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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('项目名称');
            $table->string('full_name', 255)->nullable()->comment('完整名称（user/repo）');
            $table->text('description')->nullable()->comment('项目描述');
            $table->string('url', 255)->unique()->comment('GitHub URL');
            $table->string('language', 50)->nullable()->comment('编程语言');
            $table->unsignedInteger('stars')->default(0)->comment('Star 数');
            $table->unsignedInteger('forks')->default(0)->comment('Fork 数');
            $table->decimal('score', 5, 2)->default(0)->comment('评分');
            $table->json('tags')->nullable()->comment('标签（JSON 数组）');
            $table->text('monetization')->nullable()->comment('变现分析');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium')->comment('难度');
            $table->boolean('is_featured')->default(false)->comment('是否推荐');
            $table->boolean('is_vip')->default(false)->comment('是否 VIP 专属');
            $table->unsignedBigInteger('category_id')->nullable()->comment('分类 ID');
            $table->timestamps();
            
            // Foreign key
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            
            // Indexes
            $table->index('url');
            $table->index('stars');
            $table->index('language');
            $table->index('is_featured');
            $table->index('is_vip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
