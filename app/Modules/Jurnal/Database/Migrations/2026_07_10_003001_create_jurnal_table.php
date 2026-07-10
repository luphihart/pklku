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
        Schema::create('jurnal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penempatan_pkl_id')->constrained('penempatan_pkl')->onDelete('cascade');
            $table->date('tanggal');
            $table->text('deskripsi_aktivitas');
            $table->string('foto_kegiatan')->nullable();
            $table->enum('status_verifikasi', ['pending', 'disetujui', 'ditolak', 'revisi'])->default('pending');
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('guru')->onDelete('set null');
            $table->timestamps();

            // Index gabungan
            $table->index(['penempatan_pkl_id', 'tanggal', 'status_verifikasi'], 'idx_jurnal_pencarian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal');
    }
};
