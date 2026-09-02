<?php

namespace App\Modules\Dashboard\Repositories;

use App\Models\User;
use App\Modules\MasterData\Models\Murid;
use App\Modules\MasterData\Models\Guru;
use App\Modules\MasterData\Models\Dudi;
use App\Modules\PKL\Models\PenempatanPkl;
use App\Modules\Presensi\Models\Presensi;
use Carbon\Carbon;

use Illuminate\Support\Facades\Cache;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getCounts(): array
    {
        $user = auth()->user();
        $cacheKey = 'dashboard_counts_' . ($user && $user->role === 'guru' ? 'guru_' . ($user->guru?->id ?? 0) : 'admin');

        return Cache::remember($cacheKey, now()->addMinutes(5), function() use ($user) {
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
        });
    }

    public function getAttendanceStatsToday(): array
    {
        $today = Carbon::today()->toDateString();
        $user = auth()->user();
        $cacheKey = 'dashboard_attendance_stats_v3_' . $today . '_' . ($user && $user->role === 'guru' ? 'guru_' . ($user->guru?->id ?? 0) : 'admin');

        return Cache::remember($cacheKey, now()->addSeconds(30), function() use ($user, $today) {
            if ($user && $user->role === 'guru') {
                $guruId = $user->guru ? $user->guru->id : 0;
                
                $placementIds = PenempatanPkl::where('guru_id', $guruId)->where('status', 'aktif')->pluck('id');
            } else {
                $placementIds = PenempatanPkl::where('status', 'aktif')->pluck('id');
            }

            $totalAktif = $placementIds->count();

            $hadirTepatWaktu = Presensi::where('tanggal', $today)
                ->where('status_masuk', 'tepat_waktu')
                ->whereIn('penempatan_pkl_id', $placementIds)
                ->count();
                
            $terlambat = Presensi::where('tanggal', $today)
                ->where('status_masuk', 'terlambat')
                ->whereIn('penempatan_pkl_id', $placementIds)
                ->count();

            $liburShift = Presensi::where('tanggal', $today)
                ->where('status_masuk', 'libur_shift')
                ->whereIn('penempatan_pkl_id', $placementIds)
                ->count();

            $alpha = Presensi::where('tanggal', $today)
                ->where('status_masuk', 'alpha')
                ->whereIn('penempatan_pkl_id', $placementIds)
                ->count();

            $izin = \App\Modules\Presensi\Models\IzinSakit::where('status_approval', 'disetujui')
                ->where('tipe', 'izin')
                ->where('tanggal_mulai', '<=', $today)
                ->where('tanggal_selesai', '>=', $today)
                ->whereIn('penempatan_pkl_id', $placementIds)
                ->count();

            $sakit = \App\Modules\Presensi\Models\IzinSakit::where('status_approval', 'disetujui')
                ->where('tipe', 'sakit')
                ->where('tanggal_mulai', '<=', $today)
                ->where('tanggal_selesai', '>=', $today)
                ->whereIn('penempatan_pkl_id', $placementIds)
                ->count();

            $totalHadir = $hadirTepatWaktu + $terlambat;
            $belumAbsen = max(0, $totalAktif - ($totalHadir + $liburShift + $alpha + $izin + $sakit));

            return [
                'total_pkl' => $totalAktif,
                'hadir' => $totalHadir,
                'tepat_waktu' => $hadirTepatWaktu,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'libur_shift' => $liburShift,
                'alpha' => $alpha,
                'belum_hadir' => $belumAbsen,
            ];
        });
    }
}
