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
        Schema::table('penempatan_pkl', function (Blueprint $table) {
            if (!Schema::hasColumn('penempatan_pkl', 'tipe_kerja')) {
                $table->enum('tipe_kerja', ['wfo', 'wfa', 'hybrid'])->default('wfo')->after('status');
            }
            if (!Schema::hasColumn('penempatan_pkl', 'hari_wfa')) {
                $table->string('hari_wfa')->nullable()->after('tipe_kerja');
            }
        });

        Schema::table('presensi', function (Blueprint $table) {
            if (!Schema::hasColumn('presensi', 'is_wfa')) {
                $table->boolean('is_wfa')->default(false)->after('status_pulang');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penempatan_pkl', function (Blueprint $table) {
            if (Schema::hasColumn('penempatan_pkl', 'hari_wfa')) {
                $table->dropColumn('hari_wfa');
            }
            if (Schema::hasColumn('penempatan_pkl', 'tipe_kerja')) {
                $table->dropColumn('tipe_kerja');
            }
        });

        Schema::table('presensi', function (Blueprint $table) {
            if (Schema::hasColumn('presensi', 'is_wfa')) {
                $table->dropColumn('is_wfa');
            }
        });
    }
};
