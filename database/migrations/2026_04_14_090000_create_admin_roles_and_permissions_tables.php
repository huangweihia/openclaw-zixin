<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('admin_roles')) {
            Schema::create('admin_roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('key')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admin_permissions')) {
            Schema::create('admin_permissions', function (Blueprint $table) {
                $table->id();
                $table->string('module')->nullable();
                $table->string('action')->nullable();
                $table->string('key')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admin_role_permissions')) {
            Schema::create('admin_role_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('admin_role_id');
                $table->unsignedBigInteger('admin_permission_id');

                $table->primary(['admin_role_id', 'admin_permission_id'], 'admin_role_permission_pk');

                $table->foreign('admin_role_id')
                    ->references('id')
                    ->on('admin_roles')
                    ->onDelete('cascade');

                $table->foreign('admin_permission_id')
                    ->references('id')
                    ->on('admin_permissions')
                    ->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('admin_user_roles')) {
            Schema::create('admin_user_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('admin_role_id');

                $table->primary(['user_id', 'admin_role_id'], 'admin_user_role_pk');

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('admin_role_id')
                    ->references('id')
                    ->on('admin_roles')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('admin_user_roles')) {
            Schema::drop('admin_user_roles');
        }

        if (Schema::hasTable('admin_role_permissions')) {
            Schema::drop('admin_role_permissions');
        }

        if (Schema::hasTable('admin_permissions')) {
            Schema::drop('admin_permissions');
        }

        if (Schema::hasTable('admin_roles')) {
            Schema::drop('admin_roles');
        }
    }
};

