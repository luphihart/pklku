<?php

namespace App\Modules\Jurnal\Repositories;

use App\Modules\Jurnal\Models\Jurnal;

class JournalRepository implements JournalRepositoryInterface
{
    public function getStudentJournals(int $placementId)
    {
        return Jurnal::where('penempatan_pkl_id', $placementId)
            ->orderBy('tanggal', 'desc')
            ->paginate(15);
    }

    public function getTeacherJournals(?int $guruId, $statusOrFilters = null, int $perPage = 15)
    {
        $query = Jurnal::with(['penempatanPkl.murid.kelas', 'penempatanPkl.dudi']);
        
        if ($guruId) {
            $query->whereHas('penempatanPkl', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            });
        }

        $filters = is_array($statusOrFilters) ? $statusOrFilters : (is_string($statusOrFilters) ? ['status' => $statusOrFilters] : []);

        if (!empty($filters['status'])) {
            $query->where('status_verifikasi', $filters['status']);
        }

        if (!empty($filters['kelas_id'])) {
            $kelasId = $filters['kelas_id'];
            $query->whereHas('penempatanPkl.murid', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        $search = trim($filters['search'] ?? ($filters['nama'] ?? ''));
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('deskripsi_aktivitas', 'like', "%{$search}%")
                  ->orWhereHas('penempatanPkl.murid', function($m) use ($search) {
                      $m->where('nama', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                  })
                  ->orWhereHas('penempatanPkl.dudi', function($d) use ($search) {
                      $d->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['tanggal_mulai'])) {
            $query->whereDate('tanggal', '>=', $filters['tanggal_mulai']);
        }

        if (!empty($filters['tanggal_selesai'])) {
            $query->whereDate('tanggal', '<=', $filters['tanggal_selesai']);
        }

        if (!empty($filters['tanggal'])) {
            $query->whereDate('tanggal', $filters['tanggal']);
        }

        return $query->orderBy('tanggal', 'desc')->paginate($perPage);
    }

    public function findById(int $id)
    {
        return Jurnal::with(['penempatanPkl.murid', 'penempatanPkl.dudi'])->findOrFail($id);
    }

    public function createJournal(array $data)
    {
        return Jurnal::create($data);
    }

    public function updateJournal(int $id, array $data)
    {
        $journal = Jurnal::findOrFail($id);
        $journal->update($data);
        return $journal;
    }
}
