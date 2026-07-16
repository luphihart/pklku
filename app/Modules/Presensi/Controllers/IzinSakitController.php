<?php

namespace App\Modules\Presensi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Presensi\Services\PermissionService;
use App\Modules\Presensi\Models\IzinSakit;
use Illuminate\Http\Request;

class IzinSakitController extends Controller
{
    protected $service;

    public function __construct(PermissionService $service)
    {
        $this->service = $service;
    }

    /**
     * List all leave permissions.
     */
    public function index(Request $request)
    {
        $role = auth()->user()->role;

        if ($role === 'murid') {
            $murid = auth()->user()->murid;
            $placement = $murid ? $murid->penempatanAktif : null;
            $history = [];
            if ($placement) {
                $history = $this->service->getHistory($placement->id);
            }
            return view('presensi::izin.murid_index', compact('placement', 'history'));
        }

        // Sisi Guru / Admin: List bimbingan requests
        $query = IzinSakit::with(['penempatanPkl.murid.kelas', 'penempatanPkl.dudi']);
        
        if ($role === 'guru') {
            $guruId = auth()->user()->guru->id;
            $query->whereHas('penempatanPkl', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            });
        }

        $permissions = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('presensi::izin.index', compact('permissions'));
    }

    /**
     * Submit leave permission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'penempatan_pkl_id' => 'required|exists:penempatan_pkl,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tipe' => 'required|in:izin,sakit',
            'alasan' => 'required|string',
            'surat' => 'required|file|mimes:jpeg,png,jpg|max:2048',
        ], [
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'surat.required' => 'Surat pendukung wajib dilampirkan.',
            'surat.mimes' => 'Format surat pendukung harus berupa gambar (JPG, JPEG, PNG).',
            'surat.max' => 'Ukuran file surat pendukung maksimal 2MB.',
        ]);

        $this->service->apply(
            $request->penempatan_pkl_id,
            $request->only('tanggal_mulai', 'tanggal_selesai', 'tipe', 'alasan'),
            $request->file('surat')
        );

        return redirect()->route('izin.index')->with('success', 'Pengajuan izin/sakit berhasil dikirim dan menunggu verifikasi.');
    }

    /**
     * Approve or reject leave request.
     */
    public function review(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'catatan_guru' => 'nullable|string',
        ]);

        $guruId = auth()->user()->role === 'guru' ? auth()->user()->guru->id : null;

        $this->service->review($id, $guruId, $request->status, $request->catatan_guru);

        return redirect()->route('izin.index')->with('success', 'Status pengajuan izin/sakit berhasil diperbarui.');
    }

    /**
     * Show edit form for revision.
     */
    public function edit(int $id)
    {
        $permission = IzinSakit::findOrFail($id);

        if (auth()->user()->role === 'murid' && $permission->penempatanPkl->murid_id !== auth()->user()->murid->id) {
            abort(403);
        }

        if (!in_array($permission->status_approval, ['pending', 'ditolak'])) {
            return redirect()->route('izin.index')->with('error', 'Hanya pengajuan pending atau ditolak yang dapat diedit.');
        }

        return view('presensi::izin.edit', compact('permission'));
    }

    /**
     * Update/Revise permission application.
     */
    public function update(Request $request, int $id)
    {
        $permission = IzinSakit::findOrFail($id);

        if (auth()->user()->role === 'murid' && $permission->penempatanPkl->murid_id !== auth()->user()->murid->id) {
            abort(403);
        }

        $rules = [
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tipe' => 'required|in:izin,sakit',
            'alasan' => 'required|string',
        ];

        if (!$permission->surat_pendukung) {
            $rules['surat'] = 'required|file|mimes:jpeg,png,jpg|max:2048';
        } else {
            $rules['surat'] = 'nullable|file|mimes:jpeg,png,jpg|max:2048';
        }

        $request->validate($rules, [
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'surat.required' => 'Surat pendukung wajib dilampirkan.',
            'surat.mimes' => 'Format surat pendukung harus berupa gambar (JPG, JPEG, PNG).',
            'surat.max' => 'Ukuran file surat pendukung maksimal 2MB.',
        ]);

        try {
            $this->service->revise($id, $request->only('tanggal_mulai', 'tanggal_selesai', 'tipe', 'alasan'), $request->file('surat'));
            return redirect()->route('izin.index')->with('success', 'Pengajuan izin/sakit berhasil diperbarui dan status di-reset ke pending.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete permission request (Admin only).
     */
    public function destroy(int $id)
    {
        $permission = IzinSakit::findOrFail($id);

        if ($permission->surat_pendukung && file_exists(public_path('storage/izin/' . $permission->surat_pendukung))) {
            @unlink(public_path('storage/izin/' . $permission->surat_pendukung));
        }

        $permission->delete();

        return redirect()->route('izin.index')->with('success', 'Pengajuan izin/sakit berhasil dihapus.');
    }
}
