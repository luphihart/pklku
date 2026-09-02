<?php

namespace App\Modules\Jurnal\Services;

use App\Modules\Jurnal\Repositories\JournalRepositoryInterface;

class JournalService
{
    protected $repo;

    public function __construct(JournalRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getStudentHistory(int $placementId) { return $this->repo->getStudentJournals($placementId); }
    public function getTeacherReviews(?int $guruId, $statusOrFilters = null, int $perPage = 15) { return $this->repo->getTeacherJournals($guruId, $statusOrFilters, $perPage); }
    public function getDetail(int $id) { return $this->repo->findById($id); }

    /**
     * Submit new daily journal entry.
     */
    public function saveEntry(int $placementId, array $data, $fotoFile = null)
    {
        $filename = null;
        if ($fotoFile) {
            $dirPath = public_path('storage/jurnal');
            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0755, true);
            }
            $filename = 'jurnal_' . $placementId . '_' . time() . '.' . $fotoFile->getClientOriginalExtension();
            $fotoFile->move($dirPath, $filename);
        }

        $journal = $this->repo->createJournal([
            'penempatan_pkl_id' => $placementId,
            'tanggal' => $data['tanggal'] ?? now()->toDateString(),
            'deskripsi_aktivitas' => $data['deskripsi_aktivitas'],
            'foto_kegiatan' => $filename,
            'status_verifikasi' => 'pending',
        ]);

        $this->logActivity("Menulis jurnal kegiatan harian baru, tanggal: " . $journal->tanggal);
        return $journal;
    }

    /**
     * Update journal entry (Only allowed if status is pending or revision).
     */
    public function editEntry(int $id, array $data, $fotoFile = null)
    {
        $journal = $this->repo->findById($id);

        if (!in_array($journal->status_verifikasi, ['pending', 'revisi'])) {
            throw new \Exception("Jurnal tidak dapat diubah karena sudah diverifikasi oleh Guru Pembimbing.");
        }

        $updateData = [
            'deskripsi_aktivitas' => $data['deskripsi_aktivitas'],
            'status_verifikasi' => 'pending', // Reset status to pending when modified
        ];

        if ($fotoFile) {
            $dirPath = public_path('storage/jurnal');
            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0755, true);
            }
            // Delete old photo
            if ($journal->foto_kegiatan && file_exists($dirPath . '/' . $journal->foto_kegiatan)) {
                @unlink($dirPath . '/' . $journal->foto_kegiatan);
            }

            $filename = 'jurnal_' . $journal->penempatan_pkl_id . '_' . time() . '.' . $fotoFile->getClientOriginalExtension();
            $fotoFile->move($dirPath, $filename);
            $updateData['foto_kegiatan'] = $filename;
        }

        $updated = $this->repo->updateJournal($id, $updateData);
        $this->logActivity("Mengubah jurnal kegiatan harian, tanggal: " . $journal->tanggal);
        return $updated;
    }

    /**
     * Review/Verify journal entry by Guru Pembimbing.
     */
    public function verifyEntry(int $id, ?int $guruId, string $status, ?string $catatan = null)
    {
        $journal = $this->repo->findById($id);
        
        $updated = $this->repo->updateJournal($id, [
            'status_verifikasi' => $status, // 'disetujui', 'ditolak', 'revisi'
            'catatan_verifikasi' => $catatan,
            'verified_by' => $guruId,
        ]);

        $this->logActivity("Memverifikasi jurnal ID: {$id} dengan status: {$status}");
        return $updated;
    }

    /**
     * Cancel verification / Reset status to pending.
     */
    public function cancelVerification(int $id)
    {
        $journal = $this->repo->findById($id);

        $updated = $this->repo->updateJournal($id, [
            'status_verifikasi' => 'pending',
            'catatan_verifikasi' => null,
            'verified_by' => null,
        ]);

        $this->logActivity("Membatalkan verifikasi jurnal ID: {$id} (kembali ke Pending)");
        return $updated;
    }

    private function logActivity(string $aktivitas, ?int $userId = null): void
    {
        $uId = $userId ?? \Illuminate\Support\Facades\Auth::id();
        try {
            \App\Modules\System\Models\AuditLog::create([
                'user_id' => $uId,
                'aktivitas' => $aktivitas,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Unknown',
                'payload' => null,
            ]);
        } catch (\Throwable $e) {}
    }
}
