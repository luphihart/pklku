<?php

namespace App\Modules\Pengumuman\Repositories;

interface AnnouncementRepositoryInterface
{
    public function getActiveAnnouncementsForUser(int $userId, string $role);
    public function getAllAnnouncements();
    public function createAnnouncement(array $data);
    public function deleteAnnouncement(int $id);
}
