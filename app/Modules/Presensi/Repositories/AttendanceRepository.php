<?php

namespace App\Modules\Presensi\Repositories;

use App\Modules\Presensi\Models\Presensi;
use App\Modules\Presensi\Models\IzinSakit;
use Carbon\Carbon;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function getStudentAttendanceHistory(int $placementId)
    {
        $presensis = Presensi::where('penempatan_pkl_id', $placementId)->get();

        $leaves = IzinSakit::where('penempatan_pkl_id', $placementId)
            ->where('status_approval', 'disetujui')
            ->get();

        $combined = collect();

        foreach ($presensis as $p) {
            $type = 'hadir';
            if ($p->status_masuk === 'libur_shift') {
                $type = 'libur_shift';
            } elseif ($p->status_masuk === 'alpha') {
                $type = 'alpha';
            }

            $combined->push((object)[
                'id' => $p->id,
                'type' => $type,
                'tanggal' => $p->tanggal,
                'jam_masuk' => $p->jam_masuk,
                'jam_pulang' => $p->jam_pulang,
                'foto_masuk' => $p->foto_masuk,
                'foto_pulang' => $p->foto_pulang,
                'status_masuk' => $p->status_masuk,
                'status_pulang' => $p->status_pulang,
                'shift_harian' => $p->shift_harian ?? null,
                'is_wfa' => (bool) ($p->is_wfa ?? false),
                'keterangan' => $p->keterangan ?? ($p->status_masuk === 'libur_shift' ? 'Libur Shift DUDI' : null),
                'surat_pendukung' => null,
            ]);
        }

        foreach ($leaves as $l) {
            $start = Carbon::parse($l->tanggal_mulai);
            $end = Carbon::parse($l->tanggal_selesai);
            $curr = $start->copy();
            while ($curr->lessThanOrEqualTo($end)) {
                $dateStr = $curr->toDateString();
                if (!$combined->firstWhere('tanggal', $dateStr)) {
                    $combined->push((object)[
                        'id' => 'leave_' . $l->id . '_' . $dateStr,
                        'type' => $l->tipe, // 'izin' or 'sakit'
                        'tanggal' => $dateStr,
                        'jam_masuk' => null,
                        'jam_pulang' => null,
                        'foto_masuk' => null,
                        'foto_pulang' => null,
                        'status_masuk' => $l->tipe,
                        'status_pulang' => null,
                        'shift_harian' => null,
                        'keterangan' => $l->alasan,
                        'surat_pendukung' => $l->surat_pendukung,
                    ]);
                }
                $curr->addDay();
            }
        }

        return $combined->sortByDesc('tanggal')->values();
    }

    public function getTodayAttendance(int $placementId)
    {
        $today = Carbon::today()->toDateString();
        return Presensi::where('penempatan_pkl_id', $placementId)
            ->where('tanggal', $today)
            ->first();
    }

    public function getTodayApprovedLeave(int $placementId)
    {
        $today = Carbon::today()->toDateString();
        return IzinSakit::where('penempatan_pkl_id', $placementId)
            ->where('status_approval', 'disetujui')
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->first();
    }

    public function saveAttendance(array $data)
    {
        return Presensi::create($data);
    }

    public function updateAttendance(int $id, array $data)
    {
        $attendance = Presensi::findOrFail($id);
        $attendance->update($data);
        return $attendance;
    }

    public function getPermissionHistory(int $placementId)
    {
        return IzinSakit::where('penempatan_pkl_id', $placementId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function savePermission(array $data)
    {
        return IzinSakit::create($data);
    }
}
