<?php

namespace App\Modules\PKL\Repositories;

use App\Modules\PKL\Models\PenempatanPkl;
use App\Modules\MasterData\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;

class PlacementRepository implements PlacementRepositoryInterface
{
    public function getActivePlacements(array $filters = [])
    {
        $query = PenempatanPkl::with(['murid.kelas', 'dudi', 'guru', 'pembimbingIndustri', 'tahunAjaran'])
            ->select('penempatan_pkl.*');

        if (!empty($filters['status'])) {
            $query->where('penempatan_pkl.status', $filters['status']);
        }

        if (!empty($filters['dudi_id'])) {
            $query->where('penempatan_pkl.dudi_id', $filters['dudi_id']);
        }

        if (!empty($filters['guru_id'])) {
            $query->where('penempatan_pkl.guru_id', $filters['guru_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($mainQ) use ($search) {
                $mainQ->whereHas('murid', function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%");
                })->orWhereHas('dudi', function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%");
                })->orWhereHas('guru', function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%");
                });
            });
        }

        $sortBy = $filters['sort_by'] ?? ($filters['sort_field'] ?? '');
        $order = strtolower($filters['order'] ?? ($filters['sort'] ?? 'asc'));
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'asc';
        }

        if ($sortBy === 'nis') {
            $query->join('murid', 'penempatan_pkl.murid_id', '=', 'murid.id')
                  ->orderBy('murid.nis', $order);
        } elseif ($sortBy === 'nama' || $sortBy === 'murid') {
            $query->join('murid', 'penempatan_pkl.murid_id', '=', 'murid.id')
                  ->orderBy('murid.nama', $order);
        } elseif ($sortBy === 'kelas') {
            $query->join('murid', 'penempatan_pkl.murid_id', '=', 'murid.id')
                  ->leftJoin('kelas', 'murid.kelas_id', '=', 'kelas.id')
                  ->orderBy('kelas.nama', $order);
        } elseif ($sortBy === 'dudi' || $sortBy === 'tempat_dudi') {
            $query->leftJoin('dudi', 'penempatan_pkl.dudi_id', '=', 'dudi.id')
                  ->orderBy('dudi.nama', $order);
        } elseif ($sortBy === 'guru' || $sortBy === 'guru_pembimbing') {
            $query->leftJoin('guru', 'penempatan_pkl.guru_id', '=', 'guru.id')
                  ->orderBy('guru.nama', $order);
        } else {
            $query->orderBy('penempatan_pkl.id', 'desc');
        }

        return $query->paginate(15)->appends(array_filter($filters));
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
    ) {
        $ta = TahunAjaran::where('is_aktif', true)->first();
        if (!$ta) {
            throw new \Exception('Tidak ada Tahun Ajaran aktif.');
        }

        return DB::transaction(function() use (
            $muridIds, $dudiId, $guruId, $pembimbingIndustriId, $ta, $tglMulai, $tglSelesai,
            $tipeKerja, $hariWfa, $hariLibur, $tipeShift, $jamMasuk, $batasTerlambat, $jamPulang, $tutupJamPulang
        ) {
            // Find which murid already have active placement in ONE single query
            $existingMuridIds = PenempatanPkl::whereIn('murid_id', $muridIds)
                ->where('tahun_ajaran_id', $ta->id)
                ->where('status', 'aktif')
                ->pluck('murid_id')
                ->toArray();

            $validMuridIds = array_diff($muridIds, $existingMuridIds);

            if (empty($validMuridIds) && !empty($existingMuridIds)) {
                throw new \Exception('Semua murid yang Anda pilih sudah memiliki penempatan PKL aktif pada tahun ajaran ini.');
            }

            $created = [];
            foreach ($validMuridIds as $muridId) {
                $created[] = PenempatanPkl::create([
                    'murid_id' => $muridId,
                    'dudi_id' => $dudiId,
                    'guru_id' => $guruId,
                    'pembimbing_industri_id' => $pembimbingIndustriId,
                    'tahun_ajaran_id' => $ta->id,
                    'tanggal_mulai' => $tglMulai,
                    'tanggal_selesai' => $tglSelesai,
                    'status' => 'aktif',
                    'tipe_kerja' => $tipeKerja,
                    'hari_wfa' => $hariWfa,
                    'hari_libur' => $hariLibur,
                    'tipe_shift' => $tipeShift,
                    'jam_masuk' => $jamMasuk,
                    'batas_terlambat' => $batasTerlambat,
                    'jam_pulang' => $jamPulang,
                    'tutup_jam_pulang' => $tutupJamPulang,
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
