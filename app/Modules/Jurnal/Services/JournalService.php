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
    public function getStatusCounts(?int $guruId, $filters = []) { return $this->repo->getStatusCounts($guruId, $filters); }
    public function getDetail(int $id) { return $this->repo->findById($id); }
    public function getIndustriReviews(?int $dudiId, $filters = [], int $perPage = 15) { return $this->repo->getIndustriJournals($dudiId, $filters, $perPage); }
    public function getIndustriStatusCounts(?int $dudiId, $filters = []) { return $this->repo->getIndustriStatusCounts($dudiId, $filters); }

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
            $ext = strtolower($fotoFile->getClientOriginalExtension()) ?: 'jpg';
            if ($ext === 'jpeg') $ext = 'jpg';
            $filename = 'jurnal_' . $placementId . '_' . time() . '.' . $ext;
            $outputPath = $dirPath . '/' . $filename;
            $this->processUploadedImage($fotoFile, $outputPath);
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
            'tanggal' => $data['tanggal'] ?? $journal->tanggal,
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

            $ext = strtolower($fotoFile->getClientOriginalExtension()) ?: 'jpg';
            if ($ext === 'jpeg') $ext = 'jpg';
            $filename = 'jurnal_' . $journal->penempatan_pkl_id . '_' . time() . '.' . $ext;
            $outputPath = $dirPath . '/' . $filename;
            $this->processUploadedImage($fotoFile, $outputPath);
            $updateData['foto_kegiatan'] = $filename;
        }

        $updated = $this->repo->updateJournal($id, $updateData);
        $this->logActivity("Mengubah jurnal kegiatan harian, tanggal: " . ($updateData['tanggal'] ?? $journal->tanggal));
        return $updated;
    }

    /**
     * Helper to process, resize, and compress uploaded journal photos.
     */
    private function processUploadedImage($file, string $outputPath): void
    {
        $tempFile = $file->getRealPath();

        try {
            if (class_exists(\Intervention\Image\ImageManager::class)) {
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $image = $manager->read($tempFile);
                $image->scale(width: 800);
                $ext = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'webp'])) {
                    $image->save($outputPath);
                } else {
                    $image->toJpeg(75)->save($outputPath);
                }
            } else {
                $this->compressImageNative($tempFile, $outputPath, 800, 75);
            }
        } catch (\Throwable $e) {
            // ultimate fallback: save directly
            $file->move(dirname($outputPath), basename($outputPath));
        }
    }

    private function compressImageNative(string $sourcePath, string $destPath, int $maxWidth, int $quality): void
    {
        $imgInfo = @getimagesize($sourcePath);
        if (!$imgInfo) {
            copy($sourcePath, $destPath);
            return;
        }

        list($origWidth, $origHeight, $type) = $imgInfo;

        $width = $origWidth;
        $height = $origHeight;

        if ($origWidth > $maxWidth && $origWidth > 0) {
            $width = $maxWidth;
            $height = (int)($origHeight * ($maxWidth / $origWidth));
        }

        $srcImg = null;
        switch ($type) {
            case IMAGETYPE_JPEG: $srcImg = @imagecreatefromjpeg($sourcePath); break;
            case IMAGETYPE_PNG: $srcImg = @imagecreatefrompng($sourcePath); break;
            case IMAGETYPE_WEBP: $srcImg = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : null; break;
            default: $srcImg = @imagecreatefromjpeg($sourcePath); break;
        }

        if (!$srcImg) {
            copy($sourcePath, $destPath);
            return;
        }

        $destImg = imagecreatetruecolor($width, $height);
        if (!$destImg) {
            imagedestroy($srcImg);
            copy($sourcePath, $destPath);
            return;
        }

        // Handle transparency for PNG / WEBP
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
            imagealphablending($destImg, false);
            imagesavealpha($destImg, true);
        }

        imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

        if ($type == IMAGETYPE_PNG) {
            imagepng($destImg, $destPath, 8);
        } elseif ($type == IMAGETYPE_WEBP && function_exists('imagewebp')) {
            imagewebp($destImg, $destPath, $quality);
        } else {
            imagejpeg($destImg, $destPath, $quality);
        }

        imagedestroy($srcImg);
        imagedestroy($destImg);
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

    /**
     * Bulk verify / review multiple journal entries.
     */
    public function bulkVerifyEntries(array $journalIds, ?int $guruId, string $status, ?string $catatan = null): int
    {
        if (empty($journalIds)) {
            return 0;
        }

        if ($status === 'pending') {
            $updateData = [
                'status_verifikasi' => 'pending',
                'catatan_verifikasi' => null,
                'verified_by' => null,
            ];
            $affected = $this->repo->bulkUpdateStatus($journalIds, $guruId, $updateData);
            $this->logActivity("Mengembalikan status verifikasi massal {$affected} jurnal ke Menunggu (Pending)");
            return $affected;
        }

        $updateData = [
            'status_verifikasi' => $status,
            'catatan_verifikasi' => $catatan,
            'verified_by' => $guruId,
        ];

        $affected = $this->repo->bulkUpdateStatus($journalIds, $guruId, $updateData);
        $this->logActivity("Memverifikasi massal {$affected} jurnal dengan status: {$status}");
        return $affected;
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
