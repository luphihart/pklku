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
        Schema::create('penempatan_pkl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('murid_id')->constrained('murid')->onDelete('cascade');
            $table->foreignId('dudi_id')->constrained('dudi')->onDelete('restrict');
            $table->foreignId('guru_id')->constrained('guru')->onDelete('restrict'); // Guru Pembimbing
            $table->foreignId('pembimbing_industri_id')->nullable()->constrained('pembimbing_industri')->onDelete('set null'); // Pembimbing Industri
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->onDelete('restrict');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['aktif', 'selesai', 'batal'])->default('aktif');
            $table->timestamps();

            // Indexes
            $table->index(['murid_id', 'tahun_ajaran_id', 'status'], 'idx_murid_tahun_status');
            $table->index(['guru_id', 'status'], 'idx_guru_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penempatan_pkl');
    }
};
