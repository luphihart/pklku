<?php

namespace App\Modules\System\Repositories;

use App\Modules\System\Models\AuditLog;
use Carbon\Carbon;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    protected function applyFilters($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('aktivitas', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['role'])) {
            $role = $filters['role'];
            if ($role === 'system') {
                $query->whereNull('user_id');
            } else {
                $query->whereHas('user', function($u) use ($role) {
                    $u->where('role', $role);
                });
            }
        }

        if (!empty($filters['kategori'])) {
            $kat = $filters['kategori'];
            if ($kat === 'keamanan') {
                $query->where(function($q) {
                    $q->where('aktivitas', 'like', '%Login%')
                      ->orWhere('aktivitas', 'like', '%Logout%')
                      ->orWhere('aktivitas', 'like', '%Password%')
                      ->orWhere('aktivitas', 'like', '%masuk%')
                      ->orWhere('aktivitas', 'like', '%keluar%');
                });
            } elseif ($kat === 'jurnal') {
                $query->where('aktivitas', 'like', '%jurnal%');
            } elseif ($kat === 'penilaian') {
                $query->where(function($q) {
                    $q->where('aktivitas', 'like', '%penilaian%')
                      ->orWhere('aktivitas', 'like', '%nilai%');
                });
            } elseif ($kat === 'master') {
                $query->where(function($q) {
                    $q->where('aktivitas', 'like', '%murid%')
                      ->orWhere('aktivitas', 'like', '%siswa%')
                      ->orWhere('aktivitas', 'like', '%guru%')
                      ->orWhere('aktivitas', 'like', '%dudi%')
                      ->orWhere('aktivitas', 'like', '%industri%')
                      ->orWhere('aktivitas', 'like', '%penempatan%');
                });
            } elseif ($kat === 'setting') {
                $query->where(function($q) {
                    $q->where('aktivitas', 'like', '%pengaturan%')
                      ->orWhere('aktivitas', 'like', '%konfigurasi%')
                      ->orWhere('aktivitas', 'like', '%branding%');
                });
            }
        }

        if (!empty($filters['tanggal_mulai'])) {
            $query->whereDate('created_at', '>=', $filters['tanggal_mulai']);
        }

        if (!empty($filters['tanggal_selesai'])) {
            $query->whereDate('created_at', '<=', $filters['tanggal_selesai']);
        }

        return $query;
    }

    public function getPaginatedLogs(array $filters = [], int $perPage = 25)
    {
        $query = AuditLog::with('user');
        $query = $this->applyFilters($query, $filters);
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getFilteredLogs(array $filters = [])
    {
        $query = AuditLog::with('user');
        $query = $this->applyFilters($query, $filters);
        return $query->orderBy('created_at', 'desc')->get();
    }

    public function clearLogs(?int $olderThanDays = null)
    {
        if ($olderThanDays && $olderThanDays > 0) {
            $cutoffDate = Carbon::now()->subDays($olderThanDays);
            return AuditLog::where('created_at', '<', $cutoffDate)->delete();
        }
        return AuditLog::truncate();
    }
}
