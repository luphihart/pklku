<?php

namespace App\Modules\PKL\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PKL\Services\PlacementService;
use App\Modules\PKL\Models\KunjunganMonitoring;
use Illuminate\Http\Request;

class KunjunganController extends Controller
{
    protected $service;

    public function __construct(PlacementService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $role = auth()->user()->role;
        $query = KunjunganMonitoring::with(['penempatanPkl.murid', 'penempatanPkl.dudi', 'penempatanPkl.guru']);

        if ($role === 'guru') {
            // Filter by teacher
            $guruId = auth()->user()->guru?->id ?: -1;
            $query->whereHas('penempatanPkl', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            });
        }

        $kunjungans = $query->orderBy('tanggal', 'desc')->paginate(15);
        
        // Placements for dropdown (to assign visitations)
        $placementsQuery = \App\Modules\PKL\Models\PenempatanPkl::with(['murid', 'dudi'])->where('status', 'aktif');
        if ($role === 'guru') {
            $guruId = auth()->user()->guru?->id ?: -1;
            $placementsQuery->where('guru_id', $guruId);
        }
        $placements = $placementsQuery->get();
        $dudiPlacements = $placements->unique('dudi_id');

        return view('pkl::kunjungan.index', compact('kunjungans', 'placements', 'dudiPlacements'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('kunjungan.index')->with('error', 'Admin tidak diperkenankan mencatat kunjungan.');
        }

        $request->validate([
            'penempatan_pkl_id' => 'required|exists:penempatan_pkl,id',
            'tanggal' => 'required|date',
            'jenis_kunjungan' => 'required|in:Penjajakan Kerja Sama,Penyerahan Murid,Monitoring Berkala,Penarikan PKL',
            'deskripsi_kunjungan' => 'required|string',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'foto.required' => 'Foto bukti kunjungan wajib diunggah.',
            'jenis_kunjungan.required' => 'Jenis kunjungan wajib dipilih.',
            'deskripsi_kunjungan.required' => 'Catatan kunjungan wajib diisi.',
        ]);

        $this->service->recordVisitation(
            $request->penempatan_pkl_id,
            $request->only('tanggal', 'jenis_kunjungan', 'deskripsi_kunjungan'),
            $request->file('foto')
        );

        return redirect()->route('kunjungan.index')->with('success', 'Kunjungan pembimbing berhasil dicatat.');
    }

    public function update(Request $request, int $id)
    {
        $kunjungan = KunjunganMonitoring::with('penempatanPkl')->findOrFail($id);

        if (auth()->user()->role === 'guru' && $kunjungan->penempatanPkl?->guru_id !== auth()->user()->guru?->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit kunjungan ini.');
        }
        
        $request->validate([
            'penempatan_pkl_id' => 'required|exists:penempatan_pkl,id',
            'tanggal' => 'required|date',
            'jenis_kunjungan' => 'required|in:Penjajakan Kerja Sama,Penyerahan Murid,Monitoring Berkala,Penarikan PKL',
            'deskripsi_kunjungan' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'jenis_kunjungan.required' => 'Jenis kunjungan wajib dipilih.',
            'deskripsi_kunjungan.required' => 'Catatan kunjungan wajib diisi.',
        ]);

        $filepath = $kunjungan->foto_kunjungan;
        if ($request->hasFile('foto')) {
            $dirPath = public_path('storage/kunjungan');
            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0755, true);
            }
            if ($filepath && file_exists($dirPath . '/' . $filepath)) {
                @unlink($dirPath . '/' . $filepath);
            }
            $filename = 'kunjungan_' . $request->penempatan_pkl_id . '_' . time() . '.' . $request->file('foto')->getClientOriginalExtension();
            $request->file('foto')->move($dirPath, $filename);
            $filepath = $filename;
        }

        $kunjungan->update([
            'penempatan_pkl_id' => $request->penempatan_pkl_id,
            'tanggal' => $request->tanggal,
            'jenis_kunjungan' => $request->jenis_kunjungan,
            'deskripsi_kunjungan' => $request->deskripsi_kunjungan,
            'foto_kunjungan' => $filepath,
        ]);

        return redirect()->route('kunjungan.index')->with('success', 'Kunjungan pembimbing berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $kunjungan = KunjunganMonitoring::with('penempatanPkl')->findOrFail($id);
        
        if (auth()->user()->role === 'guru' && $kunjungan->penempatanPkl?->guru_id !== auth()->user()->guru?->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus kunjungan ini.');
        }

        if ($kunjungan->foto_kunjungan && file_exists(public_path('storage/kunjungan/' . $kunjungan->foto_kunjungan))) {
            @unlink(public_path('storage/kunjungan/' . $kunjungan->foto_kunjungan));
        }

        $kunjungan->delete();

        return redirect()->route('kunjungan.index')->with('success', 'Kunjungan pembimbing berhasil dihapus.');
    }

    public function exportPdf()
    {
        $role = auth()->user()->role;
        $query = KunjunganMonitoring::with(['penempatanPkl.murid', 'penempatanPkl.dudi', 'penempatanPkl.guru']);

        if ($role === 'guru') {
            $guruId = auth()->user()->guru?->id ?: -1;
            $query->whereHas('penempatanPkl', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            });
        }

        $kunjungans = $query->orderBy('tanggal', 'desc')->get();
        $branding = $this->getBranding();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pkl::kunjungan.pdf', compact('kunjungans', 'branding'));
        return $pdf->download('rekap_kunjungan_dudi_' . time() . '.pdf');
    }

    private function getBranding(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('school_branding_kunjungan', now()->addHours(24), function() {
            $settings = \App\Modules\Setting\Models\Setting::whereIn('key', [
                'nama_sekolah', 'alamat_sekolah', 'nama_kepala_sekolah', 'nip_kepala_sekolah', 'kota_sekolah'
            ])->pluck('value', 'key');

            return [
                'nama_sekolah' => $settings->get('nama_sekolah') ?: 'SMK NEGERI 1 JAKARTA',
                'alamat_sekolah' => $settings->get('alamat_sekolah') ?: 'Jl. Teknologi Canggih No. 42, Kota Digital',
                'kepala_sekolah' => $settings->get('nama_kepala_sekolah') ?: 'Dr. H. Akhmad Yusuf, M.T.',
                'nip_kepala_sekolah' => $settings->get('nip_kepala_sekolah') ?: '198001012005011001',
                'kota_sekolah' => $settings->get('kota_sekolah') ?: 'Pati',
            ];
        });
    }
}
