<?php

namespace App\Modules\Pengumuman\Services;

use App\Modules\Pengumuman\Repositories\AnnouncementRepositoryInterface;
use App\Modules\Pengumuman\Models\PengumumanPenerima;
use Illuminate\Support\Facades\Auth;

class AnnouncementService
{
    protected $repo;

    public function __construct(AnnouncementRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getUserAnnouncements()
    {
        $user = Auth::user();
        return $this->repo->getActiveAnnouncementsForUser($user->id, $user->role);
    }

    public function listAll()
    {
        return $this->repo->getAllAnnouncements();
    }

    public function create(array $data, array $customUserIds = [])
    {
        $announcement = $this->repo->createAnnouncement([
            'judul' => $data['judul'],
            'isi' => $data['isi'],
            'target_role' => $data['target_role'],
        ]);

        if ($data['target_role'] === 'kustom' && !empty($customUserIds)) {
            foreach ($customUserIds as $userId) {
                PengumumanPenerima::create([
                    'pengumuman_id' => $announcement->id,
                    'user_id' => $userId,
                ]);
            }
        }

        $this->logActivity("Membuat pengumuman baru: " . $announcement->judul);
        return $announcement;
    }

    public function update(int $id, array $data, array $customUserIds = [])
    {
        $announcement = \App\Modules\Pengumuman\Models\Pengumuman::findOrFail($id);
        $announcement->update([
            'judul' => $data['judul'],
            'isi' => $data['isi'],
            'target_role' => $data['target_role'],
        ]);

        // Wipe old recipients
        PengumumanPenerima::where('pengumuman_id', $id)->delete();

        if ($data['target_role'] === 'kustom' && !empty($customUserIds)) {
            foreach ($customUserIds as $userId) {
                PengumumanPenerima::create([
                    'pengumuman_id' => $announcement->id,
                    'user_id' => $userId,
                ]);
            }
        }

        $this->logActivity("Mengubah pengumuman: " . $announcement->judul);
        return $announcement;
    }

    public function remove(int $id)
    {
        $this->repo->deleteAnnouncement($id);
        $this->logActivity("Menghapus pengumuman ID: " . $id);
    }

    /**
     * Audit log helper.
     */
    private function logActivity(string $aktivitas)
    {
        try {
            \App\Modules\System\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'aktivitas' => $aktivitas,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Unknown',
                'payload' => null,
            ]);
        } catch (\Throwable $e) {
            // Ignore
        }
    }
}
