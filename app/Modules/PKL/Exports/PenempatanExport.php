<?php

namespace App\Modules\PKL\Exports;

use App\Modules\PKL\Models\PenempatanPkl;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Carbon;

class PenempatanExport
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function generate(): StreamedResponse
    {
        $query = PenempatanPkl::with([
            'murid.kelas',
            'dudi',
            'guru',
            'pembimbingIndustri'
        ]);

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['dudi_id'])) {
            $query->where('dudi_id', $this->filters['dudi_id']);
        }

        if (!empty($this->filters['guru_id'])) {
            $query->where('guru_id', $this->filters['guru_id']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('murid', function ($m) use ($search) {
                    $m->where('nama', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%");
                })->orWhereHas('dudi', function ($d) use ($search) {
                    $d->where('nama', 'like', "%{$search}%");
                })->orWhereHas('guru', function ($g) use ($search) {
                    $g->where('nama', 'like', "%{$search}%");
                });
            });
        }

        $placements = $query->orderBy('status', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plotting Penempatan PKL');

        // Headers
        $headers = [
            'No',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Mitra DUDI',
            'Guru Pembimbing Sekolah',
            'Pembimbing DUDI / Industri',
            'Model Kerja',
            'Shift Kerja',
            'Jam Kerja',
            'Jadwal Hari WFA',
            'Hari Libur Rutin',
            'Tanggal Mulai PKL',
            'Tanggal Selesai PKL',
            'Status Penempatan',
        ];

        // Title Block
        $sheet->setCellValue('A1', 'DATA PLOTTING PENEMPATAN SISWA PKL');
        $sheet->setCellValue('A2', 'Waktu Ekspor: ' . date('d-m-Y H:i:s') . ' | Total Data: ' . $placements->count() . ' Penempatan');
        $sheet->mergeCells('A1:O1');
        $sheet->mergeCells('A2:O2');

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
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']], // Indigo/Purple
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // Data Rows
        $rowNum = $headerRow + 1;
        $no = 1;

        foreach ($placements as $p) {
            $nis = $p->murid?->nis ?: '-';
            $namaSiswa = $p->murid?->nama ?: 'Siswa Terhapus';
            $kelas = $p->murid?->kelas?->nama ?: '-';
            $dudi = $p->dudi?->nama ?: 'DUDI Terhapus';
            $guru = $p->guru?->nama ?: '-';
            $pembimbingIndustri = $p->pembimbingIndustri?->nama ?: '-';
            
            $modelKerja = strtoupper($p->tipe_kerja ?: 'WFO');
            $shiftInfo = $p->getEffectiveShiftHours();
            $shiftKerja = ucfirst($p->tipe_shift ?? 'reguler');
            $jamKerja = $shiftInfo['jam_masuk'] . ' - ' . $shiftInfo['jam_pulang'];
            $hariWfa = $p->tipe_kerja === 'hybrid' ? ($p->hari_wfa ?: '-') : '-';
            $hariLibur = $p->hari_libur ?: 'Sabtu, Minggu (Default)';

            $tglMulai = $p->tanggal_mulai ? Carbon::parse($p->tanggal_mulai)->format('d/m/Y') : '-';
            $tglSelesai = $p->tanggal_selesai ? Carbon::parse($p->tanggal_selesai)->format('d/m/Y') : '-';
            $status = ucfirst($p->status);

            $sheet->setCellValueByColumnAndRow(1, $rowNum, $no++);
            $sheet->setCellValueExplicitByColumnAndRow(2, $rowNum, $nis, DataType::TYPE_STRING);
            $sheet->setCellValueByColumnAndRow(3, $rowNum, $namaSiswa);
            $sheet->setCellValueByColumnAndRow(4, $rowNum, $kelas);
            $sheet->setCellValueByColumnAndRow(5, $rowNum, $dudi);
            $sheet->setCellValueByColumnAndRow(6, $rowNum, $guru);
            $sheet->setCellValueByColumnAndRow(7, $rowNum, $pembimbingIndustri);
            $sheet->setCellValueByColumnAndRow(8, $rowNum, $modelKerja);
            $sheet->setCellValueByColumnAndRow(9, $rowNum, $shiftKerja);
            $sheet->setCellValueByColumnAndRow(10, $rowNum, $jamKerja);
            $sheet->setCellValueByColumnAndRow(11, $rowNum, $hariWfa);
            $sheet->setCellValueByColumnAndRow(12, $rowNum, $hariLibur);
            $sheet->setCellValueByColumnAndRow(13, $rowNum, $tglMulai);
            $sheet->setCellValueByColumnAndRow(14, $rowNum, $tglSelesai);
            $sheet->setCellValueByColumnAndRow(15, $rowNum, $status);

            // Alignment
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("M{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("N{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("O{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("M{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Status color highlight
            if ($p->status === 'aktif') {
                $sheet->getStyle("O{$rowNum}")->getFont()->getColor()->setRGB('16A34A');
            } else {
                $sheet->getStyle("O{$rowNum}")->getFont()->getColor()->setRGB('64748B');
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

        $filename = 'data_plotting_penempatan_' . date('Ymd_His') . '.xlsx';

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
