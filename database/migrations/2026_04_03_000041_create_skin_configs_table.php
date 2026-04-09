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
        Schema::create('skin_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('皮肤名称');
            $table->string('code', 50)->unique()->comment('皮肤代码');
            $table->string('description', 500)->nullable()->comment('皮肤描述');
            $table->string('preview_image', 255)->nullable()->comment('预览图 URL');
            $table->json('css_variables')->nullable()->comment('CSS 变量（JSON 对象）');
            $table->enum('type', ['free', 'vip', 'svip'])->default('free')->comment('皮肤类型');
            $table->integer('sort')->default(0)->comment('排序');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('type');
            $table->index('sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skin_configs');
    }
};
