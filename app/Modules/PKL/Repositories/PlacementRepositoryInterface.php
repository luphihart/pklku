<?php

namespace App\Modules\PKL\Repositories;

interface PlacementRepositoryInterface
{
    public function getActivePlacements(array $filters = []);
    public function createMassPlacement(
        array $muridIds,
        int $dudiId,
        int $guruId,
        ?int $pembimbingIndustriId,
        string $tglMulai,
        string $tglSelesai,
        string $tipeKerja = 'wfo',
        ?string $hariWfa = null,
        ?string $hariLibur = null,
        string $tipeShift = 'reguler',
        ?string $jamMasuk = null,
        ?string $batasTerlambat = null,
        ?string $jamPulang = null,
        ?string $tutupJamPulang = null
    );
    public function updatePlacement(int $id, array $data);
    public function deletePlacement(int $id);
}
