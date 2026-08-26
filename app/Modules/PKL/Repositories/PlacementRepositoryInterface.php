<?php

namespace App\Modules\PKL\Repositories;

interface PlacementRepositoryInterface
{
    public function getActivePlacements(array $filters = []);
    public function findById(int $id);
    public function createMassPlacement(array $muridIds, int $dudiId, int $guruId, ?int $pembimbingIndustriId, string $tglMulai, string $tglSelesai, string $tipeKerja = 'wfo', ?string $hariWfa = null);
    public function updatePlacement(int $id, array $data);
    public function deletePlacement(int $id);
}
