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
            $todayLeave = null;
            $weeklyOffQuota = 2;
            $weeklyOffUsed = 0;

            if ($placement) {
                $history = $this->service->getHistory($placement->id);
                $today = $this->service->getToday($placement->id);
                $todayLeave = $this->service->getTodayLeave($placement->id);
                $weeklyOffQuota = $this->service->getWeeklyOffDaysQuota();
                $weeklyOffUsed = $this->service->getWeeklyOffDaysUsed($placement->id);
            }

            return view('presensi::murid.index', compact('placement', 'history', 'today', 'todayLeave', 'weeklyOffQuota', 'weeklyOffUsed'));
        }

        // Sisi Guru / Admin: List all attendance
        $query = Presensi::with(['penempatanPkl.murid.kelas', 'penempatanPkl.dudi']);
        
        $guruId = $role === 'guru' ? (auth()->user()->guru?->id ?: -1) : null;
        if ($role === 'guru') {
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

        if ($request->filled('kelas_id')) {
            $kelasId = $request->kelas_id;
            $query->whereHas('penempatanPkl.murid', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        $search = trim($request->get('search', $request->get('nama', '')));
        if (!empty($search)) {
            $query->whereHas('penempatanPkl.murid', function($q) use ($search) {
                $q->where(function($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            });
        }

        $kelasList = Kelas::orderBy('nama')->get();

        $placementQuery = PenempatanPkl::with(['murid.kelas', 'dudi'])->where('status', 'aktif');
        if ($role === 'guru') {
            $placementQuery->where('guru_id', $guruId);
        }
        $activePlacements = $placementQuery->get();

        $presensis = $query->latest('id')->paginate(15)->withQueryString();
        return view('presensi::index', compact('presensis', 'activePlacements', 'kelasList'));
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

        // Ownership check: murid hanya bisa presensi untuk penempatan miliknya sendiri
        $user = auth()->user();
        if ($user->role === 'murid') {
            $muridPlacementId = $user->murid?->penempatanAktif?->id;
            if ($muridPlacementId !== (int) $request->penempatan_pkl_id) {
                return response()->json(['success' => false, 'message' => 'Anda tidak diizinkan presensi untuk penempatan ini.'], 403);
            }
        }

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

        // Ownership check: murid hanya bisa checkout untuk penempatan miliknya sendiri
        $user = auth()->user();
        if ($user->role === 'murid') {
            $muridPlacementId = $user->murid?->penempatanAktif?->id;
            if ($muridPlacementId !== (int) $request->penempatan_pkl_id) {
                return response()->json(['success' => false, 'message' => 'Anda tidak diizinkan presensi untuk penempatan ini.'], 403);
            }
        }

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
     * Process student marking today as Libur Shift / Off Day.
     */
    public function markLiburShift(Request $request)
    {
        $request->validate([
            'penempatan_pkl_id' => 'required|exists:penempatan_pkl,id',
            'alasan' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        if ($user->role === 'murid') {
            $muridPlacementId = $user->murid?->penempatanAktif?->id;
            if ($muridPlacementId !== (int) $request->penempatan_pkl_id) {
                return response()->json(['success' => false, 'message' => 'Anda tidak diizinkan menandai libur shift untuk penempatan ini.'], 403);
            }
        }

        try {
            $this->service->markLiburShift($request->penempatan_pkl_id, $request->alasan);
            return response()->json([
                'success' => true, 
                'message' => 'Status hari ini berhasil dicatat sebagai Libur Shift DUDI!'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel today's Libur Shift mark.
     */
    public function cancelLiburShift(Request $request)
    {
        $request->validate([
            'penempatan_pkl_id' => 'required|exists:penempatan_pkl,id',
        ]);

        $user = auth()->user();
        if ($user->role === 'murid') {
            $muridPlacementId = $user->murid?->penempatanAktif?->id;
            if ($muridPlacementId !== (int) $request->penempatan_pkl_id) {
                return response()->json(['success' => false, 'message' => 'Anda tidak diizinkan membatalkan libur shift untuk penempatan ini.'], 403);
            }
        }

        try {
            $this->service->cancelLiburShift($request->penempatan_pkl_id);
            return response()->json([
                'success' => true, 
                'message' => 'Tanda libur shift hari ini telah dibatalkan. Anda dapat melakukan Check In sekarang.'
            ]);
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
            'jam_masuk' => 'nullable|string',
            'status_masuk' => 'nullable|in:tepat_waktu,terlambat,libur_shift,alpha',
            'jam_pulang' => 'nullable|string',
            'status_pulang' => 'nullable|in:tepat_waktu,pulang_cepat',
        ]);

        // Check if attendance already exists for this student on this day
        $exists = Presensi::where('penempatan_pkl_id', $request->penempatan_pkl_id)
            ->where('tanggal', $request->tanggal)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Catatan presensi untuk murid tersebut pada tanggal terpilih sudah ada.');
        }

        // Standardise time format to HH:MM:SS safely
        $jamMasuk = null;
        if ($request->filled('jam_masuk')) {
            $parsed = strtotime($request->jam_masuk);
            $jamMasuk = ($parsed !== false) ? date('H:i:s', $parsed) : null;
        }
        $statusMasuk = $request->status_masuk ?: ($jamMasuk ? 'tepat_waktu' : null);

        $jamPulang = null;
        if ($request->filled('jam_pulang')) {
            $parsed = strtotime($request->jam_pulang);
            $jamPulang = ($parsed !== false) ? date('H:i:s', $parsed) : null;
        }
        $statusPulang = $jamPulang ? ($request->status_pulang ?: 'tepat_waktu') : null;

        $placement = PenempatanPkl::findOrFail($request->penempatan_pkl_id);

        Presensi::create([
            'penempatan_pkl_id' => $request->penempatan_pkl_id,
            'tanggal' => $request->tanggal,
            'jam_masuk' => $jamMasuk,
            'status_masuk' => $statusMasuk,
            'jam_pulang' => $jamPulang,
            'status_pulang' => $statusPulang,
            'shift_harian' => $placement->tipe_shift ?? 'reguler',
            'is_wfa' => 0,
            'keterangan' => $statusMasuk === 'libur_shift' ? 'Libur Shift DUDI (Input Manual)' : ($statusMasuk === 'alpha' ? 'Alpha (Input Manual)' : null),
        ]);

        return redirect()->back()->with('success', 'Catatan presensi manual berhasil disimpan.');
    }

    /**
     * Update manual attendance correction (Admin / Guru).
     */
    public function updateManual(Request $request, int $id)
    {
        $request->validate([
            'jam_masuk' => 'nullable|string',
            'status_masuk' => 'nullable|in:tepat_waktu,terlambat,libur_shift,alpha',
            'jam_pulang' => 'nullable|string',
            'status_pulang' => 'nullable|in:tepat_waktu,pulang_cepat',
        ]);

        $presensi = Presensi::findOrFail($id);

        $jamMasuk = null;
        if ($request->filled('jam_masuk')) {
            $parsed = strtotime($request->jam_masuk);
            $jamMasuk = ($parsed !== false) ? date('H:i:s', $parsed) : null;
        }
        $statusMasuk = $request->status_masuk ?: ($jamMasuk ? 'tepat_waktu' : null);

        $jamPulang = null;
        if ($request->filled('jam_pulang')) {
            $parsed = strtotime($request->jam_pulang);
            $jamPulang = ($parsed !== false) ? date('H:i:s', $parsed) : null;
        }
        $statusPulang = $jamPulang ? ($request->status_pulang ?: 'tepat_waktu') : null;

        $presensi->update([
            'jam_masuk' => $jamMasuk,
            'status_masuk' => $statusMasuk,
            'jam_pulang' => $jamPulang,
            'status_pulang' => $statusPulang,
            'keterangan' => $statusMasuk === 'libur_shift' ? 'Libur Shift DUDI (Koreksi Admin)' : ($statusMasuk === 'alpha' ? 'Alpha (Koreksi Admin)' : $presensi->keterangan),
        ]);

        return redirect()->back()->with('success', 'Data presensi berhasil diperbarui.');
    }
}
