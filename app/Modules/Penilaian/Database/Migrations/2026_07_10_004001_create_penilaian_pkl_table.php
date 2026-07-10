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
        Schema::create('penilaian_pkl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penempatan_pkl_id')->unique()->constrained('penempatan_pkl')->onDelete('cascade');
            $table->json('nilai_guru_json')->nullable(); // menyimpan penilaian aspek guru
            $table->json('nilai_industri_json')->nullable(); // menyimpan penilaian aspek industri
            $table->decimal('rata_nilai_guru', 5, 2)->nullable();
            $table->decimal('rata_nilai_industri', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('predikat', 2)->nullable(); // A, B, C, D
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_pkl');
    }
};
