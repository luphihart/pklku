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
            $query = \App\Modules\PKL\Models\PenempatanPkl::select(['id', 'murid_id', 'dudi_id', 'guru_id', 'status'])
                ->with([
                    'murid:id,nama,nis,kelas_id',
                    'murid.kelas:id,nama',
                    'dudi:id,nama,alamat,latitude,longitude,pic_nama,pic_phone',
                    'guru:id,nama'
                ])
                ->where('status', 'aktif');

            if ($user->role === 'guru' && $user->guru) {
                $query->where('guru_id', $user->guru->id);
            }

            $placements = $query->get();

            $placementIds = $placements->pluck('id');
            $today = now()->toDateString();
            $todayPresensi = \App\Modules\Presensi\Models\Presensi::whereIn('penempatan_pkl_id', $placementIds)
                ->where('tanggal', $today)
                ->select(['id', 'penempatan_pkl_id', 'tanggal', 'jam_masuk', 'jam_pulang', 'status_masuk', 'lat_masuk', 'lng_masuk', 'lat_pulang', 'lng_pulang'])
                ->get()
                ->keyBy('penempatan_pkl_id');

            $todayLeaves = \App\Modules\Presensi\Models\IzinSakit::whereIn('penempatan_pkl_id', $placementIds)
                ->where('status_approval', 'disetujui')
                ->where('tanggal_mulai', '<=', $today)
                ->where('tanggal_selesai', '>=', $today)
                ->select(['id', 'penempatan_pkl_id', 'tipe', 'alasan'])
                ->get()
                ->keyBy('penempatan_pkl_id');

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

            // Sort DUDI list alphabetically by DUDI name (case-insensitive)
            uasort($dudiList, function ($a, $b) {
                return strcasecmp($a['dudi']->nama ?? '', $b['dudi']->nama ?? '');
            });

            // Sort students within each DUDI alphabetically
            foreach ($dudiList as &$dItem) {
                usort($dItem['placements'], function ($a, $b) {
                    return strcasecmp($a->murid?->nama ?? '', $b->murid?->nama ?? '');
                });
            }
            unset($dItem);
        } elseif ($user->role === 'industri') {
            // Pembimbing Industri: hanya data murid aktif di DUDInya
            $pembimbing = $user->pembimbingIndustri;
            $dudiId = $pembimbing?->dudi_id;
            $todayLeaves = collect();

            if ($dudiId) {
                $placements = \App\Modules\PKL\Models\PenempatanPkl::select(['id', 'murid_id', 'dudi_id', 'guru_id', 'status'])
                    ->with([
                        'murid:id,nama,nis,kelas_id',
                        'murid.kelas:id,nama',
                        'dudi:id,nama,alamat,latitude,longitude,pic_nama,pic_phone',
                        'guru:id,nama'
                    ])
                    ->where('status', 'aktif')
                    ->where('dudi_id', $dudiId)
                    ->get();

                $placementIds = $placements->pluck('id');
                $today = now()->toDateString();
                $todayPresensi = \App\Modules\Presensi\Models\Presensi::whereIn('penempatan_pkl_id', $placementIds)
                    ->where('tanggal', $today)
                    ->select(['id', 'penempatan_pkl_id', 'tanggal', 'jam_masuk', 'jam_pulang', 'status_masuk', 'lat_masuk', 'lng_masuk', 'lat_pulang', 'lng_pulang'])
                    ->get()
                    ->keyBy('penempatan_pkl_id');

                $todayLeaves = \App\Modules\Presensi\Models\IzinSakit::whereIn('penempatan_pkl_id', $placementIds)
                    ->where('status_approval', 'disetujui')
                    ->where('tanggal_mulai', '<=', $today)
                    ->where('tanggal_selesai', '>=', $today)
                    ->select(['id', 'penempatan_pkl_id', 'tipe', 'alasan'])
                    ->get()
                    ->keyBy('penempatan_pkl_id');

                if ($placements->count() > 0 && $placements->first()?->dudi) {
                    $dudi = $placements->first()->dudi;
                    $dudiList[$dudi->id] = ['dudi' => $dudi, 'placements' => $placements->all()];
                }
            } else {
                $todayPresensi = collect();
            }
        } else {
            $todayLeaves = collect();
        }

        return [
            'counts' => $this->repo->getCounts(),
            'attendance' => $this->repo->getAttendanceStatsToday(),
            'announcements' => $announcements,
            'placements' => $placements,
            'dudiList' => $dudiList,
            'todayPresensi' => $todayPresensi,
            'todayLeaves' => $todayLeaves,
        ];
    }
}
