<?php

namespace App\Modules\PKL\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PKL\Services\PlacementService;
use App\Modules\MasterData\Models\Murid;
use App\Modules\MasterData\Models\Dudi;
use App\Modules\MasterData\Models\Guru;
use App\Modules\MasterData\Models\PembimbingIndustri;
use Illuminate\Http\Request;

class PenempatanController extends Controller
{
    protected $service;

    public function __construct(PlacementService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only('status', 'dudi_id', 'guru_id', 'search', 'sort_by', 'order', 'sort');
        $placements = $this->service->listPlacements($filters);

        // Get DUDI, Guru, and students without active placements for plotting, ordered alphabetically
        $dudis = Dudi::orderBy('nama', 'asc')->get();
        $gurus = Guru::orderBy('nama', 'asc')->get();
        
        // Find students who do NOT have an active placement currently
        $unassignedStudents = Murid::with('kelas')
            ->whereDoesntHave('penempatanPkl', function($q) {
                $q->where('status', 'aktif');
            })
            ->orderBy('nama', 'asc')
            ->get();

        $kelasOptions = $unassignedStudents->pluck('kelas')->filter()->unique('id')->sortBy('nama')->values();

        return view('pkl::penempatan.index', compact('placements', 'dudis', 'gurus', 'unassignedStudents', 'kelasOptions'));
    }

    public function export(Request $request)
    {
        $exporter = new \App\Modules\PKL\Exports\PenempatanExport($request->only('status', 'dudi_id', 'guru_id', 'search'));
        return $exporter->generate();
    }

    public function storeMassal(Request $request)
    {
        $request->validate([
            'murid_ids' => 'required|array|min:1',
            'murid_ids.*' => 'exists:murid,id',
            'dudi_id' => 'required|exists:dudi,id',
            'guru_id' => 'required|exists:guru,id',
            'pembimbing_industri_id' => 'nullable|exists:pembimbing_industri,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'tipe_kerja' => 'nullable|in:wfo,wfa,hybrid',
            'hari_wfa' => 'nullable|array',
            'hari_libur' => 'nullable|array',
            'tipe_shift' => 'nullable|in:reguler,pagi,siang,sore,custom,rolling',
            'jam_masuk' => 'nullable|string|max:8',
            'batas_terlambat' => 'nullable|string|max:8',
            'jam_pulang' => 'nullable|string|max:8',
            'tutup_jam_pulang' => 'nullable|string|max:8',
        ], [
            'murid_ids.required' => 'Pilih minimal satu murid untuk ditempatkan.',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        $tipeKerja = $request->input('tipe_kerja', 'wfo');
        $hariWfa = ($tipeKerja === 'hybrid' && $request->has('hari_wfa') && is_array($request->input('hari_wfa')))
            ? implode(',', array_filter($request->input('hari_wfa', [])))
            : null;

        $hariLibur = ($request->has('hari_libur') && is_array($request->input('hari_libur')) && count($request->input('hari_libur')) > 0)
            ? implode(',', array_filter($request->input('hari_libur', [])))
            : null;

        $tipeShift = $request->input('tipe_shift', 'reguler');
        $jamMasuk = $tipeShift === 'custom' ? $this->sanitizeTime($request->input('jam_masuk')) : null;
        $batasTerlambat = $tipeShift === 'custom' ? $this->sanitizeTime($request->input('batas_terlambat')) : null;
        $jamPulang = $tipeShift === 'custom' ? $this->sanitizeTime($request->input('jam_pulang')) : null;
        $tutupJamPulang = $tipeShift === 'custom' ? $this->sanitizeTime($request->input('tutup_jam_pulang')) : null;

        try {
            $this->service->saveMassPlacement(
                $request->murid_ids,
                (int)$request->dudi_id,
                (int)$request->guru_id,
                $request->filled('pembimbing_industri_id') ? (int)$request->pembimbing_industri_id : null,
                $request->tanggal_mulai,
                $request->tanggal_selesai,
                $tipeKerja,
                $hariWfa,
                $hariLibur,
                $tipeShift,
                $jamMasuk,
                $batasTerlambat,
                $jamPulang,
                $tutupJamPulang
            );

            return redirect()->route('penempatan.index')->with('success', 'Plotting penempatan massal berhasil disimpan.');
        } catch (\Throwable $e) {
            return redirect()->route('penempatan.index')->with('error', 'Gagal menyimpan penempatan massal: ' . $e->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->service->removePlacement($id);
            return redirect()->route('penempatan.index')->with('success', 'Penempatan murid berhasil dibatalkan/dihapus.');
        } catch (\Throwable $e) {
            return redirect()->route('penempatan.index')->with('error', 'Gagal menghapus penempatan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'dudi_id' => 'required|exists:dudi,id',
            'guru_id' => 'required|exists:guru,id',
            'pembimbing_industri_id' => 'nullable|exists:pembimbing_industri,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'tipe_kerja' => 'nullable|in:wfo,wfa,hybrid',
            'hari_wfa' => 'nullable|array',
            'hari_libur' => 'nullable|array',
            'tipe_shift' => 'nullable|in:reguler,pagi,siang,sore,custom,rolling',
            'jam_masuk' => 'nullable|string|max:8',
            'batas_terlambat' => 'nullable|string|max:8',
            'jam_pulang' => 'nullable|string|max:8',
            'tutup_jam_pulang' => 'nullable|string|max:8',
        ], [
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        $tipeKerja = $request->input('tipe_kerja', 'wfo');
        $hariWfa = ($tipeKerja === 'hybrid' && $request->has('hari_wfa') && is_array($request->input('hari_wfa')))
            ? implode(',', array_filter($request->input('hari_wfa', [])))
            : null;

        $hariLibur = ($request->has('hari_libur') && is_array($request->input('hari_libur')) && count($request->input('hari_libur')) > 0)
            ? implode(',', array_filter($request->input('hari_libur', [])))
            : null;

        $tipeShift = $request->input('tipe_shift', 'reguler');
        $jamMasuk = $tipeShift === 'custom' ? $this->sanitizeTime($request->input('jam_masuk')) : null;
        $batasTerlambat = $tipeShift === 'custom' ? $this->sanitizeTime($request->input('batas_terlambat')) : null;
        $jamPulang = $tipeShift === 'custom' ? $this->sanitizeTime($request->input('jam_pulang')) : null;
        $tutupJamPulang = $tipeShift === 'custom' ? $this->sanitizeTime($request->input('tutup_jam_pulang')) : null;

        $updateData = [
            'dudi_id'                => (int)$request->input('dudi_id'),
            'guru_id'                => (int)$request->input('guru_id'),
            'pembimbing_industri_id' => $request->filled('pembimbing_industri_id') ? (int)$request->input('pembimbing_industri_id') : null,
            'tanggal_mulai'          => $request->input('tanggal_mulai'),
            'tanggal_selesai'        => $request->input('tanggal_selesai'),
            'tipe_kerja'             => $tipeKerja,
            'hari_wfa'               => $hariWfa,
            'hari_libur'             => $hariLibur,
            'tipe_shift'             => $tipeShift,
            'jam_masuk'              => $jamMasuk,
            'batas_terlambat'        => $batasTerlambat,
            'jam_pulang'             => $jamPulang,
            'tutup_jam_pulang'       => $tutupJamPulang,
        ];

        try {
            $this->service->editPlacement($id, $updateData);
            return redirect()->route('penempatan.index')->with('success', 'Detail penempatan murid berhasil diperbarui.');
        } catch (\Throwable $e) {
            return redirect()->route('penempatan.index')->with('error', 'Gagal memperbarui penempatan: ' . $e->getMessage());
        }
    }

    private function sanitizeTime(?string $time): ?string
    {
        if (empty($time)) {
            return null;
        }
        $time = trim($time);
        if (preg_match('/^(\d{1,2}):(\d{2})(:(\d{2}))?$/', $time, $matches)) {
            $hours = (int)$matches[1];
            $minutes = (int)$matches[2];
            $seconds = isset($matches[4]) ? (int)$matches[4] : 0;
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        return null;
    }

    public function destroyBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu penempatan untuk dihapus.');
        }

        $count = 0;
        foreach ($ids as $id) {
            try {
                $this->service->removePlacement($id);
                $count++;
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        return redirect()->route('penempatan.index')->with('success', $count . ' penempatan murid berhasil dihapus.');
    }

    /**
     * Get industrial supervisors for a given DUDI. Used for dynamic dropdown assignment.
     */
    public function getPembimbingIndustri(int $dudiId)
    {
        $supervisors = PembimbingIndustri::where('dudi_id', $dudiId)->get();
        return response()->json($supervisors);
    }
}
