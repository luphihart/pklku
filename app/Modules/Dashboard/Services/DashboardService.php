<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Dashboard\Repositories\DashboardRepositoryInterface;

class DashboardService
{
    protected $repo;

    public function __construct(DashboardRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getDashboardData(): array
    {
        $user = auth()->user();
        $announcements = \App\Modules\Pengumuman\Models\Pengumuman::where(function ($query) use ($user) {
            $query->where('target_role', 'semua')
                  ->orWhere('target_role', $user->role)
                  ->orWhereHas('penerima', function ($q) use ($user) {
                      $q->where('user_id', $user->id);
                  });
        })->orderBy('created_at', 'desc')->limit(5)->get();

        return [
            'counts' => $this->repo->getCounts(),
            'attendance' => $this->repo->getAttendanceStatsToday(),
            'announcements' => $announcements,
        ];
    }
}
