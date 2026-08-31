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
        Schema::table('presensi', function (Blueprint $table) {
            $table->string('status_masuk', 30)->nullable()->change();
            if (!Schema::hasColumn('presensi', 'keterangan')) {
                $table->string('keterangan', 255)->nullable()->after('status_pulang');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            if (Schema::hasColumn('presensi', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
        });
    }
};
