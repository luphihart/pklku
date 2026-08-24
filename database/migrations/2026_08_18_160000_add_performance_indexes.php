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
        // 1. Indeks pada tabel murid untuk sorting nama & soft deletes
        Schema::table('murid', function (Blueprint $table) {
            $table->index('nama', 'idx_murid_nama');
            $table->index('deleted_at', 'idx_murid_deleted_at');
        });

        // 2. Indeks pada tabel guru untuk sorting nama & soft deletes
        Schema::table('guru', function (Blueprint $table) {
            $table->index('nama', 'idx_guru_nama');
            $table->index('deleted_at', 'idx_guru_deleted_at');
        });

        // 3. Indeks pada tabel dudi untuk sorting nama
        Schema::table('dudi', function (Blueprint $table) {
            $table->index('nama', 'idx_dudi_nama');
        });

        // 4. Indeks pada tabel kunjungan_monitoring untuk penempatan & tanggal
        Schema::table('kunjungan_monitoring', function (Blueprint $table) {
            $table->index(['penempatan_pkl_id', 'tanggal'], 'idx_kunjungan_penempatan_tgl');
        });

        // 5. Indeks pada tabel izin_sakit untuk penempatan & approval
        Schema::table('izin_sakit', function (Blueprint $table) {
            $table->index(['penempatan_pkl_id', 'status_approval'], 'idx_izin_penempatan_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('murid', function (Blueprint $table) {
            $table->dropIndex('idx_murid_nama');
            $table->dropIndex('idx_murid_deleted_at');
        });

        Schema::table('guru', function (Blueprint $table) {
            $table->dropIndex('idx_guru_nama');
            $table->dropIndex('idx_guru_deleted_at');
        });

        Schema::table('dudi', function (Blueprint $table) {
            $table->dropIndex('idx_dudi_nama');
        });

        Schema::table('kunjungan_monitoring', function (Blueprint $table) {
            $table->dropIndex('idx_kunjungan_penempatan_tgl');
        });

        Schema::table('izin_sakit', function (Blueprint $table) {
            $table->dropIndex('idx_izin_penempatan_status');
        });
    }
};
