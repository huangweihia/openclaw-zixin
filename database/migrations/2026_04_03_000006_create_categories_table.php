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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('分类名称');
            $table->string('slug', 100)->unique()->comment('URL 标识');
            $table->string('description', 500)->nullable()->comment('分类描述');
            $table->integer('sort')->default(0)->comment('排序（越大越靠前）');
            $table->boolean('is_premium')->default(false)->comment('是否付费分类');
            $table->timestamps();
            
            // Indexes
            $table->index('slug');
            $table->index('sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
