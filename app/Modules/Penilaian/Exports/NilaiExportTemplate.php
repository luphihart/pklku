<?php

namespace App\Modules\Penilaian\Exports;

use App\Modules\MasterData\Models\Murid;
use App\Modules\Penilaian\Models\IndikatorPenilaian;
use App\Modules\Penilaian\Models\TujuanPembelajaran;
use App\Modules\PKL\Models\PenempatanPkl;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;

class NilaiExportTemplate
{
    protected string $role;
    protected ?int $guruId;

    /**
     * @param string   $role   Role of the logged-in user ('admin' or 'guru').
     * @param int|null $guruId Guru ID – required when role is 'guru'.
     */
    public function __construct(string $role, ?int $guruId = null)
    {
        $this->role = $role;
        $this->guruId = $guruId;
    }

    /**
     * Generate the Excel template and return a streamed download response.
     */
    public function generate(): StreamedResponse
    {
        // ------------------------------------------------------------------
        // 1. Query active placements (filtered by guru when applicable)
        // ------------------------------------------------------------------
        $query = PenempatanPkl::where('status', 'aktif')
            ->with(['murid.kelas', 'dudi']);

        if ($this->role === 'guru' && $this->guruId) {
            $query->where('guru_id', $this->guruId);
        }

        $placements = $query->get();

        // ------------------------------------------------------------------
        // 2. Fetch indicator lists ordered by nomor_urut and ID
        // ------------------------------------------------------------------
        $guruIndicators    = IndikatorPenilaian::where('tipe', 'guru')->orderBy('nomor_urut')->orderBy('id')->get();
        $industriIndicators = IndikatorPenilaian::where('tipe', 'industri')->orderBy('nomor_urut')->orderBy('id')->get();
        $tps                = TujuanPembelajaran::orderBy('nomor')->orderBy('id')->get();

        // ------------------------------------------------------------------
        // 3. Build workbook
        // ------------------------------------------------------------------
        $spreadsheet = new Spreadsheet();

        // ---- Main data sheet ----
        $dataSheet = $spreadsheet->getActiveSheet();
        $dataSheet->setTitle('Data Nilai');

        $this->buildDataSheet($dataSheet, $placements, $guruIndicators, $industriIndicators, $tps);

        // ---- Hidden metadata sheet ----
        $metaSheet = new Worksheet($spreadsheet, 'metadata');
        $spreadsheet->addSheet($metaSheet);
        $metaSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        $metaSheet->setCellValue('A1', $guruIndicators->pluck('id')->implode(','));
        $metaSheet->setCellValue('A2', $industriIndicators->pluck('id')->implode(','));
        $metaSheet->setCellValue('A3', $tps->pluck('id')->implode(','));

        // Ensure the data sheet is selected when the file is opened
        $spreadsheet->setActiveSheetIndex(0);

        // ------------------------------------------------------------------
        // 4. Return streamed download response
        // ------------------------------------------------------------------
        $filename = 'template_input_nilai_' . date('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ======================================================================
    // Private helpers
    // ======================================================================

    /**
     * Build the main data sheet with headers, student rows, and styling.
     */
    private function buildDataSheet(
        Worksheet $sheet,
        $placements,
        $guruIndicators,
        $industriIndicators,
        $tps
    ): void {
        // ------------------------------------------------------------------
        // Build header row
        // ------------------------------------------------------------------
        $headers = ['NIS', 'Nama Murid', 'Kelas', 'DUDI'];

        foreach ($guruIndicators as $indicator) {
            $headers[] = 'Guru: ' . $indicator->nama;
        }
        foreach ($industriIndicators as $indicator) {
            $headers[] = 'DUDI: ' . $indicator->nama;
        }
        foreach ($tps as $tp) {
            $headers[] = 'Keterangan TP ' . ($tp->nomor ?? '-') . ': ' . Str::limit($tp->nama, 50);
        }

        $headers[] = 'Catatan';

        // Write header cells
        $colIndex = 1; // PhpSpreadsheet uses 1-based column index
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($colIndex, 1, $header);
            $colIndex++;
        }

        // ------------------------------------------------------------------
        // Style the header row
        // ------------------------------------------------------------------
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $headerRange   = 'A1:' . $lastColLetter . '1';

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'], // Blue header
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // ------------------------------------------------------------------
        // Fill student data rows (indicator, TP comments & catatan columns left blank)
        // ------------------------------------------------------------------
        $rowNum = 2;
        foreach ($placements as $placement) {
            $murid = $placement->murid;
            if (!$murid) {
                continue;
            }

            $sheet->setCellValueByColumnAndRow(1, $rowNum, $murid->nis);
            $sheet->setCellValueByColumnAndRow(2, $rowNum, $murid->nama);
            $sheet->setCellValueByColumnAndRow(3, $rowNum, $murid->kelas->nama ?? '-');
            $sheet->setCellValueByColumnAndRow(4, $rowNum, $placement->dudi->nama ?? '-');

            // Indicator columns, TP columns, and Catatan column → left empty

            $rowNum++;
        }

        // ------------------------------------------------------------------
        // Auto-size all columns
        // ------------------------------------------------------------------
        for ($col = 1; $col <= count($headers); $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
    }
}
