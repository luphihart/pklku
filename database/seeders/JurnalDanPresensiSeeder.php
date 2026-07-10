<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\PKL\Models\PenempatanPkl;
use App\Modules\Presensi\Models\Presensi;
use App\Modules\Jurnal\Models\Jurnal;
use Carbon\Carbon;

class JurnalDanPresensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $penempatans = PenempatanPkl::where('status', 'aktif')->get();

        // Loop for the past 5 days (excluding weekend)
        for ($i = 5; $i >= 1; $i--) {
            $date = Carbon::now()->subDays($i);

            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            $dateString = $date->toDateString();

            foreach ($penempatans as $p) {
                // 1. Presensi
                // Random status checkin
                $isLate = rand(0, 10) > 8; // 20% chance of being late
                $jamMasuk = $isLate ? '07:45:00' : '07:15:00';
                $statusMasuk = $isLate ? 'terlambat' : 'tepat_waktu';

                Presensi::create([
                    'penempatan_pkl_id' => $p->id,
                    'tanggal' => $dateString,
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => '16:05:00',
                    'lat_masuk' => $p->dudi->latitude + (rand(-10, 10) / 100000.0), // slightly offset coordinates
                    'lng_masuk' => $p->dudi->longitude + (rand(-10, 10) / 100000.0),
                    'lat_pulang' => $p->dudi->latitude + (rand(-10, 10) / 100000.0),
                    'lng_pulang' => $p->dudi->longitude + (rand(-10, 10) / 100000.0),
                    'foto_masuk' => 'selfie_masuk_dummy.jpg',
                    'foto_pulang' => 'selfie_pulang_dummy.jpg',
                    'status_masuk' => $statusMasuk,
                    'status_pulang' => 'tepat_waktu',
                ]);

                // 2. Jurnal
                $statusVerif = rand(0, 10) > 4 ? 'disetujui' : 'pending';
                $catatan = $statusVerif == 'disetujui' ? 'Bagus, pertahankan.' : null;
                $verifiedBy = $statusVerif == 'disetujui' ? $p->guru_id : null;

                Jurnal::create([
                    'penempatan_pkl_id' => $p->id,
                    'tanggal' => $dateString,
                    'deskripsi_aktivitas' => 'Melakukan pekerjaan harian di divisi IT. Membantu troubleshooting jaringan lokal dan mempelajari framework PHP.',
                    'foto_kegiatan' => 'jurnal_dummy.jpg',
                    'status_verifikasi' => $statusVerif,
                    'catatan_verifikasi' => $catatan,
                    'verified_by' => $verifiedBy,
                ]);
            }
        }
    }
}
