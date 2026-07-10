<?php

namespace App\Modules\Dashboard\Repositories;

interface DashboardRepositoryInterface
{
    public function getCounts(): array;
    public function getAttendanceStatsToday(): array;
}
