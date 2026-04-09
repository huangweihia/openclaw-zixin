<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skin_configs', function (Blueprint $table) {
            if (! Schema::hasColumn('skin_configs', 'owner_user_id')) {
                $table->unsignedBigInteger('owner_user_id')->nullable()->after('code')->comment('私有主题所属用户');
            }
            if (! Schema::hasColumn('skin_configs', 'is_private')) {
                $table->boolean('is_private')->default(false)->after('type')->comment('是否私有主题（仅 owner 可见）');
            }
            if (! Schema::hasColumn('skin_configs', 'custom_source')) {
                $table->string('custom_source', 40)->nullable()->after('is_private')->comment('定制来源，如 svip_custom');
            }
        });

        Schema::table('skin_configs', function (Blueprint $table) {
            if (Schema::hasColumn('skin_configs', 'owner_user_id')) {
                $table->index(['owner_user_id', 'is_private'], 'skin_owner_private_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('skin_configs', function (Blueprint $table) {
            if (Schema::hasColumn('skin_configs', 'custom_source')) {
                $table->dropColumn('custom_source');
            }
            if (Schema::hasColumn('skin_configs', 'is_private')) {
                $table->dropColumn('is_private');
            }
            if (Schema::hasColumn('skin_configs', 'owner_user_id')) {
                $table->dropColumn('owner_user_id');
            }
        });
    }
};

