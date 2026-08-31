<?php

namespace App\Modules\Laporan\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PKL\Models\PenempatanPkl;
use App\Modules\Setting\Models\Setting;
use App\Modules\Laporan\Exports\AttendanceExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $role = auth()->user()->role;
        
        $query = PenempatanPkl::with([
            'murid.kelas.jurusan',
            'dudi',
            'guru',
            'penilaianPkl',
            'pembimbingIndustri'
        ]);

        if ($role === 'guru') {
            $query->where('guru_id', auth()->user()->guru?->id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->whereHas('murid', function($sq) use ($search) {
                    $sq->where('nama', 'like', "%{$search}%")
                       ->orWhere('nis', 'like', "%{$search}%");
                })->orWhereHas('dudi', function($sq) use ($search) {
                    $sq->where('nama', 'like', "%{$search}%");
                });
            });
        }

        $allPlacements = (clone $query)->whereIn('status', ['aktif', 'selesai'])->orderBy('status', 'asc')->get();
        $placements = $query->orderBy('status', 'asc')->paginate(15)->withQueryString();

        return view('laporan::index', compact('placements', 'allPlacements'));
    }

    /**
     * Export student PKL grade sheet certificate as PDF with official school header.
     */
    public function downloadNilaiPdf(int $placementId)
    {
        $placement = PenempatanPkl::with(['murid.kelas.jurusan', 'dudi', 'guru', 'penilaianPkl', 'pembimbingIndustri'])->findOrFail($placementId);
        
        $role = auth()->user()->role;

        // Murid hanya bisa download rapor miliknya sendiri
        if ($role === 'murid') {
            $muridId = auth()->user()->murid?->id;
            if (!$muridId || $muridId !== $placement->murid_id) {
                abort(403, 'Anda tidak diizinkan mengakses rapor siswa lain.');
            }
        }

        if (!$placement->penilaianPkl) {
            return back()->with('error', 'Siswa ini belum dinilai.');
        }

        $branding = $this->getBranding();

        $pdf = Pdf::loadView('laporan::pdf.nilai', compact('placement', 'branding'));
        
        $nama = $placement->murid?->nama ?? 'siswa';
        return $pdf->download('rapor_pkl_' . strtolower(str_replace(' ', '_', $nama)) . '.pdf');
    }

    /**
     * Export attendance logs as Excel.
     */
    public function downloadPresensiExcel(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:harian,mingguan,bulanan,kustom',
            'tanggal' => 'required_if:filter_type,harian|nullable|date',
            'minggu' => 'required_if:filter_type,mingguan|nullable|string',
            'bulan' => 'required_if:filter_type,bulanan|nullable|string',
            'tahun' => 'required_if:filter_type,bulanan|nullable|string',
            'tanggal_mulai' => 'required_if:filter_type,kustom|nullable|date',
            'tanggal_selesai' => 'required_if:filter_type,kustom|nullable|date|after_or_equal:tanggal_mulai',
        ]);

        return Excel::download(new AttendanceExport($request->all()), 'rekap_presensi_' . time() . '.xlsx');
    }

    /**
     * Export attendance recap as PDF (Portrait for Daily, Landscape for Multi-day).
     */
    public function downloadPresensiRekapPdf(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:harian,mingguan,bulanan,kustom',
            'tanggal' => 'required_if:filter_type,harian|nullable|date',
            'minggu' => 'required_if:filter_type,mingguan|nullable|string',
            'bulan' => 'required_if:filter_type,bulanan|nullable|string',
            'tahun' => 'required_if:filter_type,bulanan|nullable|string',
            'tanggal_mulai' => 'required_if:filter_type,kustom|nullable|date',
            'tanggal_selesai' => 'required_if:filter_type,kustom|nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $filterType = $request->filter_type;
        $startDate = null;
        $endDate = null;
        $label = '';

        switch ($filterType) {
            case 'harian':
                $date = $request->tanggal ?? now()->toDateString();
                $startDate = $date;
                $endDate = $date;
                $label = 'Tanggal: ' . \Carbon\Carbon::parse($date)->translatedFormat('d F Y');
                break;

            case 'mingguan':
                $weekStr = $request->minggu ?? date('Y-\WW');
                if (preg_match('/(\d+)-W(\d+)/', $weekStr, $matches)) {
                    $year = (int)$matches[1];
                    $week = (int)$matches[2];
                    $dto = new \DateTime();
                    $dto->setISODate($year, $week);
                    $startDate = $dto->format('Y-m-d');
                    $dto->modify('+6 days');
                    $endDate = $dto->format('Y-m-d');
                    $label = 'Minggu Ke-' . $week . ' Tahun ' . $year . ' (' . \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y') . ')';
                } else {
                    $startDate = now()->startOfWeek()->toDateString();
                    $endDate = now()->endOfWeek()->toDateString();
                    $label = 'Minggu Ini';
                }
                break;

            case 'bulanan':
                $month = $request->bulan ?? date('m');
                $year = $request->tahun ?? date('Y');
                $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
                $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
                $label = 'Periode Bulan: ' . \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');
                break;

            case 'kustom':
                $startDate = $request->tanggal_mulai ?? now()->toDateString();
                $endDate = $request->tanggal_selesai ?? now()->toDateString();
                $label = 'Periode: ' . \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y');
                break;
        }

        // Generate dates in range
        $dates = [];
        $current = \Carbon\Carbon::parse($startDate);
        $last = \Carbon\Carbon::parse($endDate);
        while ($current->lessThanOrEqualTo($last)) {
            $dates[] = $current->toDateString();
            $current->addDay();
        }

        $role = auth()->user()->role;
        $placementsQuery = PenempatanPkl::select(['id', 'murid_id', 'dudi_id', 'guru_id', 'status'])
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

        // Fetch presensi logs
        $presensiList = \App\Modules\Presensi\Models\Presensi::whereIn('penempatan_pkl_id', $placementIds)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->select(['id', 'penempatan_pkl_id', 'tanggal', 'jam_masuk', 'jam_pulang', 'status_masuk', 'status_pulang'])
            ->get();
            
        $presensiData = [];
        foreach ($presensiList as $p) {
            $presensiData[$p->penempatan_pkl_id][$p->tanggal] = $p;
        }

        // Fetch approved leave data
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

        // Fetch holidays
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

        $branding = $this->getBranding();

        $pdf = Pdf::loadView('laporan::pdf.presensi_rekap', compact(
            'filterType', 'dates', 'placements', 'presensiData', 
            'leavesByPlacementAndDate', 'holidayMap', 'label', 'branding'
        ))->setPaper('a4', $filterType === 'harian' ? 'portrait' : 'landscape');

        return $pdf->download('rekap_presensi_' . time() . '.pdf');
    }

    /**
     * Export journal recap as PDF.
     */
    public function downloadJurnalRekapPdf(Request $request)
    {
        if ($request->filled('placement_id')) {
            return $this->downloadStudentJournalPdf($request);
        }

        $request->validate([
            'filter_type' => 'required|in:harian,bulanan,kustom',
            'tanggal' => 'required_if:filter_type,harian|nullable|date',
            'bulan' => 'required_if:filter_type,bulanan|nullable|string',
            'tahun' => 'required_if:filter_type,bulanan|nullable|string',
            'tanggal_mulai' => 'required_if:filter_type,kustom|nullable|date',
            'tanggal_selesai' => 'required_if:filter_type,kustom|nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $filterType = $request->filter_type;
        $startDate = null;
        $endDate = null;
        $label = '';

        switch ($filterType) {
            case 'harian':
                $date = $request->tanggal ?? now()->toDateString();
                $startDate = $date;
                $endDate = $date;
                $label = 'Tanggal: ' . \Carbon\Carbon::parse($date)->translatedFormat('d F Y');
                break;

            case 'bulanan':
                $month = $request->bulan ?? date('m');
                $year = $request->tahun ?? date('Y');
                $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
                $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
                $label = 'Periode Bulan: ' . \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');
                break;

            case 'kustom':
                $startDate = $request->tanggal_mulai ?? now()->toDateString();
                $endDate = $request->tanggal_selesai ?? now()->toDateString();
                $label = 'Periode: ' . \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y');
                break;
        }

        $role = auth()->user()->role;
        $placementsQuery = PenempatanPkl::select(['id', 'murid_id', 'dudi_id', 'guru_id', 'status'])
            ->with([
                'murid:id,nama,nis,kelas_id',
                'murid.kelas:id,nama',
                'dudi:id,nama'
            ])
            ->where('status', 'aktif');

        if ($role === 'guru') {
            $placementsQuery->where('guru_id', auth()->user()->guru?->id);
        }

        $placementIds = $placementsQuery->pluck('id')->toArray();

        $journals = \App\Modules\Jurnal\Models\Jurnal::whereIn('penempatan_pkl_id', $placementIds)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->with(['penempatanPkl.murid.kelas', 'penempatanPkl.dudi'])
            ->orderBy('tanggal', 'desc')
            ->get();

        $branding = $this->getBranding();

        $pdf = Pdf::loadView('laporan::pdf.jurnal_rekap', compact(
            'journals', 'startDate', 'endDate', 'label', 'branding'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('rekap_jurnal_' . time() . '.pdf');
    }

    /**
     * Download student journal as PDF.
     */
    public function downloadStudentJournalPdf(Request $request, $placementId = null)
    {
        $role = auth()->user()->role;
        $targetPlacementId = $placementId ?: ($request->query('placement_id') ?: $request->input('placement_id'));

        if (in_array($role, ['admin', 'guru']) && $targetPlacementId) {
            $placement = PenempatanPkl::with(['murid.kelas.jurusan', 'dudi', 'guru', 'pembimbingIndustri'])
                ->findOrFail($targetPlacementId);

            if ($role === 'guru') {
                $guruId = auth()->user()->guru?->id;
                if (!$guruId || $placement->guru_id !== $guruId) {
                    abort(403, 'Anda hanya dapat mengakses laporan murid bimbingan Anda.');
                }
            }
        } else {
            $murid = auth()->user()->murid;
            $placement = $murid ? PenempatanPkl::with(['murid.kelas.jurusan', 'dudi', 'guru', 'pembimbingIndustri'])
                ->where('murid_id', $murid->id)
                ->whereIn('status', ['aktif', 'selesai'])
                ->latest()
                ->first() : null;
        }

        if (!$placement) {
            return redirect()->back()->with('error', 'Data penempatan PKL tidak ditemukan.');
        }

        $journals = \App\Modules\Jurnal\Models\Jurnal::where('penempatan_pkl_id', $placement->id)
            ->select(['id', 'penempatan_pkl_id', 'tanggal', 'deskripsi_aktivitas', 'status_verifikasi', 'catatan_verifikasi'])
            ->orderBy('tanggal', 'asc')
            ->get();

        $branding = $this->getBranding();
        $nama = $placement->murid?->nama ?? 'siswa';

        $pdf = Pdf::loadView('laporan::pdf.jurnal_siswa', compact('placement', 'journals', 'branding'));
        return $pdf->download('laporan_jurnal_' . strtolower(str_replace(' ', '_', $nama)) . '.pdf');
    }

    /**
     * Download student attendance as PDF.
     */
    public function downloadStudentAttendancePdf(Request $request, $placementId = null)
    {
        $role = auth()->user()->role;
        $targetPlacementId = $placementId ?: ($request->query('placement_id') ?: $request->input('placement_id'));

        if (in_array($role, ['admin', 'guru']) && $targetPlacementId) {
            $placement = PenempatanPkl::with(['murid.kelas.jurusan', 'dudi', 'guru', 'pembimbingIndustri'])
                ->findOrFail($targetPlacementId);

            if ($role === 'guru') {
                $guruId = auth()->user()->guru?->id;
                if (!$guruId || $placement->guru_id !== $guruId) {
                    abort(403, 'Anda hanya dapat mengakses laporan murid bimbingan Anda.');
                }
            }
        } else {
            $murid = auth()->user()->murid;
            $placement = $murid ? PenempatanPkl::with(['murid.kelas.jurusan', 'dudi', 'guru', 'pembimbingIndustri'])
                ->where('murid_id', $murid->id)
                ->whereIn('status', ['aktif', 'selesai'])
                ->latest()
                ->first() : null;
        }

        if (!$placement) {
            return redirect()->back()->with('error', 'Data penempatan PKL tidak ditemukan.');
        }

        $presensiList = \App\Modules\Presensi\Models\Presensi::where('penempatan_pkl_id', $placement->id)
            ->select(['id', 'penempatan_pkl_id', 'tanggal', 'jam_masuk', 'jam_pulang', 'status_masuk', 'status_pulang'])
            ->get();

        $leaves = \App\Modules\Presensi\Models\IzinSakit::where('penempatan_pkl_id', $placement->id)
            ->where('status_approval', 'disetujui')
            ->get();

        $attendanceRecords = collect();

        foreach ($presensiList as $p) {
            $status = $p->status_masuk === 'tepat_waktu' ? 'Hadir (Tepat Waktu)' : 'Terlambat';
            $type = $p->status_masuk === 'tepat_waktu' ? 'hadir' : 'terlambat';
            $keterangan = '-';

            if ($p->status_masuk === 'libur_shift') {
                $status = 'Libur Shift DUDI';
                $type = 'libur_shift';
                $keterangan = 'Libur Shift / Off Day';
            } elseif ($p->status_masuk === 'alpha') {
                $status = 'Alpha (Tidak Hadir)';
                $type = 'alpha';
                $keterangan = 'Tidak Hadir Tanpa Keterangan';
            }

            $attendanceRecords->push((object)[
                'tanggal' => $p->tanggal,
                'jam_masuk' => $p->jam_masuk,
                'jam_pulang' => $p->jam_pulang,
                'status' => $status,
                'type' => $type,
                'keterangan' => $keterangan,
            ]);
        }

        foreach ($leaves as $l) {
            $start = \Carbon\Carbon::parse($l->tanggal_mulai);
            $end = \Carbon\Carbon::parse($l->tanggal_selesai);
            $curr = $start->copy();
            while ($curr->lessThanOrEqualTo($end)) {
                $dateStr = $curr->toDateString();
                if (!$attendanceRecords->firstWhere('tanggal', $dateStr)) {
                    $attendanceRecords->push((object)[
                        'tanggal' => $dateStr,
                        'jam_masuk' => null,
                        'jam_pulang' => null,
                        'status' => ucfirst($l->tipe) . ' (Disetujui)',
                        'type' => $l->tipe,
                        'keterangan' => ucfirst($l->tipe) . ': ' . $l->alasan,
                    ]);
                }
                $curr->addDay();
            }
        }

        $presensis = $attendanceRecords->sortBy('tanggal')->values();

        $summary = [
            'total_hadir' => $presensis->where('type', 'hadir')->count(),
            'total_terlambat' => $presensis->where('type', 'terlambat')->count(),
            'total_izin' => $presensis->where('type', 'izin')->count(),
            'total_sakit' => $presensis->where('type', 'sakit')->count(),
            'total_libur_shift' => $presensis->where('type', 'libur_shift')->count(),
            'total_alpha' => $presensis->where('type', 'alpha')->count(),
        ];

        $branding = $this->getBranding();
        $nama = $placement->murid?->nama ?? 'siswa';

        $pdf = Pdf::loadView('laporan::pdf.presensi_siswa', compact('placement', 'presensis', 'summary', 'branding'));
        return $pdf->download('laporan_presensi_' . strtolower(str_replace(' ', '_', $nama)) . '.pdf');
    }

    /**
     * Fetch school branding settings for PDF Kop Surat headers (cached).
     */
    private function getBranding(): array
    {
        return Cache::remember('school_branding', now()->addHours(24), function() {
            $settings = Setting::whereIn('key', [
                'nama_sekolah', 'alamat_sekolah', 'nama_kepala_sekolah', 'nip_kepala_sekolah', 'kota_sekolah', 'footer_rapor'
            ])->pluck('value', 'key');

            return [
                'nama_sekolah' => $settings->get('nama_sekolah') ?: 'SMK NEGERI 1 JAKARTA',
                'alamat_sekolah' => $settings->get('alamat_sekolah') ?: 'Jl. Teknologi Canggih No. 42, Kota Digital',
                'kepala_sekolah' => $settings->get('nama_kepala_sekolah') ?: 'Dr. H. Akhmad Yusuf, M.T.',
                'nip_kepala_sekolah' => $settings->get('nip_kepala_sekolah') ?: '198001012005011001',
                'kota_sekolah' => $settings->get('kota_sekolah') ?: 'Pati',
                'footer_rapor' => $settings->get('footer_rapor') ?: 'Nilai diisi rentang 0 - 100. Keterangan diisi jika dibutuhkan.',
            ];
        });
    }
}
