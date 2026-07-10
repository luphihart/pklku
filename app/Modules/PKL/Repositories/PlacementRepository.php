<?php

namespace App\Modules\PKL\Repositories;

use App\Modules\PKL\Models\PenempatanPkl;
use App\Modules\MasterData\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;

class PlacementRepository implements PlacementRepositoryInterface
{
    public function getActivePlacements(array $filters = [])
    {
        $query = PenempatanPkl::with(['murid.kelas', 'dudi', 'guru', 'pembimbingIndustri', 'tahunAjaran']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['dudi_id'])) {
            $query->where('dudi_id', $filters['dudi_id']);
        }

        if (!empty($filters['guru_id'])) {
            $query->where('guru_id', $filters['guru_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('murid', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        return $query->paginate(15);
    }

    public function findById(int $id)
    {
        return PenempatanPkl::with(['murid.kelas', 'dudi', 'guru', 'pembimbingIndustri', 'tahunAjaran'])->findOrFail($id);
    }

    public function createPlacement(array $data)
    {
        $ta = TahunAjaran::where('is_aktif', true)->first();
        if (!$ta) {
            throw new \Exception('Tidak ada Tahun Ajaran aktif.');
        }

        return PenempatanPkl::create(array_merge($data, [
            'tahun_ajaran_id' => $ta->id,
            'status' => 'aktif',
        ]));
    }

    public function createMassPlacement(array $muridIds, int $dudiId, int $guruId, ?int $pembimbingIndustriId, string $tglMulai, string $tglSelesai)
    {
        $ta = TahunAjaran::where('is_aktif', true)->first();
        if (!$ta) {
            throw new \Exception('Tidak ada Tahun Ajaran aktif.');
        }

        return DB::transaction(function() use ($muridIds, $dudiId, $guruId, $pembimbingIndustriId, $ta, $tglMulai, $tglSelesai) {
            $created = [];
            foreach ($muridIds as $muridId) {
                // Ensure no active duplicate placement for this murid
                $exists = PenempatanPkl::where('murid_id', $muridId)
                    ->where('tahun_ajaran_id', $ta->id)
                    ->where('status', 'aktif')
                    ->exists();

                if ($exists) {
                    continue; // Skip or throw error depending on strictness
                }

                $created[] = PenempatanPkl::create([
                    'murid_id' => $muridId,
                    'dudi_id' => $dudiId,
                    'guru_id' => $guruId,
                    'pembimbing_industri_id' => $pembimbingIndustriId,
                    'tahun_ajaran_id' => $ta->id,
                    'tanggal_mulai' => $tglMulai,
                    'tanggal_selesai' => $tglSelesai,
                    'status' => 'aktif',
                ]);
            }
            return $created;
        });
    }

    public function updatePlacement(int $id, array $data)
    {
        $placement = PenempatanPkl::findOrFail($id);
        return $placement->update($data);
    }

    public function deletePlacement(int $id)
    {
        $placement = PenempatanPkl::findOrFail($id);
        return $placement->delete();
    }
}
