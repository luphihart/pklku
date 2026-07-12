<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Dashboard\Repositories\DashboardRepositoryInterface;

class DashboardService
{
    protected $repo;

    public function __construct(DashboardRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getDashboardData(): array
    {
        $user = auth()->user();
        $announcements = \App\Modules\Pengumuman\Models\Pengumuman::where(function ($query) use ($user) {
            $query->where('target_role', 'semua')
                  ->orWhere('target_role', $user->role)
                  ->orWhereHas('penerima', function ($q) use ($user) {
                      $q->where('user_id', $user->id);
                  });
        })->orderBy('created_at', 'desc')->limit(5)->get();

        $placements = collect();
        $dudiList = [];
        $todayPresensi = collect();

        if (in_array($user->role, ['admin', 'guru'])) {
            $query = \App\Modules\PKL\Models\PenempatanPkl::with(['murid.kelas', 'dudi', 'guru'])
                ->where('status', 'aktif');

            if ($user->role === 'guru' && $user->guru) {
                $query->where('guru_id', $user->guru->id);
            }

            $placements = $query->get();

            $placementIds = $placements->pluck('id');
            $todayPresensi = \App\Modules\Presensi\Models\Presensi::whereIn('penempatan_pkl_id', $placementIds)
                ->where('tanggal', now()->toDateString())
                ->get();

            foreach ($placements as $p) {
                if ($p->dudi) {
                    if (!isset($dudiList[$p->dudi_id])) {
                        $dudiList[$p->dudi_id] = [
                            'dudi' => $p->dudi,
                            'placements' => []
                        ];
                    }
                    $dudiList[$p->dudi_id]['placements'][] = $p;
                }
            }
        }

        return [
            'counts' => $this->repo->getCounts(),
            'attendance' => $this->repo->getAttendanceStatsToday(),
            'announcements' => $announcements,
            'placements' => $placements,
            'dudiList' => $dudiList,
            'todayPresensi' => $todayPresensi,
        ];
    }
}
