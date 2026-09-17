<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Perbarui kolom role di tabel users agar mendukung role 'industri'
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'guru', 'murid', 'industri') NOT NULL DEFAULT 'murid'");

        // 2. Tambah kolom user_id di pembimbing_industri jika belum ada
        if (!Schema::hasColumn('pembimbing_industri', 'user_id')) {
            Schema::table('pembimbing_industri', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('dudi_id')
                      ->constrained('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pembimbing_industri', 'user_id')) {
            Schema::table('pembimbing_industri', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'guru', 'murid') NOT NULL DEFAULT 'murid'");
    }
};
