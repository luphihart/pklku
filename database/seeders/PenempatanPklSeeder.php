<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\MasterData\Models\TahunAjaran;
use App\Modules\MasterData\Models\Murid;
use App\Modules\MasterData\Models\Guru;
use App\Modules\MasterData\Models\Dudi;
use App\Modules\MasterData\Models\PembimbingIndustri;
use App\Modules\PKL\Models\PenempatanPkl;

class PenempatanPklSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Tahun Ajaran
        $ta = TahunAjaran::create([
            'tahun' => '2025/2026',
            'semester' => 'ganjil',
            'is_aktif' => true,
        ]);

        // Get resources
        $murids = Murid::all();
        $gurus = Guru::all();
        $dudiPT = Dudi::where('nama', 'like', '%Sukses Kreatif%')->first();
        $dudiCV = Dudi::where('nama', 'like', '%Tech Media%')->first();

        if ($murids->count() >= 4 && $gurus->count() >= 2 && $dudiPT && $dudiCV) {
            // Get pembimbing industri
            $piPT = PembimbingIndustri::where('dudi_id', $dudiPT->id)->first();
            $piCV = PembimbingIndustri::where('dudi_id', $dudiCV->id)->first();

            // Assign Ahmad and Citra to PT. Sukses Kreatif, guided by Guru Budi (guru1)
            PenempatanPkl::create([
                'murid_id' => $murids[0]->id, // Ahmad
                'dudi_id' => $dudiPT->id,
                'guru_id' => $gurus[0]->id, // Budi
                'pembimbing_industri_id' => $piPT ? $piPT->id : null,
                'tahun_ajaran_id' => $ta->id,
                'tanggal_mulai' => '2026-07-01',
                'tanggal_selesai' => '2026-12-31',
                'status' => 'aktif',
            ]);

            PenempatanPkl::create([
                'murid_id' => $murids[1]->id, // Citra
                'dudi_id' => $dudiPT->id,
                'guru_id' => $gurus[0]->id, // Budi
                'pembimbing_industri_id' => $piPT ? $piPT->id : null,
                'tahun_ajaran_id' => $ta->id,
                'tanggal_mulai' => '2026-07-01',
                'tanggal_selesai' => '2026-12-31',
                'status' => 'aktif',
            ]);

            // Assign Danu and Eka to CV. Tech Media, guided by Guru Siti (guru2)
            PenempatanPkl::create([
                'murid_id' => $murids[2]->id, // Danu
                'dudi_id' => $dudiCV->id,
                'guru_id' => $gurus[1]->id, // Siti
                'pembimbing_industri_id' => $piCV ? $piCV->id : null,
                'tahun_ajaran_id' => $ta->id,
                'tanggal_mulai' => '2026-07-01',
                'tanggal_selesai' => '2026-12-31',
                'status' => 'aktif',
            ]);

            PenempatanPkl::create([
                'murid_id' => $murids[3]->id, // Eka
                'dudi_id' => $dudiCV->id,
                'guru_id' => $gurus[1]->id, // Siti
                'pembimbing_industri_id' => $piCV ? $piCV->id : null,
                'tahun_ajaran_id' => $ta->id,
                'tanggal_mulai' => '2026-07-01',
                'tanggal_selesai' => '2026-12-31',
                'status' => 'aktif',
            ]);
        }
    }
}
