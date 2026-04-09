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
        Schema::create('ad_slots', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('广告位名称');
            $table->string('code', 100)->unique()->comment('广告位代码');
            $table->string('position', 100)->comment('广告位置');
            $table->enum('type', ['banner', 'sidebar', 'inline', 'popup'])->default('banner')->comment('广告类型');
            $table->integer('width')->nullable()->comment('宽度（px）');
            $table->integer('height')->nullable()->comment('高度（px）');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_slots');
    }
};
