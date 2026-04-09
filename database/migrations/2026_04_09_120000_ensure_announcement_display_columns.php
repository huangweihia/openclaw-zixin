<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 若历史环境未执行 2026_04_08_100000，补全 announcements 展示字段，避免首页报错。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'display_position')) {
                $table->string('display_position', 20)->default('top')->after('priority');
            }
            if (! Schema::hasColumn('announcements', 'is_floating')) {
                $table->boolean('is_floating')->default(false)->after('display_position');
            }
            if (! Schema::hasColumn('announcements', 'cover_image')) {
                $table->string('cover_image', 500)->nullable()->after('is_floating');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'cover_image')) {
                $table->dropColumn('cover_image');
            }
            if (Schema::hasColumn('announcements', 'is_floating')) {
                $table->dropColumn('is_floating');
            }
            if (Schema::hasColumn('announcements', 'display_position')) {
                $table->dropColumn('display_position');
            }
        });
    }
};
