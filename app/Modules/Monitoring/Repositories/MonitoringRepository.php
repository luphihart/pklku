<?php

namespace App\Modules\Monitoring\Repositories;

use App\Modules\PKL\Models\PenempatanPkl;
use App\Modules\System\Models\AuditLog;

class MonitoringRepository implements MonitoringRepositoryInterface
{
    public function getActivePlacementsMap()
    {
        return PenempatanPkl::with(['murid.kelas', 'dudi', 'guru'])
            ->where('status', 'aktif')
            ->get();
    }

    public function getRecentActivities(int $limit = 20)
    {
        return AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
