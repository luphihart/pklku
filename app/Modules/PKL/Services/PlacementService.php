<?php

namespace App\Modules\PKL\Services;

use App\Modules\PKL\Repositories\PlacementRepositoryInterface;
use App\Modules\PKL\Models\KunjunganMonitoring;

class PlacementService
{
    protected $repo;

    public function __construct(PlacementRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function listPlacements(array $filters = []) { return $this->repo->getActivePlacements($filters); }
    public function getPlacement(int $id) { return $this->repo->findById($id); }

    public function savePlacement(array $data)
    {
        $placement = $this->repo->createPlacement($data);
        $this->logActivity("Melakukan penempatan PKL murid ID: " . $placement->murid_id . " ke DUDI ID: " . $placement->dudi_id);
        return $placement;
    }

    public function saveMassPlacement(array $muridIds, int $dudiId, int $guruId, ?int $pembimbingIndustriId, string $tglMulai, string $tglSelesai, string $tipeKerja = 'wfo', ?string $hariWfa = null, ?string $hariLibur = null)
    {
        $placements = $this->repo->createMassPlacement($muridIds, $dudiId, $guruId, $pembimbingIndustriId, $tglMulai, $tglSelesai, $tipeKerja, $hariWfa, $hariLibur);
        $count = count($placements);
        $this->logActivity("Melakukan penempatan PKL massal untuk {$count} murid");
        return $placements;
    }

    public function editPlacement(int $id, array $data)
    {
        $result = $this->repo->updatePlacement($id, $data);
        $this->logActivity("Mengubah detail penempatan PKL ID: " . $id);
        return $result;
    }

    public function removePlacement(int $id)
    {
        $result = $this->repo->deletePlacement($id);
        $this->logActivity("Menghapus/Membatalkan penempatan PKL ID: " . $id);
        return $result;
    }

    /**
     * Add visitation record (Kunjungan Monitoring) by Guru.
     */
    public function recordVisitation(int $placementId, array $data, $fotoFile = null)
    {
        $filepath = null;

        if ($fotoFile) {
            $dirPath = public_path('storage/kunjungan');
            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0755, true);
            }
            $filename = 'kunjungan_' . $placementId . '_' . time() . '.' . $fotoFile->getClientOriginalExtension();
            $fotoFile->move($dirPath, $filename);
            $filepath = $filename;
        }

        $visitation = KunjunganMonitoring::create([
            'penempatan_pkl_id' => $placementId,
            'tanggal' => $data['tanggal'] ?? now()->toDateString(),
            'jenis_kunjungan' => $data['jenis_kunjungan'] ?? null,
            'deskripsi_kunjungan' => $data['deskripsi_kunjungan'],
            'foto_kunjungan' => $filepath,
            'latitude' => null,
            'longitude' => null,
        ]);

        $this->logActivity("Mencatat kunjungan guru pembimbing untuk penempatan ID: " . $placementId);
        return $visitation;
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
