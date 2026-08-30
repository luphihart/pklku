<?php

namespace App\Modules\MasterData\Exports;

use App\Modules\MasterData\Models\Dudi;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DudiExport
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function generate(): StreamedResponse
    {
        $query = Dudi::withCount([
            'penempatanPkl as total_siswa_aktif' => function ($q) {
                $q->where('status', 'aktif');
            }
        ]);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('pic_nama', 'like', "%{$search}%")
                  ->orWhere('pic_phone', 'like', "%{$search}%");
            });
        }

        $sort = $this->filters['sort'] ?? 'asc';
        $dudis = $query->orderBy('nama', $sort)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Mitra DUDI');

        // Headers
        $headers = [
            'No',
            'Nama Mitra DUDI',
            'Nama PIC / Kontak',
            'No. HP / WhatsApp PIC',
            'Hari Kerja Operasional',
            'Radius Presensi',
            'Titik Koordinat (Lat, Long)',
            'Alamat Kantor / Perusahaan',
            'Jumlah Siswa Aktif PKL',
        ];

        // Title Block
        $sheet->setCellValue('A1', 'DATA MASTER MITRA DUNIA USAHA / DUNIA INDUSTRI (DUDI)');
        $sheet->setCellValue('A2', 'Waktu Ekspor: ' . date('d-m-Y H:i:s') . ' | Total Data: ' . $dudis->count() . ' Mitra Perusahaan');
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');

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
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0284C7']], // Sky/Blue
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // Data Rows
        $rowNum = $headerRow + 1;
        $no = 1;

        foreach ($dudis as $dudi) {
            $koordinat = "{$dudi->latitude}, {$dudi->longitude}";

            $sheet->setCellValueByColumnAndRow(1, $rowNum, $no++);
            $sheet->setCellValueByColumnAndRow(2, $rowNum, $dudi->nama);
            $sheet->setCellValueByColumnAndRow(3, $rowNum, $dudi->pic_nama ?: '-');
            $sheet->setCellValueExplicitByColumnAndRow(4, $rowNum, $dudi->pic_phone ?: '-', DataType::TYPE_STRING);
            $sheet->setCellValueByColumnAndRow(5, $rowNum, $dudi->hari_kerja ?: 'Senin - Jumat');
            $sheet->setCellValueByColumnAndRow(6, $rowNum, $dudi->radius_meter . ' Meter');
            $sheet->setCellValueExplicitByColumnAndRow(7, $rowNum, $koordinat, DataType::TYPE_STRING);
            $sheet->setCellValueByColumnAndRow(8, $rowNum, $dudi->alamat ?: '-');
            $sheet->setCellValueByColumnAndRow(9, $rowNum, $dudi->total_siswa_aktif . ' Siswa');

            // Alignment
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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

        $filename = 'data_mitra_dudi_' . date('Ymd_His') . '.xlsx';

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
