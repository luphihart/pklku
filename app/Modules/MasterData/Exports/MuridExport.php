<?php

namespace App\Modules\MasterData\Exports;

use App\Modules\MasterData\Models\Murid;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MuridExport
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function generate(): StreamedResponse
    {
        $query = Murid::with(['user', 'kelas.jurusan', 'penempatanAktif.dudi', 'penempatanAktif.guru']);

        if (!empty($this->filters['kelas_id'])) {
            $query->where('kelas_id', $this->filters['kelas_id']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $murids = $query->orderBy('nama', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Murid');

        // Headers
        $headers = [
            'No',
            'NIS',
            'Nama Lengkap',
            'Kelas',
            'Jurusan',
            'Email',
            'No. WhatsApp / HP',
            'Tanggal Lahir',
            'Status PKL',
            'Mitra DUDI Tempat PKL',
            'Guru Pembimbing Sekolah',
        ];

        // Title Block
        $sheet->setCellValue('A1', 'DATA MASTER SISWA / MURID PKL');
        $sheet->setCellValue('A2', 'Waktu Ekspor: ' . date('d-m-Y H:i:s') . ' | Total Data: ' . $murids->count() . ' Siswa');
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');

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
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // Data Rows
        $rowNum = $headerRow + 1;
        $no = 1;

        foreach ($murids as $murid) {
            $penempatan = $murid->penempatanAktif;
            $statusPkl = $penempatan ? 'Aktif PKL' : 'Belum Ditempatkan';
            $dudiNama = $penempatan?->dudi?->nama ?? '-';
            $guruNama = $penempatan?->guru?->nama ?? '-';
            $tglLahir = $murid->user?->tanggal_lahir ? $murid->user->tanggal_lahir->format('d/m/Y') : '-';

            $sheet->setCellValueByColumnAndRow(1, $rowNum, $no++);
            $sheet->setCellValueExplicitByColumnAndRow(2, $rowNum, $murid->nis, DataType::TYPE_STRING);
            $sheet->setCellValueByColumnAndRow(3, $rowNum, $murid->nama);
            $sheet->setCellValueByColumnAndRow(4, $rowNum, $murid->kelas?->nama ?? '-');
            $sheet->setCellValueByColumnAndRow(5, $rowNum, $murid->kelas?->jurusan?->nama ?? '-');
            $sheet->setCellValueByColumnAndRow(6, $rowNum, $murid->user?->email ?? '-');
            $sheet->setCellValueExplicitByColumnAndRow(7, $rowNum, $murid->user?->phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValueByColumnAndRow(8, $rowNum, $tglLahir);
            $sheet->setCellValueByColumnAndRow(9, $rowNum, $statusPkl);
            $sheet->setCellValueByColumnAndRow(10, $rowNum, $dudiNama);
            $sheet->setCellValueByColumnAndRow(11, $rowNum, $guruNama);

            // Row styling
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Highlight status PKL
            if ($statusPkl === 'Aktif PKL') {
                $sheet->getStyle("I{$rowNum}")->getFont()->getColor()->setRGB('16A34A');
            } else {
                $sheet->getStyle("I{$rowNum}")->getFont()->getColor()->setRGB('DC2626');
            }

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

        $filename = 'data_murid_' . date('Ymd_His') . '.xlsx';

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
