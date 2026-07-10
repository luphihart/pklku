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
        $filters = $request->only('status', 'dudi_id', 'guru_id', 'search');
        $placements = $this->service->listPlacements($filters);

        // Get DUDI, Guru, and students without active placements for plotting
        $dudis = Dudi::all();
        $gurus = Guru::all();
        
        // Find students who do NOT have an active placement currently
        $unassignedStudents = Murid::with('kelas')
            ->whereDoesntHave('penempatanPkl', function($q) {
                $q->where('status', 'aktif');
            })->get();

        $kelasOptions = $unassignedStudents->pluck('kelas')->unique('id')->filter();

        return view('pkl::penempatan.index', compact('placements', 'dudis', 'gurus', 'unassignedStudents', 'kelasOptions'));
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
        ], [
            'murid_ids.required' => 'Pilih minimal satu murid untuk ditempatkan.',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        $this->service->saveMassPlacement(
            $request->murid_ids,
            $request->dudi_id,
            $request->guru_id,
            $request->pembimbing_industri_id,
            $request->tanggal_mulai,
            $request->tanggal_selesai
        );

        return redirect()->route('penempatan.index')->with('success', 'Plotting penempatan massal berhasil disimpan.');
    }

    public function destroy(int $id)
    {
        $this->service->removePlacement($id);
        return redirect()->route('penempatan.index')->with('success', 'Penempatan murid berhasil dibatalkan/dihapus.');
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'dudi_id' => 'required|exists:dudi,id',
            'guru_id' => 'required|exists:guru,id',
            'pembimbing_industri_id' => 'nullable|exists:pembimbing_industri,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ], [
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        $this->service->editPlacement($id, $request->only('dudi_id', 'guru_id', 'pembimbing_industri_id', 'tanggal_mulai', 'tanggal_selesai'));

        return redirect()->route('penempatan.index')->with('success', 'Detail penempatan murid berhasil diperbarui.');
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
