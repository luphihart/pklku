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
        Schema::table('penilaian_pkl', function (Blueprint $table) {
            if (!Schema::hasColumn('penilaian_pkl', 'bukti_nilai_industri')) {
                $table->string('bukti_nilai_industri')->nullable()->after('keterangan_tp_json');
            }
            if (!Schema::hasColumn('penilaian_pkl', 'status_nilai_industri')) {
                $table->enum('status_nilai_industri', ['draft', 'diajukan', 'diverifikasi'])->default('draft')->after('bukti_nilai_industri');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penilaian_pkl', function (Blueprint $table) {
            if (Schema::hasColumn('penilaian_pkl', 'bukti_nilai_industri')) {
                $table->dropColumn('bukti_nilai_industri');
            }
            if (Schema::hasColumn('penilaian_pkl', 'status_nilai_industri')) {
                $table->dropColumn('status_nilai_industri');
            }
        });
    }
};
