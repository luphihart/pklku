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
            if (!Schema::hasColumn('penempatan_pkl', 'tipe_shift')) {
                $table->string('tipe_shift', 50)->default('reguler')->after('hari_libur');
            }
            if (!Schema::hasColumn('penempatan_pkl', 'jam_masuk')) {
                $table->time('jam_masuk')->nullable()->after('tipe_shift');
            }
            if (!Schema::hasColumn('penempatan_pkl', 'batas_terlambat')) {
                $table->time('batas_terlambat')->nullable()->after('jam_masuk');
            }
            if (!Schema::hasColumn('penempatan_pkl', 'jam_pulang')) {
                $table->time('jam_pulang')->nullable()->after('batas_terlambat');
            }
            if (!Schema::hasColumn('penempatan_pkl', 'tutup_jam_pulang')) {
                $table->time('tutup_jam_pulang')->nullable()->after('jam_pulang');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penempatan_pkl', function (Blueprint $table) {
            $table->dropColumn(['tipe_shift', 'jam_masuk', 'batas_terlambat', 'jam_pulang', 'tutup_jam_pulang']);
        });
    }
};
