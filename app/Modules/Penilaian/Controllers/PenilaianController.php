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
            return view('penilaian::murid_index', compact('placement', 'evaluation', 'tps'));
        }

        // Admin & Guru Pembimbing
        $query = PenempatanPkl::with(['murid.kelas', 'dudi', 'penilaianPkl'])
            ->where('status', 'aktif');

        if ($role === 'guru') {
            $query->where('guru_id', auth()->user()->guru->id);
        }

        $placements = $query->paginate(15);

        // Fetch dynamic learning objectives and their indicators
        $tps = \App\Modules\Penilaian\Models\TujuanPembelajaran::with(['indikators' => function ($q) {
            $q->orderBy('nomor_urut', 'asc');
        }])->orderBy('nomor', 'asc')->get();

        return view('penilaian::index', compact('placements', 'tps'));
    }

    /**
     * Store/Update evaluation marks.
     */
    public function store(Request $request)
    {
        $request->validate([
            'penempatan_pkl_id' => 'required|exists:penempatan_pkl,id',
            'nilai_guru' => 'required|array',
            'nilai_guru.*' => 'required|numeric|min:0|max:100',
            'nilai_industri' => 'required|array',
            'nilai_industri.*' => 'required|numeric|min:0|max:100',
            'keterangan_tp' => 'required|array',
            'keterangan_tp.*' => 'required|string|min:1',
            'catatan' => 'required|string',
        ]);

        $this->service->saveEvaluation($request->all());

        return redirect()->route('penilaian.index')->with('success', 'Penilaian siswa berhasil disimpan.');
    }

    /**
     * Download Excel template for inputting grades.
     */
    public function downloadTemplate()
    {
        $role = auth()->user()->role;
        $guruId = $role === 'guru' ? auth()->user()->guru->id : null;

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
            $guruId = $role === 'guru' ? auth()->user()->guru->id : null;

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
}
