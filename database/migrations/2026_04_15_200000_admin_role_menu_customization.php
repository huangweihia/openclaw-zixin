<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('admin_roles') && ! Schema::hasColumn('admin_roles', 'menu_mode')) {
            Schema::table('admin_roles', function (Blueprint $table) {
                $table->string('menu_mode', 32)->default('inherit')->after('description');
            });
        }

        if (! Schema::hasTable('admin_role_menu_items')) {
            Schema::create('admin_role_menu_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_role_id');
                $table->string('menu_key', 128);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['admin_role_id', 'menu_key'], 'admin_role_menu_key_unique');
                $table->foreign('admin_role_id')
                    ->references('id')
                    ->on('admin_roles')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('admin_role_menu_items')) {
            Schema::drop('admin_role_menu_items');
        }

        if (Schema::hasTable('admin_roles') && Schema::hasColumn('admin_roles', 'menu_mode')) {
            Schema::table('admin_roles', function (Blueprint $table) {
                $table->dropColumn('menu_mode');
            });
        }
    }
};
