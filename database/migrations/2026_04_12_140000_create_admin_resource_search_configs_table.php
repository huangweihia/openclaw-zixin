<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_resource_search_configs', function (Blueprint $table) {
            $table->id();
            $table->string('resource_class')->unique();
            $table->json('search_column_names')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_resource_search_configs');
    }
};
