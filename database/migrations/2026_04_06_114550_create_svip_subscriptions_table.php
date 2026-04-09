<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('svip_subscriptions')) {
            return;
        }

        Schema::create('svip_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->json('keywords');
            $table->json('exclude_keywords')->nullable();
            $table->json('sources')->nullable();
            $table->string('frequency')->default('daily');
            $table->json('push_methods')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_fetch_at')->nullable();
            $table->integer('last_fetch_count')->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index('frequency');
        });
    }
    
    public function down(): void
    {
        if (! Schema::hasTable('svip_subscriptions')) {
            return;
        }

        Schema::dropIfExists('svip_subscriptions');
    }
};
