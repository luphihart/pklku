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

    public function getTeacherJournals(?int $guruId, ?string $status = null)
    {
        $query = Jurnal::with(['penempatanPkl.murid.kelas', 'penempatanPkl.dudi']);
        
        if ($guruId) {
            $query->whereHas('penempatanPkl', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            });
        }

        if ($status) {
            $query->where('status_verifikasi', $status);
        }

        return $query->orderBy('tanggal', 'desc')->paginate(15);
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
