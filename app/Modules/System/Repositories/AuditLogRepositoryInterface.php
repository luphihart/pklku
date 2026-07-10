<?php

namespace App\Modules\System\Repositories;

interface AuditLogRepositoryInterface
{
    public function getPaginatedLogs(int $perPage = 25);
    public function clearLogs();
}
