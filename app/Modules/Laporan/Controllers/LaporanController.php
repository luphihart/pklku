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
    public function index()
    {
        // Get placements with evaluations for PDF certificate downloads (paginated)
        $placements = PenempatanPkl::with(['murid.kelas.jurusan', 'dudi', 'penilaianPkl'])
            ->whereHas('penilaianPkl')
            ->paginate(20);

        return view('laporan::index', compact('placements'));
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
     * Download student journal as PDF.
     * Admin & Guru dapat mengakses lewat ?placement_id=X
     * Murid otomatis diarahkan ke penempatan aktif miliknya.
     */
    public function downloadStudentJournalPdf(Request $request)
    {
        $role = auth()->user()->role;

        if (in_array($role, ['admin', 'guru']) && $request->filled('placement_id')) {
            // Admin / Guru: download PDF murid tertentu berdasarkan placement_id
            $placement = PenempatanPkl::with(['murid.kelas.jurusan', 'dudi', 'guru', 'pembimbingIndustri'])
                ->findOrFail($request->placement_id);

            // Guru hanya bisa download untuk murid bimbingannya
            if ($role === 'guru') {
                $guruId = auth()->user()->guru?->id;
                if (!$guruId || $placement->guru_id !== $guruId) {
                    abort(403, 'Anda hanya dapat mengakses laporan murid bimbingan Anda.');
                }
            }
        } else {
            // Murid: gunakan penempatan aktif milik sendiri
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
     * Admin & Guru dapat mengakses lewat ?placement_id=X
     * Murid otomatis diarahkan ke penempatan aktif miliknya.
     */
    public function downloadStudentAttendancePdf(Request $request)
    {
        $role = auth()->user()->role;

        if (in_array($role, ['admin', 'guru']) && $request->filled('placement_id')) {
            // Admin / Guru: download PDF murid tertentu berdasarkan placement_id
            $placement = PenempatanPkl::with(['murid.kelas.jurusan', 'dudi', 'guru', 'pembimbingIndustri'])
                ->findOrFail($request->placement_id);

            // Guru hanya bisa download untuk murid bimbingannya
            if ($role === 'guru') {
                $guruId = auth()->user()->guru?->id;
                if (!$guruId || $placement->guru_id !== $guruId) {
                    abort(403, 'Anda hanya dapat mengakses laporan murid bimbingan Anda.');
                }
            }
        } else {
            // Murid: gunakan penempatan aktif milik sendiri
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
            $attendanceRecords->push((object)[
                'tanggal' => $p->tanggal,
                'jam_masuk' => $p->jam_masuk,
                'jam_pulang' => $p->jam_pulang,
                'status' => $p->status_masuk === 'tepat_waktu' ? 'Hadir (Tepat Waktu)' : 'Terlambat',
                'type' => $p->status_masuk === 'tepat_waktu' ? 'hadir' : 'terlambat',
                'keterangan' => '-',
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
                        'type' => $l->tipe, // 'izin' or 'sakit'
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
