<?php

namespace App\Modules\Presensi\Repositories;

interface AttendanceRepositoryInterface
{
    public function getStudentAttendanceHistory(int $placementId);
    public function getTodayAttendance(int $placementId);
    public function saveAttendance(array $data);
    public function updateAttendance(int $id, array $data);
    
    // Izin & Sakit
    public function getPermissionHistory(int $placementId);
    public function savePermission(array $data);
}
