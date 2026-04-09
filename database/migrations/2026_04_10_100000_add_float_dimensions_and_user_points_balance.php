<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'float_width')) {
                $table->unsignedSmallInteger('float_width')->nullable()->after('cover_image')->comment('浮动卡片宽度 px，空则默认');
            }
            if (! Schema::hasColumn('announcements', 'float_height')) {
                $table->unsignedSmallInteger('float_height')->nullable()->after('float_width')->comment('浮动卡片配图区高度 px，空则默认');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'points_balance')) {
                $table->unsignedInteger('points_balance')->default(0)->after('subscription_ends_at')->comment('积分余额（与 points 流水表一致）');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'float_height')) {
                $table->dropColumn('float_height');
            }
            if (Schema::hasColumn('announcements', 'float_width')) {
                $table->dropColumn('float_width');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'points_balance')) {
                $table->dropColumn('points_balance');
            }
        });
    }
};
