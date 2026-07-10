<?php

namespace App\Modules\Penilaian\Services;

use App\Modules\MasterData\Models\Murid;
use App\Modules\PKL\Models\PenempatanPkl;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class NilaiImportService
{
    protected EvaluationService $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    /**
     * Import grades from an uploaded Excel file.
     *
     * @param  string      $filePath Full path to the uploaded .xlsx file.
     * @param  string      $role     Role of the importer ('admin' or 'guru').
     * @param  int|null    $guruId   Guru ID – required when role is 'guru'.
     * @return array{success: int, errors: array}
     */
    public function importNilai(string $filePath, string $role, ?int $guruId = null): array
    {
        $spreadsheet = IOFactory::load($filePath);

        // ------------------------------------------------------------------
        // 1. Read the hidden metadata sheet for indicator ID mapping
        // ------------------------------------------------------------------
        $metaSheet = $spreadsheet->getSheetByName('metadata');
        if (!$metaSheet) {
            throw new \Exception('Sheet metadata tidak ditemukan. Pastikan file berasal dari template yang benar.');
        }

        $guruIndicatorIds    = $this->parseIndicatorIds($metaSheet->getCell('A1')->getValue());
        $industriIndicatorIds = $this->parseIndicatorIds($metaSheet->getCell('A2')->getValue());
        
        // Parse TP IDs if they exist (row 3, A3) - fallback to empty if old template
        $tpCell = $metaSheet->getCell('A3');
        $tpIds = $tpCell ? $this->parseIndicatorIds($tpCell->getValue()) : [];

        // ------------------------------------------------------------------
        // 2. Read the main data sheet
        // ------------------------------------------------------------------
        $dataSheet = $spreadsheet->getSheet(0);
        $rows      = $dataSheet->toArray();

        if (count($rows) <= 1) {
            throw new \Exception('File Excel kosong atau tidak memiliki data.');
        }

        // Column layout:
        //   0 = NIS, 1 = Nama, 2 = Kelas, 3 = DUDI,
        //   4 .. 4+guruCount-1                       = guru indicator scores,
        //   4+guruCount .. 4+guruCount+indCount-1    = industri indicator scores,
        //   4+guruCount+indCount .. 4+guruCount+indCount+tpCount-1 = TP comments,
        //   last column                              = Catatan
        $guruCount     = count($guruIndicatorIds);
        $industriCount = count($industriIndicatorIds);
        $tpCount       = count($tpIds);
        $catatanCol    = 4 + $guruCount + $industriCount + $tpCount; // 0-based index

        // ------------------------------------------------------------------
        // 3. Process rows inside a transaction
        // ------------------------------------------------------------------
        $successCount = 0;
        $errors       = [];

        DB::transaction(function () use (
            $rows,
            $guruIndicatorIds,
            $industriIndicatorIds,
            $tpIds,
            $guruCount,
            $industriCount,
            $tpCount,
            $catatanCol,
            $role,
            $guruId,
            &$successCount,
            &$errors,
        ) {
            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue; // Skip header
                }

                $rowNumber = $index + 1; // Human-friendly row number

                try {
                    // a. Read NIS
                    $nis = trim($row[0] ?? '');
                    if (empty($nis)) {
                        throw new \Exception('NIS kosong.');
                    }

                    // b. Look up Murid
                    $murid = Murid::where('nis', $nis)->first();
                    if (!$murid) {
                        throw new \Exception("Murid dengan NIS '{$nis}' tidak ditemukan.");
                    }

                    // c. Find active placement
                    $placement = PenempatanPkl::where('murid_id', $murid->id)
                        ->where('status', 'aktif')
                        ->first();

                    if (!$placement) {
                        throw new \Exception("Penempatan PKL aktif untuk NIS '{$nis}' tidak ditemukan.");
                    }

                    // d. Validate guru ownership
                    if ($role === 'guru' && $guruId && (int)$placement->guru_id !== (int)$guruId) {
                        throw new \Exception("Murid '{$nis}' bukan bimbingan Anda.");
                    }

                    // e. Build nilai_guru array
                    $nilaiGuru = [];
                    for ($i = 0; $i < $guruCount; $i++) {
                        $score = $row[4 + $i] ?? null;
                        if ($score !== null && $score !== '') {
                            $nilaiGuru[$guruIndicatorIds[$i]] = (float) $score;
                        }
                    }

                    // f. Build nilai_industri array
                    $nilaiIndustri = [];
                    for ($i = 0; $i < $industriCount; $i++) {
                        $score = $row[4 + $guruCount + $i] ?? null;
                        if ($score !== null && $score !== '') {
                            $nilaiIndustri[$industriIndicatorIds[$i]] = (float) $score;
                        }
                    }

                    // g. Build keterangan_tp array
                    $keteranganTp = [];
                    for ($i = 0; $i < $tpCount; $i++) {
                        $comment = $row[4 + $guruCount + $industriCount + $i] ?? '';
                        $keteranganTp[$tpIds[$i]] = trim((string) $comment);
                    }

                    // h. Read catatan
                    $catatan = trim($row[$catatanCol] ?? '');

                    // i. Save evaluation
                    $this->evaluationService->saveEvaluation([
                        'penempatan_pkl_id' => $placement->id,
                        'nilai_guru'        => $nilaiGuru,
                        'nilai_industri'    => $nilaiIndustri,
                        'keterangan_tp'     => $keteranganTp,
                        'catatan'           => $catatan ?: null,
                    ]);

                    $successCount++;
                } catch (\Throwable $e) {
                    $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                }
            }

            // If every single row failed, rollback the entire transaction
            if ($successCount === 0 && !empty($errors)) {
                throw new \Exception('Semua baris gagal diimport. ' . implode(' | ', $errors));
            }
        });

        return [
            'success' => $successCount,
            'errors'  => $errors,
        ];
    }

    // ======================================================================
    // Private helpers
    // ======================================================================

    /**
     * Parse a comma-separated string of indicator IDs into an int array.
     */
    private function parseIndicatorIds(?string $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        return array_map('intval', array_filter(explode(',', $raw), fn($v) => $v !== ''));
    }
}
