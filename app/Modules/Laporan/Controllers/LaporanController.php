<?php

namespace App\Modules\Laporan\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PKL\Models\PenempatanPkl;
use App\Modules\Setting\Models\Setting;
use App\Modules\Laporan\Exports\AttendanceExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        // Get all active placements with evaluations for PDF certificate downloads
        $placements = PenempatanPkl::with(['murid.kelas.jurusan', 'dudi', 'penilaianPkl'])
            ->whereHas('penilaianPkl')
            ->get();

        return view('laporan::index', compact('placements'));
    }

    /**
     * Export student PKL grade sheet certificate as PDF with official school header.
     */
    public function downloadNilaiPdf(int $placementId)
    {
        $placement = PenempatanPkl::with(['murid.kelas.jurusan', 'dudi', 'guru', 'penilaianPkl', 'pembimbingIndustri'])->findOrFail($placementId);
        
        $role = auth()->user()->role;
        if ($role === 'murid' && auth()->user()->murid->id !== $placement->murid_id) {
            abort(403, 'Anda tidak diizinkan mengakses rapor siswa lain.');
        }

        if (!$placement->penilaianPkl) {
            return back()->with('error', 'Siswa ini belum dinilai.');
        }

        $branding = $this->getBranding();

        $pdf = Pdf::loadView('laporan::pdf.nilai', compact('placement', 'branding'));
        
        return $pdf->download('rapor_pkl_' . strtolower(str_replace(' ', '_', $placement->murid->nama)) . '.pdf');
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
     */
    public function downloadStudentJournalPdf()
    {
        $murid = auth()->user()->murid;
        $placement = $murid ? PenempatanPkl::with(['murid.kelas.jurusan', 'dudi', 'guru', 'pembimbingIndustri'])
            ->where('murid_id', $murid->id)
            ->where('status', 'aktif')
            ->first() : null;

        if (!$placement) {
            return redirect()->back()->with('error', 'Anda tidak memiliki penempatan PKL aktif.');
        }

        $journals = \App\Modules\Jurnal\Models\Jurnal::where('penempatan_pkl_id', $placement->id)
            ->orderBy('tanggal', 'asc')
            ->get();

        $branding = $this->getBranding();

        $pdf = Pdf::loadView('laporan::pdf.jurnal_siswa', compact('placement', 'journals', 'branding'));
        return $pdf->download('laporan_jurnal_' . strtolower(str_replace(' ', '_', $murid->nama)) . '.pdf');
    }

    /**
     * Download student attendance as PDF.
     */
    public function downloadStudentAttendancePdf()
    {
        $murid = auth()->user()->murid;
        $placement = $murid ? PenempatanPkl::with(['murid.kelas.jurusan', 'dudi', 'guru', 'pembimbingIndustri'])
            ->where('murid_id', $murid->id)
            ->where('status', 'aktif')
            ->first() : null;

        if (!$placement) {
            return redirect()->back()->with('error', 'Anda tidak memiliki penempatan PKL aktif.');
        }

        $presensis = \App\Modules\Presensi\Models\Presensi::where('penempatan_pkl_id', $placement->id)
            ->orderBy('tanggal', 'asc')
            ->get();

        $branding = $this->getBranding();

        $pdf = Pdf::loadView('laporan::pdf.presensi_siswa', compact('placement', 'presensis', 'branding'));
        return $pdf->download('laporan_presensi_' . strtolower(str_replace(' ', '_', $murid->nama)) . '.pdf');
    }

    /**
     * Fetch school branding settings for PDF Kop Surat headers.
     */
    private function getBranding(): array
    {
        return [
            'nama_sekolah' => Setting::where('key', 'nama_sekolah')->value('value') ?: 'SMK NEGERI 1 JAKARTA',
            'alamat_sekolah' => Setting::where('key', 'alamat_sekolah')->value('value') ?: 'Jl. Teknologi Canggih No. 42, Kota Digital',
            'kepala_sekolah' => Setting::where('key', 'nama_kepala_sekolah')->value('value') ?: 'Dr. H. Akhmad Yusuf, M.T.',
            'nip_kepala_sekolah' => Setting::where('key', 'nip_kepala_sekolah')->value('value') ?: '198001012005011001',
            'kota_sekolah' => Setting::where('key', 'kota_sekolah')->value('value') ?: 'Pati',
            'footer_rapor' => Setting::where('key', 'footer_rapor')->value('value') ?: 'Nilai diisi rentang 0 - 100. Keterangan diisi jika dibutuhkan.',
        ];
    }
}
