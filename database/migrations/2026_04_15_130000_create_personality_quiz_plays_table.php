<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personality_quiz_plays', function (Blueprint $table) {
            $table->id();
            $table->uuid('guest_token')->nullable()->unique()->comment('浏览器 localStorage 中的游客标识');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('completed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personality_quiz_plays');
    }
};
