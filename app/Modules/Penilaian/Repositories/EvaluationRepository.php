<?php

namespace App\Modules\Penilaian\Repositories;

use App\Modules\Penilaian\Models\PenilaianPkl;

class EvaluationRepository implements EvaluationRepositoryInterface
{
    public function getStudentEvaluations()
    {
        return PenilaianPkl::with(['penempatanPkl.murid.kelas', 'penempatanPkl.dudi'])->paginate(15);
    }

    public function findByPlacementId(int $placementId)
    {
        return PenilaianPkl::where('penempatan_pkl_id', $placementId)->first();
    }

    public function saveEvaluation(array $data)
    {
        return PenilaianPkl::updateOrCreate(
            ['penempatan_pkl_id' => $data['penempatan_pkl_id']],
            $data
        );
    }
}
