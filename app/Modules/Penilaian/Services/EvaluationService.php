<?php

namespace App\Modules\Penilaian\Services;

use App\Modules\Penilaian\Repositories\EvaluationRepositoryInterface;
use App\Modules\Setting\Models\Setting;

class EvaluationService
{
    protected $repo;

    public function __construct(EvaluationRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function listEvaluations() { return $this->repo->getStudentEvaluations(); }
    public function getEvaluationByPlacement(int $placementId) { return $this->repo->findByPlacementId($placementId); }

    /**
     * Calculate and save student PKL evaluation.
     */
    public function saveEvaluation(array $data)
    {
        // Pre-fetch all indicators in a single query to eliminate N+1 queries
        $allIndikatorIds = array_unique(array_merge(
            array_keys($data['nilai_guru'] ?? []),
            array_keys($data['nilai_industri'] ?? [])
        ));
        $indikators = \App\Modules\Penilaian\Models\IndikatorPenilaian::whereIn('id', $allIndikatorIds)->get()->keyBy('id');

        // 1. Process Guru Marks
        $nilaiGuruJson = [];
        $sumGuru = 0;
        $countGuru = 0;
        foreach ($data['nilai_guru'] ?? [] as $id => $val) {
            $ind = $indikators->get($id);
            $nama = $ind ? $ind->nama : 'Indikator #' . $id;
            $deskripsi = $ind ? $ind->deskripsi : '';
            $nilaiGuruJson[$id] = [
                'nama' => $nama,
                'deskripsi' => $deskripsi,
                'nilai' => (float)$val,
                'keterangan' => ''
            ];
            $sumGuru += (float)$val;
            $countGuru++;
        }
        $avgGuru = $countGuru > 0 ? $sumGuru / $countGuru : 0;

        // 2. Process Industri/DUDI Marks
        $nilaiIndustriJson = [];
        $sumIndustri = 0;
        $countIndustri = 0;
        foreach ($data['nilai_industri'] ?? [] as $id => $val) {
            $ind = $indikators->get($id);
            $nama = $ind ? $ind->nama : 'Indikator #' . $id;
            $deskripsi = $ind ? $ind->deskripsi : '';
            $nilaiIndustriJson[$id] = [
                'nama' => $nama,
                'deskripsi' => $deskripsi,
                'nilai' => (float)$val,
                'keterangan' => ''
            ];
            $sumIndustri += (float)$val;
            $countIndustri++;
        }
        $avgIndustri = $countIndustri > 0 ? $sumIndustri / $countIndustri : 0;

        // 3. Fetch Weights from settings
        $settings = Setting::whereIn('key', ['bobot_nilai_guru', 'bobot_nilai_industri'])->pluck('value', 'key');
        $weightGuru = (float)($settings->get('bobot_nilai_guru') ?: 50.0);
        $weightIndustri = (float)($settings->get('bobot_nilai_industri') ?: 50.0);

        // 4. Calculate final combined score with weight normalization
        $totalWeight = ($weightGuru + $weightIndustri) ?: 100.0;
        $finalScore = (($avgGuru * $weightGuru) + ($avgIndustri * $weightIndustri)) / $totalWeight;

        // 5. Determine predicate grade
        $predicate = 'D';
        if ($finalScore >= 90) {
            $predicate = 'A';
        } elseif ($finalScore >= 80) {
            $predicate = 'B';
        } elseif ($finalScore >= 70) {
            $predicate = 'C';
        }

        $payload = [
            'penempatan_pkl_id' => $data['penempatan_pkl_id'],
            'nilai_guru_json' => $nilaiGuruJson,
            'nilai_industri_json' => $nilaiIndustriJson,
            'keterangan_tp_json' => $data['keterangan_tp'] ?? [],
            'rata_nilai_guru' => round($avgGuru, 2),
            'rata_nilai_industri' => round($avgIndustri, 2),
            'nilai_akhir' => round($finalScore, 2),
            'predikat' => $predicate,
            'catatan' => $data['catatan'] ?? null,
        ];

        $evaluation = $this->repo->saveEvaluation($payload);

        $this->logActivity("Menginput penilaian akhir PKL untuk penempatan ID: " . $data['penempatan_pkl_id']);
        return $evaluation;
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
