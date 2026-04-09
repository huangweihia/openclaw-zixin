<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * 将早期简化版 email_subscriptions（is_active / confirmed_at）升级为设计文档结构。
 * 全新库若已用 000032 新结构创建，本迁移检测到 subscribed_to 后即跳过。
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_subscriptions')) {
            return;
        }

        if (Schema::hasColumn('email_subscriptions', 'subscribed_to')) {
            return;
        }

        Schema::table('email_subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->json('subscribed_to')->nullable()->after('email');
            $table->boolean('is_unsubscribed')->default(false)->after('subscribed_to');
            $table->string('unsubscribe_token', 100)->nullable()->after('unsubscribed_at');
        });

        $driver = Schema::getConnection()->getDriverName();

        foreach (DB::table('email_subscriptions')->orderBy('id')->cursor() as $row) {
            $isUnsub = false;
            if (isset($row->is_active)) {
                $isUnsub = ! (bool) $row->is_active;
            }
            if (! empty($row->unsubscribed_at)) {
                $isUnsub = true;
            }

            DB::table('email_subscriptions')->where('id', $row->id)->update([
                'subscribed_to' => json_encode(['notification']),
                'is_unsubscribed' => $isUnsub,
                'unsubscribe_token' => Str::random(48),
            ]);
        }

        DB::table('email_subscriptions')->whereNull('subscribed_to')->update([
            'subscribed_to' => json_encode(['notification']),
        ]);

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE email_subscriptions MODIFY subscribed_to JSON NOT NULL');
        }

        Schema::table('email_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('email_subscriptions', 'is_active')) {
                $table->dropColumn(['is_active', 'confirmed_at']);
            }
        });

        Schema::table('email_subscriptions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique('unsubscribe_token');
            $table->index('is_unsubscribed');
        });
    }

    public function down(): void
    {
        // 不可逆：旧结构已弃用
    }
};
