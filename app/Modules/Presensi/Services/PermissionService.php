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
            $filename = 'surat_izin_' . $placementId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/izin'), $filename);
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
            $filename = 'surat_izin_' . $permission->penempatan_pkl_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/izin'), $filename);
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
}
