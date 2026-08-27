<?php

namespace App\Modules\Penilaian\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Penilaian\Services\EvaluationService;
use App\Modules\Penilaian\Services\NilaiImportService;
use App\Modules\Penilaian\Exports\NilaiExportTemplate;
use App\Modules\PKL\Models\PenempatanPkl;
use Illuminate\Http\Request;

class PenilaianController extends Controller
{
    protected $service;
    protected $importService;

    public function __construct(EvaluationService $service, NilaiImportService $importService)
    {
        $this->service = $service;
        $this->importService = $importService;
    }

    /**
     * Display grades dashboard.
     */
    public function index(Request $request)
    {
        $role = auth()->user()->role;
        $isMasaPenilaianOpen = $this->service->isMasaPenilaianOpen();

        if ($role === 'murid') {
            $murid = auth()->user()->murid;
            $placement = $murid ? $murid->penempatanAktif : null;
            $evaluation = null;
            if ($placement) {
                $evaluation = $this->service->getEvaluationByPlacement($placement->id);
            }
            $tps = \App\Modules\Penilaian\Models\TujuanPembelajaran::with(['indikators' => function ($q) {
                $q->orderBy('nomor_urut', 'asc');
            }])->orderBy('nomor', 'asc')->get();
            return view('penilaian::murid_index', compact('placement', 'evaluation', 'tps', 'isMasaPenilaianOpen'));
        }

        // Admin & Guru Pembimbing
        $query = PenempatanPkl::with(['murid.kelas', 'dudi', 'penilaianPkl'])
            ->where('status', 'aktif');

        if ($role === 'guru') {
            $guruId = auth()->user()->guru?->id ?: -1;
            $query->where('guru_id', $guruId);
        }

        $placements = $query->paginate(15);

        // Fetch dynamic learning objectives and their indicators
        $tps = \App\Modules\Penilaian\Models\TujuanPembelajaran::with(['indikators' => function ($q) {
            $q->orderBy('nomor_urut', 'asc');
        }])->orderBy('nomor', 'asc')->get();

        return view('penilaian::index', compact('placements', 'tps', 'isMasaPenilaianOpen'));
    }

    /**
     * Toggle Assessment Period (Admin only).
     */
    public function toggleMasaPenilaian(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses khusus administrator.');
        }

        $current = \App\Modules\Setting\Models\Setting::where('key', 'masa_penilaian')->value('value');
        $newStatus = ($current === 'buka') ? 'tutup' : 'buka';

        \App\Modules\Setting\Models\Setting::updateOrCreate(
            ['key' => 'masa_penilaian'],
            ['value' => $newStatus]
        );

        $statusText = ($newStatus === 'buka') ? 'DIBUKA' : 'DITUTUP';
        return redirect()->back()->with('success', "Masa pengisian nilai PKL berhasil {$statusText}.");
    }

    /**
     * Student submits DUDI marks & uploads certificate / proof.
     */
    public function storeMurid(Request $request)
    {
        $murid = auth()->user()->murid;
        $placement = $murid ? $murid->penempatanAktif : null;

        if (!$placement) {
            return redirect()->back()->with('error', 'Anda belum memiliki penempatan PKL aktif.');
        }

        if (!$this->service->isMasaPenilaianOpen()) {
            return redirect()->back()->with('error', 'Masa pengisian penilaian PKL saat ini sedang ditutup oleh Admin.');
        }

        $existing = $this->service->getEvaluationByPlacement($placement->id);
        $hasProof = $existing && !empty($existing->bukti_nilai_industri);

        $request->validate([
            'nilai_industri' => 'required|array|min:1',
            'nilai_industri.*' => 'required|numeric|min:0|max:100',
            'keterangan_tp' => 'nullable|array',
            'keterangan_tp.*' => 'nullable|string',
            'foto_bukti' => ($hasProof ? 'nullable' : 'required') . '|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'nilai_industri.required' => 'Semua nilai indikator DUDI wajib diisi.',
            'nilai_industri.*.numeric' => 'Nilai harus berupa angka antara 0 hingga 100.',
            'foto_bukti.required' => 'Wajib mengunggah foto / scan PDF lembar nilai fisik dari DUDI.',
            'foto_bukti.max' => 'Ukuran file bukti nilai maksimal 2MB.',
        ]);

        try {
            $this->service->saveMuridEvaluation(
                $placement->id,
                $request->input('nilai_industri', []),
                $request->file('foto_bukti'),
                $request->input('keterangan_tp', [])
            );
            return redirect()->route('penilaian.index')->with('success', 'Nilai DUDI, keterangan TP, dan bukti lembar fisik berhasil dikirim. Menunggu verifikasi dari Guru Pembimbing.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan nilai: ' . $e->getMessage());
        }
    }

    /**
     * Teacher / Admin validates and saves final evaluation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'penempatan_pkl_id' => 'required|exists:penempatan_pkl,id',
            'nilai_guru' => 'required|array',
            'nilai_guru.*' => 'required|numeric|min:0|max:100',
            'koreksi_nilai_industri' => 'nullable|array',
            'koreksi_nilai_industri.*' => 'nullable|numeric|min:0|max:100',
            'keterangan_tp' => 'required|array',
            'keterangan_tp.*' => 'required|string|min:1',
            'catatan' => 'required|string',
        ]);

        // Guru hanya bisa input nilai untuk murid bimbingannya sendiri
        $role = auth()->user()->role;
        if ($role === 'guru') {
            $guruId = auth()->user()->guru?->id;
            $placement = PenempatanPkl::findOrFail($request->penempatan_pkl_id);
            if (!$guruId || $placement->guru_id !== $guruId) {
                abort(403, 'Anda hanya dapat menginput nilai untuk murid bimbingan Anda.');
            }
        }

        $this->service->saveGuruEvaluation(
            (int)$request->penempatan_pkl_id,
            $request->input('nilai_guru', []),
            $request->input('keterangan_tp', []),
            $request->input('catatan', ''),
            $request->input('koreksi_nilai_industri')
        );

        return redirect()->route('penilaian.index')->with('success', 'Penilaian siswa berhasil disahkan dan disimpan.');
    }

    /**
     * Download Excel template for inputting grades.
     */
    public function downloadTemplate()
    {
        $role = auth()->user()->role;
        $guruId = $role === 'guru' ? (auth()->user()->guru?->id ?: -1) : null;

        $exporter = new NilaiExportTemplate($role, $guruId);
        return $exporter->generate();
    }

    /**
     * Import grades from Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        try {
            $role = auth()->user()->role;
            $guruId = $role === 'guru' ? (auth()->user()->guru?->id ?: -1) : null;

            $result = $this->importService->importNilai(
                $request->file('file_excel')->getRealPath(),
                $role,
                $guruId
            );

            $message = "Berhasil mengimport {$result['success']} data nilai.";
            if (!empty($result['errors'])) {
                $message .= " Namun terdapat error: " . implode(', ', $result['errors']);
                return redirect()->route('penilaian.index')->with('warning', $message);
            }

            return redirect()->route('penilaian.index')->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()->route('penilaian.index')->with('error', 'Gagal mengimport data nilai: ' . $e->getMessage());
        }
    }

    /**
     * Delete submitted evaluation record (Admin only).
     */
    public function destroy(int $id)
    {
        $penilaian = \App\Modules\Penilaian\Models\PenilaianPkl::findOrFail($id);
        $penilaian->delete();

        return redirect()->route('penilaian.index')->with('success', 'Data nilai siswa berhasil dihapus.');
    }
}
