<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('display_name', 120);
            $table->string('caption', 255)->nullable()->comment('如：VIP 会员 · 3 个月');
            $table->text('body');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('avatar_initial', 8)->default('用');
            $table->string('gradient_from', 64)->default('from-blue-400');
            $table->string('gradient_to', 64)->default('to-blue-600');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_testimonials');
    }
};
