<?php

namespace App\Modules\Presensi\Repositories;

use App\Modules\Presensi\Models\Presensi;
use App\Modules\Presensi\Models\IzinSakit;
use Carbon\Carbon;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function getStudentAttendanceHistory(int $placementId)
    {
        return Presensi::where('penempatan_pkl_id', $placementId)
            ->orderBy('tanggal', 'desc')
            ->paginate(15);
    }

    public function getTodayAttendance(int $placementId)
    {
        $today = Carbon::today()->toDateString();
        return Presensi::where('penempatan_pkl_id', $placementId)
            ->where('tanggal', $today)
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
