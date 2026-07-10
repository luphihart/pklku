<?php

namespace App\Modules\Dashboard\Repositories;

use App\Models\User;
use App\Modules\MasterData\Models\Murid;
use App\Modules\MasterData\Models\Guru;
use App\Modules\MasterData\Models\Dudi;
use App\Modules\PKL\Models\PenempatanPkl;
use App\Modules\Presensi\Models\Presensi;
use Carbon\Carbon;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getCounts(): array
    {
        $user = auth()->user();
        if ($user && $user->role === 'guru') {
            $guruId = $user->guru ? $user->guru->id : 0;
            return [
                'murid' => PenempatanPkl::where('guru_id', $guruId)->distinct('murid_id')->count('murid_id'),
                'dudi' => PenempatanPkl::where('guru_id', $guruId)->distinct('dudi_id')->count('dudi_id'),
                'penempatan_aktif' => PenempatanPkl::where('guru_id', $guruId)->where('status', 'aktif')->count(),
            ];
        }

        return [
            'murid' => Murid::count(),
            'guru' => Guru::count(),
            'dudi' => Dudi::count(),
            'penempatan_aktif' => PenempatanPkl::where('status', 'aktif')->count(),
        ];
    }

    public function getAttendanceStatsToday(): array
    {
        $today = Carbon::today()->toDateString();
        $user = auth()->user();
        
        if ($user && $user->role === 'guru') {
            $guruId = $user->guru ? $user->guru->id : 0;
            
            $totalAktif = PenempatanPkl::where('guru_id', $guruId)->where('status', 'aktif')->count();
            
            $hadir = Presensi::where('tanggal', $today)
                ->whereNotNull('jam_masuk')
                ->whereHas('penempatanPkl', function ($query) use ($guruId) {
                    $query->where('guru_id', $guruId);
                })
                ->count();
                
            $terlambat = Presensi::where('tanggal', $today)
                ->where('status_masuk', 'terlambat')
                ->whereHas('penempatanPkl', function ($query) use ($guruId) {
                    $query->where('guru_id', $guruId);
                })
                ->count();
        } else {
            $totalAktif = PenempatanPkl::where('status', 'aktif')->count();
            
            $hadir = Presensi::where('tanggal', $today)
                ->whereNotNull('jam_masuk')
                ->count();
                
            $terlambat = Presensi::where('tanggal', $today)
                ->where('status_masuk', 'terlambat')
                ->count();
        }
            
        $belumHadir = max(0, $totalAktif - $hadir);

        return [
            'total_pkl' => $totalAktif,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'belum_hadir' => $belumHadir,
        ];
    }
}
