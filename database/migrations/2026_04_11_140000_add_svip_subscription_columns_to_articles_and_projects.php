<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('articles')) {
            Schema::table('articles', function (Blueprint $table) {
                if (! Schema::hasColumn('articles', 'svip_subscription_id')) {
                    $table->unsignedBigInteger('svip_subscription_id')->nullable()->after('author_id')->index();
                }
                if (! Schema::hasColumn('articles', 'is_vip_only')) {
                    $table->boolean('is_vip_only')->default(false)->after('is_vip');
                }
            });
        }

        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (! Schema::hasColumn('projects', 'svip_subscription_id')) {
                    $table->unsignedBigInteger('svip_subscription_id')->nullable()->after('category_id')->index();
                }
                if (! Schema::hasColumn('projects', 'collected_at')) {
                    $table->timestamp('collected_at')->nullable()->after('updated_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('articles')) {
            Schema::table('articles', function (Blueprint $table) {
                if (Schema::hasColumn('articles', 'is_vip_only')) {
                    $table->dropColumn('is_vip_only');
                }
                if (Schema::hasColumn('articles', 'svip_subscription_id')) {
                    $table->dropColumn('svip_subscription_id');
                }
            });
        }
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (Schema::hasColumn('projects', 'collected_at')) {
                    $table->dropColumn('collected_at');
                }
                if (Schema::hasColumn('projects', 'svip_subscription_id')) {
                    $table->dropColumn('svip_subscription_id');
                }
            });
        }
    }
};
