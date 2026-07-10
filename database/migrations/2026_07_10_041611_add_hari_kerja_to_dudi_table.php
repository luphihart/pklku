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
        Schema::table('dudi', function (Blueprint $table) {
            $table->string('hari_kerja')->nullable()->default('Senin,Selasa,Rabu,Kamis,Jumat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dudi', function (Blueprint $table) {
            $table->dropColumn('hari_kerja');
        });
    }
};
