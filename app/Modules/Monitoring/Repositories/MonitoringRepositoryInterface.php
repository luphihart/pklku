<?php

namespace App\Modules\Monitoring\Repositories;

interface MonitoringRepositoryInterface
{
    public function getActivePlacementsMap();
    public function getRecentActivities(int $limit = 20);
}
