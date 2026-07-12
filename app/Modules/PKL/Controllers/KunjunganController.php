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
            $guruId = auth()->user()->guru->id;
            $query->whereHas('penempatanPkl', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            });
        }

        $kunjungans = $query->orderBy('tanggal', 'desc')->paginate(15);
        
        // Placements for dropdown (to assign visitations)
        $placementsQuery = \App\Modules\PKL\Models\PenempatanPkl::with(['murid', 'dudi'])->where('status', 'aktif');
        if ($role === 'guru') {
            $placementsQuery->where('guru_id', auth()->user()->guru->id);
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
        $kunjungan = KunjunganMonitoring::findOrFail($id);
        
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
            if ($filepath && file_exists(public_path('storage/kunjungan/' . $filepath))) {
                @unlink(public_path('storage/kunjungan/' . $filepath));
            }
            $filename = 'kunjungan_' . $request->penempatan_pkl_id . '_' . time() . '.' . $request->file('foto')->getClientOriginalExtension();
            $request->file('foto')->move(public_path('storage/kunjungan'), $filename);
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
        $kunjungan = KunjunganMonitoring::findOrFail($id);
        
        if ($kunjungan->foto_kunjungan && file_exists(public_path('storage/kunjungan/' . $kunjungan->foto_kunjungan))) {
            @unlink(public_path('storage/kunjungan/' . $kunjungan->foto_kunjungan));
        }

        $kunjungan->delete();

        return redirect()->route('kunjungan.index')->with('success', 'Kunjungan pembimbing berhasil dihapus.');
    }
}
