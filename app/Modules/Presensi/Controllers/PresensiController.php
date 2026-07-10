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

        $presensis = $query->paginate(15);
        return view('presensi::index', compact('presensis'));
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
}
