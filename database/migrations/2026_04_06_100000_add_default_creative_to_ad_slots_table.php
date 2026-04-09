<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_slots', function (Blueprint $table) {
            $table->string('default_title', 255)->nullable()->after('sort')->comment('无投放/兜底时展示的标题');
            $table->string('default_image_url', 500)->nullable()->after('default_title')->comment('兜底图片');
            $table->string('default_link_url', 500)->nullable()->after('default_image_url')->comment('兜底跳转');
            $table->text('default_content')->nullable()->after('default_link_url')->comment('兜底文案/HTML');
            $table->boolean('show_default_when_empty')->default(true)->after('default_content')->comment('无有效投放时是否展示兜底素材');
        });
    }

    public function down(): void
    {
        Schema::table('ad_slots', function (Blueprint $table) {
            $table->dropColumn([
                'default_title',
                'default_image_url',
                'default_link_url',
                'default_content',
                'show_default_when_empty',
            ]);
        });
    }
};
