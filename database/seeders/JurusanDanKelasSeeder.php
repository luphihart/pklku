<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\MasterData\Models\Jurusan;
use App\Modules\MasterData\Models\Kelas;

class JurusanDanKelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Jurusan
        $rpl = Jurusan::create([
            'kode' => 'RPL',
            'nama' => 'Rekayasa Perangkat Lunak',
        ]);

        $tkj = Jurusan::create([
            'kode' => 'TKJ',
            'nama' => 'Teknik Komputer & Jaringan',
        ]);

        // 2. Seed Kelas
        Kelas::create(['nama' => 'XII RPL 1', 'jurusan_id' => $rpl->id]);
        Kelas::create(['nama' => 'XII RPL 2', 'jurusan_id' => $rpl->id]);
        Kelas::create(['nama' => 'XII TKJ 1', 'jurusan_id' => $tkj->id]);
        Kelas::create(['nama' => 'XII TKJ 2', 'jurusan_id' => $tkj->id]);
    }
}
