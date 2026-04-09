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
        Schema::table('side_hustle_cases', function (Blueprint $table) {
            // 添加资源类型字段（在 time_investment 字段后）
            $table->enum('resource_type', ['article', 'video', 'disk', 'image'])
                  ->default('article')
                  ->comment('资源类型：article=文章，video=视频，disk=网盘，image=图片')
                  ->after('time_investment');
            
            // 添加原始资源地址字段（在 resource_type 后）
            $table->string('resource_url', 500)
                  ->nullable()
                  ->comment('原始资源地址（视频链接/网盘链接/图片链接）')
                  ->after('resource_type');
            
            // 添加索引
            $table->index('resource_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('side_hustle_cases', function (Blueprint $table) {
            $table->dropIndex(['resource_type']);
            $table->dropColumn(['resource_type', 'resource_url']);
        });
    }
};
