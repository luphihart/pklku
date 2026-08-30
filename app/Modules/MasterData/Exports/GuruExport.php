<?php

namespace App\Modules\MasterData\Exports;

use App\Modules\MasterData\Models\Guru;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuruExport
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function generate(): StreamedResponse
    {
        $query = Guru::with(['user'])->withCount([
            'penempatanBimbingan as total_bimbingan' => function ($q) {
                $q->where('status', 'aktif');
            }
        ]);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $gurus = $query->orderBy('nama', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Guru');

        // Headers
        $headers = [
            'No',
            'NIP',
            'Nama Lengkap Guru',
            'Email',
            'No. WhatsApp / HP',
            'Tanggal Lahir',
            'Jumlah Siswa Bimbingan Aktif',
        ];

        // Title Block
        $sheet->setCellValue('A1', 'DATA MASTER GURU PEMBIMBING SEKOLAH');
        $sheet->setCellValue('A2', 'Waktu Ekspor: ' . date('d-m-Y H:i:s') . ' | Total Data: ' . $gurus->count() . ' Guru');
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E293B']],
        ]);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '64748B']],
        ]);

        // Write Headers at Row 4
        $headerRow = 4;
        $colIndex = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($colIndex, $headerRow, $header);
            $colIndex++;
        }

        // Header Styling
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']], // Green/Emerald
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // Data Rows
        $rowNum = $headerRow + 1;
        $no = 1;

        foreach ($gurus as $guru) {
            $tglLahir = $guru->user?->tanggal_lahir ? $guru->user->tanggal_lahir->format('d/m/Y') : '-';

            $sheet->setCellValueByColumnAndRow(1, $rowNum, $no++);
            $sheet->setCellValueExplicitByColumnAndRow(2, $rowNum, $guru->nip ?: '-', DataType::TYPE_STRING);
            $sheet->setCellValueByColumnAndRow(3, $rowNum, $guru->nama);
            $sheet->setCellValueByColumnAndRow(4, $rowNum, $guru->user?->email ?? '-');
            $sheet->setCellValueExplicitByColumnAndRow(5, $rowNum, $guru->user?->phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValueByColumnAndRow(6, $rowNum, $tglLahir);
            $sheet->setCellValueByColumnAndRow(7, $rowNum, $guru->total_bimbingan . ' Siswa');

            // Alignment
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getRowDimension($rowNum)->setRowHeight(20);
            $rowNum++;
        }

        // Borders
        $lastDataRow = $rowNum - 1;
        if ($lastDataRow >= $headerRow) {
            $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$lastDataRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ]);
        }

        // AutoSize Columns
        for ($col = 1; $col <= count($headers); $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $filename = 'data_guru_' . date('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
