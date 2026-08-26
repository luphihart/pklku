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
            if (!Schema::hasColumn('penempatan_pkl', 'hari_libur')) {
                $table->string('hari_libur')->nullable()->after('hari_wfa');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penempatan_pkl', function (Blueprint $table) {
            if (Schema::hasColumn('penempatan_pkl', 'hari_libur')) {
                $table->dropColumn('hari_libur');
            }
        });
    }
};
