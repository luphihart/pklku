<?php

namespace App\Modules\System\Repositories;

use App\Modules\System\Models\AuditLog;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    public function getPaginatedLogs(int $perPage = 25)
    {
        return AuditLog::with('user')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function clearLogs()
    {
        return AuditLog::truncate();
    }
}
