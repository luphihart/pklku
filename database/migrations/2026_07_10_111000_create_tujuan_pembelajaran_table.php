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
        Schema::create('tujuan_pembelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->nullable(); // e.g. "1", "2", "3", "4"
            $table->string('nama');
            $table->timestamps();
        });

        Schema::table('indikator_penilaian', function (Blueprint $table) {
            $table->foreignId('tujuan_pembelajaran_id')->nullable()->constrained('tujuan_pembelajaran')->onDelete('cascade');
            $table->string('nomor_urut')->nullable(); // e.g. "1.1", "1.2", "3.7"
        });

        // Seed initial Tujuan Pembelajaran (TP)
        $tp1 = DB::table('tujuan_pembelajaran')->insertGetId([
            'nomor' => '1',
            'nama' => 'Menerapkan soft skills yang dibutuhkan dalam dunia kerja (tempat PKL)',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $tp2 = DB::table('tujuan_pembelajaran')->insertGetId([
            'nomor' => '2',
            'nama' => 'Menerapkan hard skills yang ada pada dunia kerja (tempat PKL)',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $tp3 = DB::table('tujuan_pembelajaran')->insertGetId([
            'nomor' => '3',
            'nama' => 'Pengembangan hard skills sesuai Konsentrasi Keahlian di sekolah',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $tp4 = DB::table('tujuan_pembelajaran')->insertGetId([
            'nomor' => '4',
            'nama' => 'Penyiapan Kemandirian Kewirausahaan',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Clean existing indicators to start fresh and avoid duplicates
        DB::table('indikator_penilaian')->truncate();

        // Seed new indicators matching the image
        // 1. Soft Skills (tipe: industri)
        DB::table('indikator_penilaian')->insert([
            ['tujuan_pembelajaran_id' => $tp1, 'nomor_urut' => '1.1', 'nama' => 'Dapat berkomunikasi dengan baik', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp1, 'nomor_urut' => '1.2', 'nama' => 'Kejujuran', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp1, 'nomor_urut' => '1.3', 'nama' => 'Kedisiplinan', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp1, 'nomor_urut' => '1.4', 'nama' => 'Komitmen, dan tanggung jawab', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp1, 'nomor_urut' => '1.5', 'nama' => 'Memiliki semangat dan etos kerja', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp1, 'nomor_urut' => '1.6', 'nama' => 'Kemandirian dan kerja tim', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp1, 'nomor_urut' => '1.7', 'nama' => 'Kepedulian sosial dan lingkungan', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp1, 'nomor_urut' => '1.8', 'nama' => 'Menerapkan K3LH (Kesehatan, Keselamatan Kerja dan Lingkungan Hidup)', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp1, 'nomor_urut' => '1.9', 'nama' => 'Patuh pada aturan dan SOP di tempat PKL', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Hard Skills (tipe: industri)
        DB::table('indikator_penilaian')->insert([
            ['tujuan_pembelajaran_id' => $tp2, 'nomor_urut' => '2.1', 'nama' => 'Mampu menemukan ide dalam pekerjaan', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp2, 'nomor_urut' => '2.2', 'nama' => 'Mampu mengoperasikan alat di tempat PKL', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp2, 'nomor_urut' => '2.3', 'nama' => 'Dapat melakukan tugas sesuai bidang di tempat PKL (fotografi/Videografi/percetakan,dll)', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Pengembangan Hard Skills - Industri (3.1 - 3.6)
        DB::table('indikator_penilaian')->insert([
            ['tujuan_pembelajaran_id' => $tp3, 'nomor_urut' => '3.1', 'nama' => 'Mampu membuat Video Company Profile tempat PKL', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp3, 'nomor_urut' => '3.2', 'nama' => 'Ide kreatif/ Inovasi video', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp3, 'nomor_urut' => '3.3', 'nama' => 'Editing Video', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp3, 'nomor_urut' => '3.4', 'nama' => 'Keindahan gambar', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp3, 'nomor_urut' => '3.5', 'nama' => 'Kesesuaian video dengan tempat PKL', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp3, 'nomor_urut' => '3.6', 'nama' => 'Pesan video yang tersampaikan', 'tipe' => 'industri', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Pengembangan Hard Skills - Sekolah/Guru (3.7 - 3.9) (tipe: guru)
        DB::table('indikator_penilaian')->insert([
            ['tujuan_pembelajaran_id' => $tp3, 'nomor_urut' => '3.7', 'nama' => 'Membuat Laporan PKL', 'tipe' => 'guru', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp3, 'nomor_urut' => '3.8', 'nama' => 'Legalitas Laporan PKL', 'tipe' => 'guru', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp3, 'nomor_urut' => '3.9', 'nama' => 'Mempresentasikan Porto Folio / Hasil Karya Siswa', 'tipe' => 'guru', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 4. Penyiapan Kemandirian Kewirausahaan (tipe: guru)
        DB::table('indikator_penilaian')->insert([
            ['tujuan_pembelajaran_id' => $tp4, 'nomor_urut' => '4.1', 'nama' => 'Mampu menganalisa SWOT tempat PKL', 'tipe' => 'guru', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp4, 'nomor_urut' => '4.2', 'nama' => 'Mampu membuat rencana usaha mandiri', 'tipe' => 'guru', 'created_at' => now(), 'updated_at' => now()],
            ['tujuan_pembelajaran_id' => $tp4, 'nomor_urut' => '4.3', 'nama' => 'Mampu menganalisa SWOT usaha yang akan dibuat', 'tipe' => 'guru', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indikator_penilaian', function (Blueprint $table) {
            $table->dropForeign(['tujuan_pembelajaran_id']);
            $table->dropColumn(['tujuan_pembelajaran_id', 'nomor_urut']);
        });

        Schema::dropIfExists('tujuan_pembelajaran');
    }
};
