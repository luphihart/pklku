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
     * Check if the assessment period is currently open.
     */
    public function isMasaPenilaianOpen(): bool
    {
        $status = Setting::where('key', 'masa_penilaian')->value('value');
        return $status === 'buka';
    }

    /**
     * Student saves DUDI marks + uploads physical certificate/proof.
     */
    public function saveMuridEvaluation(int $placementId, array $nilaiIndustriRaw, $uploadedFile)
    {
        if (!$this->isMasaPenilaianOpen()) {
            throw new \Exception("Masa input penilaian PKL saat ini sedang ditutup oleh Admin.");
        }

        $allIndikatorIds = array_keys($nilaiIndustriRaw);
        $indikators = \App\Modules\Penilaian\Models\IndikatorPenilaian::whereIn('id', $allIndikatorIds)->get()->keyBy('id');

        $nilaiIndustriJson = [];
        $sumIndustri = 0;
        $countIndustri = 0;
        foreach ($nilaiIndustriRaw as $id => $val) {
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

        // Handle uploaded file
        $fileName = null;
        if ($uploadedFile) {
            $ext = $uploadedFile->getClientOriginalExtension();
            $fileName = 'bukti_nilai_' . $placementId . '_' . time() . '.' . $ext;
            $uploadedFile->storeAs('penilaian', $fileName, 'public');
        }

        // Get existing record if any
        $existing = $this->repo->findByPlacementId($placementId);
        $payload = [
            'penempatan_pkl_id' => $placementId,
            'nilai_industri_json' => $nilaiIndustriJson,
            'rata_nilai_industri' => round($avgIndustri, 2),
            'status_nilai_industri' => 'diajukan',
        ];

        if ($fileName) {
            $payload['bukti_nilai_industri'] = $fileName;
        }

        // If Guru already graded, calculate final score
        if ($existing && $existing->rata_nilai_guru > 0) {
            $settings = Setting::whereIn('key', ['bobot_nilai_guru', 'bobot_nilai_industri'])->pluck('value', 'key');
            $weightGuru = (float)($settings->get('bobot_nilai_guru') ?: 50.0);
            $weightIndustri = (float)($settings->get('bobot_nilai_industri') ?: 50.0);
            $totalWeight = ($weightGuru + $weightIndustri) ?: 100.0;
            $finalScore = (($existing->rata_nilai_guru * $weightGuru) + ($avgIndustri * $weightIndustri)) / $totalWeight;

            $predicate = 'D';
            if ($finalScore >= 90) $predicate = 'A';
            elseif ($finalScore >= 80) $predicate = 'B';
            elseif ($finalScore >= 70) $predicate = 'C';

            $payload['nilai_akhir'] = round($finalScore, 2);
            $payload['predikat'] = $predicate;
        }

        $evaluation = $this->repo->saveEvaluation($payload);
        $this->logActivity("Murid menginput nilai DUDI untuk penempatan ID: " . $placementId);
        return $evaluation;
    }

    /**
     * Guru saves school marks + finalizes evaluation.
     */
    public function saveGuruEvaluation(int $placementId, array $nilaiGuruRaw, array $keteranganTp, string $catatan, ?array $koreksiNilaiIndustri = null)
    {
        $allIndikatorIds = array_unique(array_merge(
            array_keys($nilaiGuruRaw),
            array_keys($koreksiNilaiIndustri ?? [])
        ));
        $indikators = \App\Modules\Penilaian\Models\IndikatorPenilaian::whereIn('id', $allIndikatorIds)->get()->keyBy('id');

        // 1. Process Guru Marks
        $nilaiGuruJson = [];
        $sumGuru = 0;
        $countGuru = 0;
        foreach ($nilaiGuruRaw as $id => $val) {
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

        // 2. Existing or corrected Industri Marks
        $existing = $this->repo->findByPlacementId($placementId);
        $nilaiIndustriJson = $existing ? ($existing->nilai_industri_json ?? []) : [];
        $avgIndustri = $existing ? (float)($existing->rata_nilai_industri ?? 0) : 0;

        if (!empty($koreksiNilaiIndustri)) {
            $sumInd = 0;
            $countInd = 0;
            $nilaiIndustriJson = [];
            foreach ($koreksiNilaiIndustri as $id => $val) {
                $ind = $indikators->get($id);
                $nama = $ind ? $ind->nama : 'Indikator #' . $id;
                $deskripsi = $ind ? $ind->deskripsi : '';
                $nilaiIndustriJson[$id] = [
                    'nama' => $nama,
                    'deskripsi' => $deskripsi,
                    'nilai' => (float)$val,
                    'keterangan' => ''
                ];
                $sumInd += (float)$val;
                $countInd++;
            }
            $avgIndustri = $countInd > 0 ? $sumInd / $countInd : 0;
        }

        // 3. Fetch Weights
        $settings = Setting::whereIn('key', ['bobot_nilai_guru', 'bobot_nilai_industri'])->pluck('value', 'key');
        $weightGuru = (float)($settings->get('bobot_nilai_guru') ?: 50.0);
        $weightIndustri = (float)($settings->get('bobot_nilai_industri') ?: 50.0);

        $totalWeight = ($weightGuru + $weightIndustri) ?: 100.0;
        $finalScore = (($avgGuru * $weightGuru) + ($avgIndustri * $weightIndustri)) / $totalWeight;

        $predicate = 'D';
        if ($finalScore >= 90) $predicate = 'A';
        elseif ($finalScore >= 80) $predicate = 'B';
        elseif ($finalScore >= 70) $predicate = 'C';

        $payload = [
            'penempatan_pkl_id' => $placementId,
            'nilai_guru_json' => $nilaiGuruJson,
            'nilai_industri_json' => $nilaiIndustriJson,
            'keterangan_tp_json' => $keteranganTp,
            'rata_nilai_guru' => round($avgGuru, 2),
            'rata_nilai_industri' => round($avgIndustri, 2),
            'nilai_akhir' => round($finalScore, 2),
            'predikat' => $predicate,
            'catatan' => $catatan,
            'status_nilai_industri' => 'diverifikasi',
        ];

        $evaluation = $this->repo->saveEvaluation($payload);
        $this->logActivity("Guru Pembimbing mengesahkan penilaian akhir PKL untuk penempatan ID: " . $placementId);
        return $evaluation;
    }

    /**
     * Calculate and save student PKL evaluation (Full / Bulk / Legacy support).
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
            'status_nilai_industri' => 'diverifikasi',
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
