<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Auth Repository
        $this->app->bind(
            \App\Modules\Auth\Repositories\UserRepositoryInterface::class,
            \App\Modules\Auth\Repositories\UserRepository::class
        );

        // Bind MasterData Repository
        $this->app->bind(
            \App\Modules\MasterData\Repositories\MasterDataRepositoryInterface::class,
            \App\Modules\MasterData\Repositories\MasterDataRepository::class
        );

        // Bind Dashboard Repository
        $this->app->bind(
            \App\Modules\Dashboard\Repositories\DashboardRepositoryInterface::class,
            \App\Modules\Dashboard\Repositories\DashboardRepository::class
        );

        // Bind Presensi (Attendance) Repository
        $this->app->bind(
            \App\Modules\Presensi\Repositories\AttendanceRepositoryInterface::class,
            \App\Modules\Presensi\Repositories\AttendanceRepository::class
        );

        // Bind Jurnal Repository
        $this->app->bind(
            \App\Modules\Jurnal\Repositories\JournalRepositoryInterface::class,
            \App\Modules\Jurnal\Repositories\JournalRepository::class
        );

        // Bind PKL (Placement) Repository
        $this->app->bind(
            \App\Modules\PKL\Repositories\PlacementRepositoryInterface::class,
            \App\Modules\PKL\Repositories\PlacementRepository::class
        );

        // Bind Pengumuman Repository
        $this->app->bind(
            \App\Modules\Pengumuman\Repositories\AnnouncementRepositoryInterface::class,
            \App\Modules\Pengumuman\Repositories\AnnouncementRepository::class
        );

        // Bind Penilaian Repository
        $this->app->bind(
            \App\Modules\Penilaian\Repositories\EvaluationRepositoryInterface::class,
            \App\Modules\Penilaian\Repositories\EvaluationRepository::class
        );

        // Bind Setting Repository
        $this->app->bind(
            \App\Modules\Setting\Repositories\SettingRepositoryInterface::class,
            \App\Modules\Setting\Repositories\SettingRepository::class
        );

        // Bind System (AuditLog) Repository
        $this->app->bind(
            \App\Modules\System\Repositories\AuditLogRepositoryInterface::class,
            \App\Modules\System\Repositories\AuditLogRepository::class
        );

        // Bind Monitoring Repository
        $this->app->bind(
            \App\Modules\Monitoring\Repositories\MonitoringRepositoryInterface::class,
            \App\Modules\Monitoring\Repositories\MonitoringRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
