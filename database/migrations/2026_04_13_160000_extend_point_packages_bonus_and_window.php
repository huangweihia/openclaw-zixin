<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('point_packages')) {
            return;
        }

        Schema::table('point_packages', function (Blueprint $table) {
            if (! Schema::hasColumn('point_packages', 'bonus_points')) {
                $table->unsignedInteger('bonus_points')->default(0)->after('points_amount')->comment('赠送积分');
            }
            if (! Schema::hasColumn('point_packages', 'active_from')) {
                $table->timestamp('active_from')->nullable()->after('is_active')->comment('生效开始时间');
            }
            if (! Schema::hasColumn('point_packages', 'active_until')) {
                $table->timestamp('active_until')->nullable()->after('active_from')->comment('生效结束时间');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('point_packages')) {
            return;
        }

        Schema::table('point_packages', function (Blueprint $table) {
            foreach (['active_until', 'active_from', 'bonus_points'] as $col) {
                if (Schema::hasColumn('point_packages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
