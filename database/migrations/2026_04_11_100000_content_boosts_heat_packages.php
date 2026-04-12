<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('content_boosts')) {
            Schema::create('content_boosts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('actor_user_id')->comment('加热发起用户');
                $table->unsignedBigInteger('user_post_id')->comment('被加热的投稿');
                $table->unsignedInteger('weight')->default(10)->comment('推荐/排序权重贡献');
                $table->unsignedInteger('points_spent')->default(0);
                $table->timestamp('starts_at');
                $table->timestamp('ends_at');
                $table->timestamps();

                $table->foreign('actor_user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('user_post_id')->references('id')->on('user_posts')->onDelete('cascade');
                $table->index(['user_post_id', 'ends_at']);
                $table->index('actor_user_id');
            });
        }

        if (! Schema::hasTable('point_packages')) {
            Schema::create('point_packages', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->unsignedInteger('points_amount')->comment('到账积分');
                $table->decimal('price_yuan', 10, 2);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->string('badge', 40)->nullable();
                $table->timestamps();
            });

            if (DB::table('point_packages')->count() === 0) {
                $now = now();
                DB::table('point_packages')->insert([
                    [
                        'name' => '入门包',
                        'points_amount' => 500,
                        'price_yuan' => 9.90,
                        'sort_order' => 10,
                        'is_active' => true,
                        'badge' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    [
                        'name' => '加热包',
                        'points_amount' => 2000,
                        'price_yuan' => 29.90,
                        'sort_order' => 20,
                        'is_active' => true,
                        'badge' => '推荐',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                ]);
            }
        }

        Schema::table('user_posts', function (Blueprint $table) {
            if (! Schema::hasColumn('user_posts', 'heat_score')) {
                $table->unsignedInteger('heat_score')->default(0)->after('favorite_count')->comment('内容热度（自动累计）');
            }
            if (! Schema::hasColumn('user_posts', 'boost_weight')) {
                $table->unsignedInteger('boost_weight')->default(0)->after('heat_score')->comment('当前有效加热权重汇总');
            }
            if (! Schema::hasColumn('user_posts', 'last_boost_at')) {
                $table->timestamp('last_boost_at')->nullable()->after('boost_weight');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_posts', function (Blueprint $table) {
            foreach (['last_boost_at', 'boost_weight', 'heat_score'] as $col) {
                if (Schema::hasColumn('user_posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::dropIfExists('point_packages');
        Schema::dropIfExists('content_boosts');
    }
};
