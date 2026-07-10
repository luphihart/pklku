<?php

namespace App\Modules\Jurnal\Repositories;

interface JournalRepositoryInterface
{
    public function getStudentJournals(int $placementId);
    public function getTeacherJournals(?int $guruId, ?string $status = null);
    public function findById(int $id);
    public function createJournal(array $data);
    public function updateJournal(int $id, array $data);
}
