<?php

namespace App\Modules\System\Repositories;

interface AuditLogRepositoryInterface
{
    public function getPaginatedLogs(array $filters = [], int $perPage = 25);
    public function getFilteredLogs(array $filters = []);
    public function clearLogs(?int $olderThanDays = null);
}
