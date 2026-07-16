<?php

namespace App\Modules\Presensi\Services;

use App\Modules\Presensi\Repositories\AttendanceRepositoryInterface;
use App\Modules\Presensi\Models\IzinSakit;
use Illuminate\Support\Facades\Auth;

class PermissionService
{
    protected $repo;

    public function __construct(AttendanceRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getHistory(int $placementId)
    {
        return $this->repo->getPermissionHistory($placementId);
    }

    /**
     * Submit new leave/sick permission request.
     */
    public function apply(int $placementId, array $data, $file = null)
    {
        $filename = null;
        if ($file) {
            $filename = 'surat_izin_' . $placementId . '_' . time() . '.jpg';
            $outputPath = public_path('storage/izin/' . $filename);
            if (!file_exists(public_path('storage/izin'))) {
                mkdir(public_path('storage/izin'), 0777, true);
            }
            $this->processUploadedFile($file, $outputPath);
        }

        return $this->repo->savePermission([
            'penempatan_pkl_id' => $placementId,
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'tipe' => $data['tipe'], // 'izin' or 'sakit'
            'alasan' => $data['alasan'],
            'surat_pendukung' => $filename,
            'status_approval' => 'pending',
        ]);
    }

    /**
     * Guru Pembimbing reviews and approves/rejects the request.
     */
    public function review(int $id, ?int $guruId, string $status, ?string $catatan = null)
    {
        $permission = IzinSakit::findOrFail($id);
        
        $permission->update([
            'status_approval' => $status, // 'disetujui' or 'ditolak'
            'approved_by' => $guruId,
            'catatan_guru' => $catatan,
        ]);

        return $permission;
    }

    /**
     * Revise/Update an existing leave/sick permission request.
     */
    public function revise(int $id, array $data, $file = null)
    {
        $permission = IzinSakit::findOrFail($id);

        if (!in_array($permission->status_approval, ['pending', 'ditolak'])) {
            throw new \Exception("Hanya pengajuan berstatus pending atau ditolak yang dapat direvisi.");
        }

        $filename = $permission->surat_pendukung;
        if ($file) {
            if ($filename && file_exists(public_path('storage/izin/' . $filename))) {
                @unlink(public_path('storage/izin/' . $filename));
            }
            $filename = 'surat_izin_' . $permission->penempatan_pkl_id . '_' . time() . '.jpg';
            $outputPath = public_path('storage/izin/' . $filename);
            if (!file_exists(public_path('storage/izin'))) {
                mkdir(public_path('storage/izin'), 0777, true);
            }
            $this->processUploadedFile($file, $outputPath);
        }

        $permission->update([
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'tipe' => $data['tipe'],
            'alasan' => $data['alasan'],
            'surat_pendukung' => $filename,
            'status_approval' => 'pending',
            'catatan_guru' => null,
            'approved_by' => null,
        ]);

        return $permission;
    }

    private function processUploadedFile($file, string $outputPath): void
    {
        $tempFile = $file->getRealPath();
        
        try {
            if (class_exists(\Intervention\Image\ImageManager::class)) {
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $image = $manager->read($tempFile);
                $image->scale(width: 800);
                $image->toJpeg(75)->save($outputPath);
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
        list($origWidth, $origHeight, $type) = getimagesize($sourcePath);
        
        $width = $origWidth;
        $height = $origHeight;

        if ($origWidth > $maxWidth) {
            $width = $maxWidth;
            $height = (int)($origHeight * ($maxWidth / $origWidth));
        }

        switch ($type) {
            case IMAGETYPE_JPEG: $srcImg = imagecreatefromjpeg($sourcePath); break;
            case IMAGETYPE_PNG: $srcImg = imagecreatefrompng($sourcePath); break;
            default: $srcImg = imagecreatefromjpeg($sourcePath); break;
        }

        $destImg = imagecreatetruecolor($width, $height);
        
        // Handle transparency
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($destImg, false);
            imagesavealpha($destImg, true);
        }

        imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);
        imagejpeg($destImg, $destPath, $quality);

        imagedestroy($srcImg);
        imagedestroy($destImg);
    }
}
