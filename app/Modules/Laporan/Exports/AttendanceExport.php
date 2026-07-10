<?php

namespace App\Modules\Laporan\Exports;

use App\Modules\Presensi\Models\Presensi;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AttendanceExport implements FromView, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = Presensi::with(['penempatanPkl.murid.kelas', 'penempatanPkl.dudi']);
        $filterType = $this->filters['filter_type'] ?? 'harian';
        $label = 'Hari Ini (' . date('d-m-Y') . ')';

        switch ($filterType) {
            case 'harian':
                $date = $this->filters['tanggal'] ?? now()->toDateString();
                $query->where('tanggal', $date);
                $label = 'Tanggal: ' . \Carbon\Carbon::parse($date)->format('d-m-Y');
                break;

            case 'mingguan':
                $weekStr = $this->filters['minggu'] ?? date('Y-\WW');
                if (preg_match('/(\d+)-W(\d+)/', $weekStr, $matches)) {
                    $year = (int)$matches[1];
                    $week = (int)$matches[2];
                    $dto = new \DateTime();
                    $dto->setISODate($year, $week);
                    $startDate = $dto->format('Y-m-d');
                    $dto->modify('+6 days');
                    $endDate = $dto->format('Y-m-d');
                    
                    $query->whereBetween('tanggal', [$startDate, $endDate]);
                    $label = 'Minggu Ke-' . $week . ' Tahun ' . $year . ' (' . \Carbon\Carbon::parse($startDate)->format('d/m/y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d/m/y') . ')';
                }
                break;

            case 'bulanan':
                $month = $this->filters['bulan'] ?? date('m');
                $year = $this->filters['tahun'] ?? date('Y');
                $query->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
                $label = 'Bulan: ' . \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');
                break;

            case 'kustom':
                $start = $this->filters['tanggal_mulai'] ?? now()->toDateString();
                $end = $this->filters['tanggal_selesai'] ?? now()->toDateString();
                $query->whereBetween('tanggal', [$start, $end]);
                $label = 'Jangkauan Kustom: ' . \Carbon\Carbon::parse($start)->format('d/m/y') . ' s/d ' . \Carbon\Carbon::parse($end)->format('d/m/y');
                break;
        }

        $presensis = $query->orderBy('tanggal', 'asc')->get();

        return view('laporan::excel.presensi', [
            'presensis' => $presensis,
            'label' => $label
        ]);
    }
}
