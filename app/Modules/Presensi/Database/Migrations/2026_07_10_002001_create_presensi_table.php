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
        Schema::create('presensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penempatan_pkl_id')->constrained('penempatan_pkl')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->double('lat_masuk')->nullable();
            $table->double('lng_masuk')->nullable();
            $table->double('lat_pulang')->nullable();
            $table->double('lng_pulang')->nullable();
            $table->string('foto_masuk')->nullable();
            $table->string('foto_pulang')->nullable();
            $table->enum('status_masuk', ['tepat_waktu', 'terlambat'])->nullable();
            $table->enum('status_pulang', ['pulang_cepat', 'tepat_waktu'])->nullable();
            $table->timestamps();

            // Index gabungan untuk pencarian harian yang cepat
            $table->unique(['penempatan_pkl_id', 'tanggal']);
            $table->index(['tanggal', 'status_masuk']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
