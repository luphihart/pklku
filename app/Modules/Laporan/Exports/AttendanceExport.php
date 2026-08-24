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
        $filterType = $this->filters['filter_type'] ?? 'harian';
        $startDate = null;
        $endDate = null;
        $label = '';

        switch ($filterType) {
            case 'harian':
                $date = $this->filters['tanggal'] ?? now()->toDateString();
                $startDate = $date;
                $endDate = $date;
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
                    $label = 'Minggu Ke-' . $week . ' Tahun ' . $year . ' (' . \Carbon\Carbon::parse($startDate)->format('d/m/y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d/m/y') . ')';
                } else {
                    $startDate = now()->startOfWeek()->toDateString();
                    $endDate = now()->endOfWeek()->toDateString();
                    $label = 'Minggu Ini';
                }
                break;

            case 'bulanan':
                $month = $this->filters['bulan'] ?? date('m');
                $year = $this->filters['tahun'] ?? date('Y');
                $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
                $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
                $label = 'Bulan: ' . \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');
                break;

            case 'kustom':
                $startDate = $this->filters['tanggal_mulai'] ?? now()->toDateString();
                $endDate = $this->filters['tanggal_selesai'] ?? now()->toDateString();
                $label = 'Jangkauan Kustom: ' . \Carbon\Carbon::parse($startDate)->format('d/m/y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d/m/y');
                break;
        }

        // Generate list of dates in the range
        $dates = [];
        $current = \Carbon\Carbon::parse($startDate);
        $last = \Carbon\Carbon::parse($endDate);
        while ($current->lessThanOrEqualTo($last)) {
            $dates[] = $current->toDateString();
            $current->addDay();
        }

        // Load active placements based on logged-in user role
        $role = auth()->user()->role;
        $placementsQuery = \App\Modules\PKL\Models\PenempatanPkl::select(['id', 'murid_id', 'dudi_id', 'guru_id', 'status'])
            ->with([
                'murid:id,nama,nis,kelas_id',
                'murid.kelas:id,nama',
                'dudi:id,nama'
            ])
            ->where('status', 'aktif');

        if ($role === 'guru') {
            $placementsQuery->where('guru_id', auth()->user()->guru?->id);
        }

        $placements = $placementsQuery->get();
        $placementIds = $placements->pluck('id')->toArray();

        // Fetch presensi data grouped by placement ID and tanggal (selected columns only)
        $presensiList = \App\Modules\Presensi\Models\Presensi::whereIn('penempatan_pkl_id', $placementIds)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->select(['id', 'penempatan_pkl_id', 'tanggal', 'jam_masuk', 'jam_pulang', 'status_masuk', 'status_pulang'])
            ->get();
            
        $presensiData = [];
        foreach ($presensiList as $p) {
            $presensiData[$p->penempatan_pkl_id][$p->tanggal] = $p;
        }

        // Fetch approved leave data (fix: status_approval enum is 'disetujui')
        $leavesData = \App\Modules\Presensi\Models\IzinSakit::whereIn('penempatan_pkl_id', $placementIds)
            ->where('status_approval', 'disetujui')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
                  ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
                  ->orWhere(function($sub) use ($startDate, $endDate) {
                      $sub->where('tanggal_mulai', '<=', $startDate)
                          ->where('tanggal_selesai', '>=', $endDate);
                  });
            })
            ->select(['id', 'penempatan_pkl_id', 'tipe', 'tanggal_mulai', 'tanggal_selesai', 'status_approval'])
            ->get();

        $leavesByPlacementAndDate = [];
        foreach ($leavesData as $leave) {
            $start = \Carbon\Carbon::parse($leave->tanggal_mulai);
            $end = \Carbon\Carbon::parse($leave->tanggal_selesai);
            $curr = $start->copy();
            while ($curr->lessThanOrEqualTo($end)) {
                $dateStr = $curr->toDateString();
                $leavesByPlacementAndDate[$leave->penempatan_pkl_id][$dateStr] = ucfirst($leave->tipe);
                $curr->addDay();
            }
        }

        // Fetch holiday data
        $holidays = \App\Modules\MasterData\Models\HariLibur::where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
              ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
              ->orWhere(function ($sub) use ($startDate, $endDate) {
                  $sub->where('tanggal_mulai', '<=', $startDate)
                      ->where('tanggal_selesai', '>=', $endDate);
              });
        })->get();

        $holidayMap = [];
        foreach ($holidays as $h) {
            $hStart = \Carbon\Carbon::parse($h->tanggal_mulai);
            $hEnd = \Carbon\Carbon::parse($h->tanggal_selesai);
            $curr = $hStart->copy();
            while ($curr->lessThanOrEqualTo($hEnd)) {
                $holidayMap[$curr->toDateString()] = 'Libur: ' . $h->nama;
                $curr->addDay();
            }
        }

        return view('laporan::excel.presensi', [
            'filterType' => $filterType,
            'dates' => $dates,
            'placements' => $placements,
            'presensiData' => $presensiData,
            'leavesByPlacementAndDate' => $leavesByPlacementAndDate,
            'holidayMap' => $holidayMap,
            'label' => $label
        ]);
    }
}
