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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('模板名称');
            $table->string('key', 255)->unique()->comment('模板键');
            $table->string('subject', 255)->comment('邮件主题');
            $table->longText('content')->comment('邮件内容（HTML）');
            $table->text('plain_text')->nullable()->comment('纯文本版本');
            $table->json('variables')->nullable()->comment('变量列表（JSON 数组）');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->timestamps();
            
            // Index
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
