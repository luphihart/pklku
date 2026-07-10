<?php

namespace App\Modules\Pengumuman\Repositories;

use App\Modules\Pengumuman\Models\Pengumuman;
use App\Modules\Pengumuman\Models\PengumumanPenerima;

class AnnouncementRepository implements AnnouncementRepositoryInterface
{
    public function getActiveAnnouncementsForUser(int $userId, string $role)
    {
        return Pengumuman::where(function ($query) use ($role, $userId) {
            $query->where('target_role', 'semua')
                  ->orWhere('target_role', $role)
                  ->orWhereHas('penerima', function ($q) use ($userId) {
                      $q->where('user_id', $userId);
                  });
        })->orderBy('created_at', 'desc')->get();
    }

    public function getAllAnnouncements()
    {
        return Pengumuman::orderBy('created_at', 'desc')->paginate(15);
    }

    public function createAnnouncement(array $data)
    {
        return Pengumuman::create($data);
    }

    public function deleteAnnouncement(int $id)
    {
        $announcement = Pengumuman::findOrFail($id);
        return $announcement->delete();
    }
}
