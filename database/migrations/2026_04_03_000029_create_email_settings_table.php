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
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('配置名称');
            $table->string('key', 255)->unique()->comment('配置键');
            $table->text('value')->comment('配置值');
            $table->string('description', 500)->nullable()->comment('描述');
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
        Schema::dropIfExists('email_settings');
    }
};
