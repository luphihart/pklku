<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('penilaian_pkl', function (Blueprint $table) {
            $table->json('keterangan_tp_json')->nullable()->after('nilai_industri_json');
        });
    }

    public function down()
    {
        Schema::table('penilaian_pkl', function (Blueprint $table) {
            $table->dropColumn('keterangan_tp_json');
        });
    }
};
