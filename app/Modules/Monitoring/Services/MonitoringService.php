<?php

namespace App\Modules\Monitoring\Services;

use App\Modules\Monitoring\Repositories\MonitoringRepositoryInterface;

class MonitoringService
{
    protected $repo;

    public function __construct(MonitoringRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Get active student locations at DUDIs.
     */
    public function getActiveLocations()
    {
        return $this->repo->getActivePlacementsMap();
    }

    /**
     * Get recent activities as notification feed.
     */
    public function getNotificationsFeed()
    {
        return $this->repo->getRecentActivities(25);
    }
}
