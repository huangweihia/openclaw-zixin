<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('svip_custom_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('svip_custom_subscriptions', 'delivery_frequency')) {
                $table->string('delivery_frequency', 40)->nullable()->after('description')->comment('推送频率');
            }
            if (! Schema::hasColumn('svip_custom_subscriptions', 'preferred_send_time')) {
                $table->string('preferred_send_time', 40)->nullable()->after('delivery_frequency')->comment('期望发送时间如 09:00');
            }
            if (! Schema::hasColumn('svip_custom_subscriptions', 'delivery_channel')) {
                $table->string('delivery_channel', 40)->nullable()->after('preferred_send_time')->comment('接收渠道');
            }
        });

        Schema::table('private_traffic_sops', function (Blueprint $table) {
            if (! Schema::hasColumn('private_traffic_sops', 'contact_note')) {
                $table->text('contact_note')->nullable()->after('content')->comment('联系方式说明');
            }
            if (! Schema::hasColumn('private_traffic_sops', 'vip_gate_engagement')) {
                $table->boolean('vip_gate_engagement')->default(false)->after('contact_note')->comment('开启后仅 VIP 可评论与查看联系方式');
            }
        });
    }

    public function down(): void
    {
        Schema::table('svip_custom_subscriptions', function (Blueprint $table) {
            foreach (['delivery_channel', 'preferred_send_time', 'delivery_frequency'] as $col) {
                if (Schema::hasColumn('svip_custom_subscriptions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('private_traffic_sops', function (Blueprint $table) {
            if (Schema::hasColumn('private_traffic_sops', 'vip_gate_engagement')) {
                $table->dropColumn('vip_gate_engagement');
            }
            if (Schema::hasColumn('private_traffic_sops', 'contact_note')) {
                $table->dropColumn('contact_note');
            }
        });
    }
};
