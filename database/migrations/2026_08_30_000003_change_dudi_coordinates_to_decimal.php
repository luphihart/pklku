<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mengubah tipe kolom latitude & longitude di tabel dudi dari DOUBLE
 * menjadi DECIMAL(10, 7) untuk mencegah pergeseran/rounding error
 * yang terjadi karena floating-point binary representation pada DOUBLE.
 *
 * DECIMAL(10, 7) mampu menyimpan koordinat seperti:
 *   -180.1234567 hingga 180.1234567
 * dengan presisi tetap hingga 7 digit di belakang koma (~1 cm akurasi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dudi', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->change();
            $table->decimal('longitude', 10, 7)->change();
        });
    }

    public function down(): void
    {
        Schema::table('dudi', function (Blueprint $table) {
            $table->double('latitude')->change();
            $table->double('longitude')->change();
        });
    }
};
