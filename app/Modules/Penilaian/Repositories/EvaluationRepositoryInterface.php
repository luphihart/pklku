<?php

namespace App\Modules\Penilaian\Repositories;

interface EvaluationRepositoryInterface
{
    public function getStudentEvaluations();
    public function findByPlacementId(int $placementId);
    public function saveEvaluation(array $data);
}
