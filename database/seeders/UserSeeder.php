<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Modules\MasterData\Models\Guru;
use App\Modules\MasterData\Models\Murid;
use App\Modules\MasterData\Models\Kelas;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Admin
        User::create([
            'name' => 'Administrator PKL',
            'email' => 'admin@pklsmk.sch.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '081234567890',
        ]);

        // 2. Create Teachers (Guru)
        $teachersData = [
            ['nama' => 'Budi Hermawan, S.Kom', 'email' => 'budi@pklsmk.sch.id', 'nip' => '198503112010011002'],
            ['nama' => 'Siti Aminah, M.T', 'email' => 'siti@pklsmk.sch.id', 'nip' => '198709152012012001'],
            ['nama' => 'Hendro Wibowo, S.Pd', 'email' => 'hendro@pklsmk.sch.id', 'nip' => '198205202008011003'],
        ];

        foreach ($teachersData as $t) {
            $user = User::create([
                'name' => $t['nama'],
                'email' => $t['email'],
                'password' => Hash::make('guru123'),
                'role' => 'guru',
                'phone' => '082345678901',
            ]);

            Guru::create([
                'user_id' => $user->id,
                'nip' => $t['nip'],
                'nama' => $t['nama'],
            ]);
        }

        // Get class ids for student assignment
        $kelasIds = Kelas::pluck('id')->toArray();
        if (empty($kelasIds)) {
            // Fallback if no classes seeded yet
            $kelasIds = [1];
        }

        // 3. Create Students (Murid)
        $studentsData = [
            ['nama' => 'Ahmad Fauzi', 'email' => 'ahmad@pklsmk.sch.id', 'nis' => '102911', 'kelas_id' => $kelasIds[0] ?? 1],
            ['nama' => 'Citra Lestari', 'email' => 'citra@pklsmk.sch.id', 'nis' => '102912', 'kelas_id' => $kelasIds[0] ?? 1],
            ['nama' => 'Danu Wijaya', 'email' => 'danu@pklsmk.sch.id', 'nis' => '102913', 'kelas_id' => $kelasIds[1] ?? ($kelasIds[0] ?? 1)],
            ['nama' => 'Eka Saputri', 'email' => 'eka@pklsmk.sch.id', 'nis' => '102914', 'kelas_id' => $kelasIds[1] ?? ($kelasIds[0] ?? 1)],
        ];

        foreach ($studentsData as $s) {
            $user = User::create([
                'name' => $s['nama'],
                'email' => $s['email'],
                'password' => Hash::make('murid123'),
                'role' => 'murid',
                'phone' => '083456789012',
            ]);

            Murid::create([
                'user_id' => $user->id,
                'nis' => $s['nis'],
                'nama' => $s['nama'],
                'kelas_id' => $s['kelas_id'],
            ]);
        }
    }
}
