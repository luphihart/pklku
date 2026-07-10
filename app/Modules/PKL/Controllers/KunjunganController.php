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

        $kunjungans = $query->paginate(15);
        
        // Placements for dropdown (to assign visitations)
        $placementsQuery = \App\Modules\PKL\Models\PenempatanPkl::with(['murid', 'dudi'])->where('status', 'aktif');
        if ($role === 'guru') {
            $placementsQuery->where('guru_id', auth()->user()->guru->id);
        }
        $placements = $placementsQuery->get();

        return view('pkl::kunjungan.index', compact('kunjungans', 'placements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'penempatan_pkl_id' => 'required|exists:penempatan_pkl,id',
            'tanggal' => 'required|date',
            'deskripsi_kunjungan' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $this->service->recordVisitation(
            $request->penempatan_pkl_id,
            $request->only('tanggal', 'deskripsi_kunjungan', 'latitude', 'longitude'),
            $request->file('foto')
        );

        return redirect()->route('kunjungan.index')->with('success', 'Kunjungan monitoring berhasil dicatat.');
    }
}
