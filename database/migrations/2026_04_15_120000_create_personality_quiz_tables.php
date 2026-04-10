<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personality_dimensions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique()->comment('稳定编码，用于排序与 pattern 对应');
            $table->string('name');
            $table->string('model_group', 64)->nullable()->comment('分组展示用');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('explanation_l');
            $table->text('explanation_m');
            $table->text('explanation_h');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('personality_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personality_dimension_id')->constrained('personality_dimensions')->cascadeOnDelete();
            $table->text('body');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('personality_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personality_question_id')->constrained('personality_questions')->cascadeOnDelete();
            $table->string('label');
            $table->unsignedTinyInteger('value')->comment('1-3，参与维度求和');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('personality_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('cn_name');
            $table->string('intro', 512)->nullable();
            $table->text('description')->nullable();
            $table->string('pattern', 32)->nullable()->comment('15 个 L/M/H，可含横线分隔');
            $table->boolean('is_fallback')->default(false)->comment('低匹配度时兜底');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('personality_quiz_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personality_quiz_settings');
        Schema::dropIfExists('personality_question_options');
        Schema::dropIfExists('personality_questions');
        Schema::dropIfExists('personality_types');
        Schema::dropIfExists('personality_dimensions');
    }
};
