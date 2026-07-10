<?php

namespace App\Modules\MasterData\Repositories;

interface MasterDataRepositoryInterface
{
    // Murid methods
    public function getAllMurid(array $filters = []);
    public function findMuridById(int $id);
    public function createMurid(array $data);
    public function updateMurid(int $id, array $data);
    public function deleteMurid(int $id);

    // Guru methods
    public function getAllGuru();
    public function findGuruById(int $id);
    public function createGuru(array $data);
    public function updateGuru(int $id, array $data);
    public function deleteGuru(int $id);

    // Dudi methods
    public function getAllDudi();
    public function findDudiById(int $id);
    public function createDudi(array $data);
    public function updateDudi(int $id, array $data);
    public function deleteDudi(int $id);
}
