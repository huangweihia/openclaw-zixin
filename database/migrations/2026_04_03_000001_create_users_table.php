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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('用户昵称');
            $table->string('email', 255)->unique()->comment('邮箱（登录账号）');
            $table->timestamp('email_verified_at')->nullable()->comment('邮箱验证时间');
            $table->string('password', 255)->comment('密码（bcrypt 加密）');
            $table->string('avatar', 255)->nullable()->comment('头像 URL');
            $table->enum('role', ['user', 'vip', 'svip', 'admin'])->default('user')->comment('用户角色');
            $table->timestamp('subscription_ends_at')->nullable()->comment('VIP 到期时间');
            $table->timestamp('last_login_at')->nullable()->comment('最后登录时间');
            $table->string('last_login_ip', 45)->nullable()->comment('最后登录 IP');
            $table->string('remember_token', 100)->nullable()->comment('记住我 token');
            $table->timestamps();
            
            // Indexes
            $table->index('email');
            $table->index('role');
            $table->index('subscription_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
