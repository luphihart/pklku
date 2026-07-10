<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Setting\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'nama_sekolah' => 'SMK Negeri 1 Antigravity',
            'alamat_sekolah' => 'Jl. Raya Antigravity No. 42, Kota Coding',
            'logo_sekolah' => 'logo_smk.png',
            'kop_surat' => 'PEMERINTAH PROVINSI ANTIGRAVITY\nDinas Pendidikan Dan Kebudayaan\nSMK NEGERI 1 ANTIGRAVITY\nJl. Raya Antigravity No. 42, Telp (021) 555-0199',
            'radius_presensi' => '50', // dalam meter
            'jam_masuk' => '07:30',
            'jam_pulang' => '16:00',
            'hari_kerja' => 'Senin,Selasa,Rabu,Kamis,Jumat',
            'bobot_nilai_guru' => '40', // 40%
            'bobot_nilai_industri' => '60', // 60%
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
