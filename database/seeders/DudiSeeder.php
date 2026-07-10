<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\MasterData\Models\Dudi;
use App\Modules\MasterData\Models\PembimbingIndustri;

class DudiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dudiList = [
            [
                'nama' => 'PT. Antigravity Global Technology',
                'alamat' => 'Sudirman Central Business District (SCBD) Lot 10, Jakarta Selatan',
                'latitude' => -6.223056,
                'longitude' => 106.809722,
                'radius_meter' => 100,
                'pic_nama' => 'Eko Prasetyo',
                'pic_phone' => '081299998888',
                'pembimbing' => [
                    ['nama' => 'Rahmat Hidayat', 'phone' => '081299998881', 'email' => 'rahmat@antigravity.co.id'],
                    ['nama' => 'Maya Kartika', 'phone' => '081299998882', 'email' => 'maya@antigravity.co.id']
                ]
            ],
            [
                'nama' => 'CV. Tech Media Nusantara',
                'alamat' => 'Jl. Pemuda No. 15, Kota Coding',
                'latitude' => -6.200000,
                'longitude' => 106.816667,
                'radius_meter' => 50,
                'pic_nama' => 'Agus Wijaya',
                'pic_phone' => '081377776666',
                'pembimbing' => [
                    ['nama' => 'Dwi Santoso', 'phone' => '081377776661', 'email' => 'dwi@techmedia.net']
                ]
            ]
        ];

        foreach ($dudiList as $d) {
            $dudi = Dudi::create([
                'nama' => $d['nama'],
                'alamat' => $d['alamat'],
                'latitude' => $d['latitude'],
                'longitude' => $d['longitude'],
                'radius_meter' => $d['radius_meter'],
                'pic_nama' => $d['pic_nama'],
                'pic_phone' => $d['pic_phone'],
            ]);

            foreach ($d['pembimbing'] as $p) {
                PembimbingIndustri::create([
                    'dudi_id' => $dudi->id,
                    'nama' => $p['nama'],
                    'phone' => $p['phone'],
                    'email' => $p['email'],
                ]);
            }
        }
    }
}
