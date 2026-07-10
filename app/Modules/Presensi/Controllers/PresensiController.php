<?php

namespace App\Modules\Presensi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Presensi\Services\AttendanceService;
use App\Modules\PKL\Models\PenempatanPkl;
use App\Modules\MasterData\Models\Kelas;
use App\Modules\MasterData\Models\Dudi;
use App\Modules\Presensi\Models\Presensi;
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    protected $service;

    public function __construct(AttendanceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display attendance dashboard or list.
     */
    public function index(Request $request)
    {
        $role = auth()->user()->role;

        if ($role === 'murid') {
            $murid = auth()->user()->murid;
            $placement = $murid ? $murid->penempatanAktif : null;
            
            $history = [];
            $today = null;

            if ($placement) {
                $history = $this->service->getHistory($placement->id);
                $today = $this->service->getToday($placement->id);
            }

            return view('presensi::murid.index', compact('placement', 'history', 'today'));
        }

        // Sisi Guru / Admin: List all attendance
        $query = Presensi::with(['penempatanPkl.murid.kelas', 'penempatanPkl.dudi']);
        
        if ($role === 'guru') {
            $guruId = auth()->user()->guru->id;
            $query->whereHas('penempatanPkl', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            });
        }

        // Filters
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        } else {
            $query->where('tanggal', now()->toDateString());
        }

        $placementQuery = PenempatanPkl::with(['murid.kelas', 'dudi'])->where('status', 'aktif');
        if ($role === 'guru') {
            $guruId = auth()->user()->guru->id;
            $placementQuery->where('guru_id', $guruId);
        }
        $activePlacements = $placementQuery->get();

        $presensis = $query->paginate(15);
        return view('presensi::index', compact('presensis', 'activePlacements'));
    }

    /**
     * Process student Check In.
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'penempatan_pkl_id' => 'required|exists:penempatan_pkl,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string', // base64 string
        ]);

        try {
            $this->service->checkIn(
                $request->penempatan_pkl_id,
                (float)$request->latitude,
                (float)$request->longitude,
                $request->photo
            );
            return response()->json(['success' => true, 'message' => 'Check In berhasil dicatat!']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Process student Check Out.
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'penempatan_pkl_id' => 'required|exists:penempatan_pkl,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string', // base64 string
        ]);

        try {
            $this->service->checkOut(
                $request->penempatan_pkl_id,
                (float)$request->latitude,
                (float)$request->longitude,
                $request->photo
            );
            return response()->json(['success' => true, 'message' => 'Check Out berhasil dicatat!']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Delete attendance log (Admin only).
     */
    public function destroy(int $id)
    {
        $presensi = Presensi::findOrFail($id);

        if ($presensi->foto_masuk && file_exists(public_path('storage/attendance/' . $presensi->foto_masuk))) {
            @unlink(public_path('storage/attendance/' . $presensi->foto_masuk));
        }
        if ($presensi->foto_pulang && file_exists(public_path('storage/attendance/' . $presensi->foto_pulang))) {
            @unlink(public_path('storage/attendance/' . $presensi->foto_pulang));
        }

        $presensi->delete();

        return redirect()->back()->with('success', 'Data presensi berhasil dihapus.');
    }

    /**
     * Store manual attendance correction (Admin / Guru).
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'penempatan_pkl_id' => 'required|exists:penempatan_pkl,id',
            'tanggal' => 'required|date',
            'jam_masuk' => 'nullable|required_without:jam_pulang|string',
            'status_masuk' => 'nullable|required_with:jam_masuk|in:tepat_waktu,terlambat',
            'jam_pulang' => 'nullable|required_without:jam_masuk|string',
            'status_pulang' => 'nullable|required_with:jam_pulang|in:tepat_waktu,pulang_cepat',
        ], [
            'jam_masuk.required_without' => 'Jam Masuk atau Jam Pulang harus diisi.',
            'jam_pulang.required_without' => 'Jam Masuk atau Jam Pulang harus diisi.',
        ]);

        // Check if attendance already exists for this student on this day
        $exists = Presensi::where('penempatan_pkl_id', $request->penempatan_pkl_id)
            ->where('tanggal', $request->tanggal)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Catatan presensi untuk murid tersebut pada tanggal terpilih sudah ada.');
        }

        // Standardise time format to HH:MM:SS
        $jamMasuk = $request->filled('jam_masuk') ? date('H:i:s', strtotime($request->jam_masuk)) : null;
        $statusMasuk = $request->filled('jam_masuk') ? $request->status_masuk : null;
        $jamPulang = $request->filled('jam_pulang') ? date('H:i:s', strtotime($request->jam_pulang)) : null;
        $statusPulang = $request->filled('jam_pulang') ? $request->status_pulang : null;

        Presensi::create([
            'penempatan_pkl_id' => $request->penempatan_pkl_id,
            'tanggal' => $request->tanggal,
            'jam_masuk' => $jamMasuk,
            'status_masuk' => $statusMasuk,
            'jam_pulang' => $jamPulang,
            'status_pulang' => $statusPulang,
            'lat_masuk' => null,
            'lng_masuk' => null,
            'lat_pulang' => null,
            'lng_pulang' => null,
            'foto_masuk' => null,
            'foto_pulang' => null,
        ]);

        return redirect()->back()->with('success', 'Koreksi presensi manual berhasil ditambahkan.');
    }

    /**
     * Update manual attendance correction (Admin / Guru).
     */
    public function updateManual(Request $request, int $id)
    {
        $request->validate([
            'jam_masuk' => 'nullable|required_without:jam_pulang|string',
            'status_masuk' => 'nullable|required_with:jam_masuk|in:tepat_waktu,terlambat',
            'jam_pulang' => 'nullable|required_without:jam_masuk|string',
            'status_pulang' => 'nullable|required_with:jam_pulang|in:tepat_waktu,pulang_cepat',
        ], [
            'jam_masuk.required_without' => 'Jam Masuk atau Jam Pulang harus diisi.',
            'jam_pulang.required_without' => 'Jam Masuk atau Jam Pulang harus diisi.',
        ]);

        $presensi = Presensi::findOrFail($id);

        $jamMasuk = $request->filled('jam_masuk') ? date('H:i:s', strtotime($request->jam_masuk)) : null;
        $statusMasuk = $request->filled('jam_masuk') ? $request->status_masuk : null;
        $jamPulang = $request->filled('jam_pulang') ? date('H:i:s', strtotime($request->jam_pulang)) : null;
        $statusPulang = $request->filled('jam_pulang') ? $request->status_pulang : null;

        $presensi->update([
            'jam_masuk' => $jamMasuk,
            'status_masuk' => $statusMasuk,
            'jam_pulang' => $jamPulang,
            'status_pulang' => $statusPulang,
        ]);

        return redirect()->back()->with('success', 'Koreksi presensi manual berhasil diperbarui.');
    }
}
