<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('indikator_penilaian', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('tipe', ['guru', 'industri']);
            $table->timestamps();
        });

        // Seed default indicators
        DB::table('indikator_penilaian')->insert([
            ['nama' => 'Sikap Kerja & Disiplin', 'tipe' => 'guru', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Penguasaan Teori & Pemahaman Kerja', 'tipe' => 'guru', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Keterampilan & Hasil Kerja Lapangan', 'tipe' => 'guru', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Sikap Kerja & Disiplin', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Penguasaan Teori & Pemahaman Kerja', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Keterampilan & Hasil Kerja Lapangan', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indikator_penilaian');
    }
};
