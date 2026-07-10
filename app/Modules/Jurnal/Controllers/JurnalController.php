<?php

namespace App\Modules\Jurnal\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Jurnal\Services\JournalService;
use Illuminate\Http\Request;

class JurnalController extends Controller
{
    protected $service;

    public function __construct(JournalService $service)
    {
        $this->service = $service;
    }

    /**
     * Display list of journals.
     */
    public function index(Request $request)
    {
        $role = auth()->user()->role;

        if ($role === 'murid') {
            $murid = auth()->user()->murid;
            $placement = $murid ? $murid->penempatanAktif : null;
            $journals = [];
            if ($placement) {
                $journals = $this->service->getStudentHistory($placement->id);
            }
            return view('jurnal::murid_index', compact('placement', 'journals'));
        }

        // Guru / Admin: List bimbingan journals
        $status = $request->get('status');
        $guruId = auth()->user()->role === 'guru' ? auth()->user()->guru->id : null;
        $journals = $this->service->getTeacherReviews($guruId, $status);

        return view('jurnal::index', compact('journals'));
    }

    /**
     * Store new journal.
     */
    public function store(Request $request)
    {
        $request->validate([
            'penempatan_pkl_id' => 'required|exists:penempatan_pkl,id',
            'tanggal' => 'required|date',
            'deskripsi_aktivitas' => 'required|string',
            'foto' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ], [
            'deskripsi_aktivitas.required' => 'Deskripsi aktivitas wajib diisi.',
            'foto.required' => 'Bukti kegiatan wajib dilampirkan.',
            'foto.mimes' => 'Format bukti kegiatan harus JPG, JPEG, PNG, atau PDF.',
            'foto.max' => 'Ukuran bukti kegiatan maksimal 2MB.',
        ]);

        $this->service->saveEntry(
            $request->penempatan_pkl_id,
            $request->only('tanggal', 'deskripsi_aktivitas'),
            $request->file('foto')
        );

        return redirect()->route('jurnal.index')->with('success', 'Jurnal harian berhasil dikirim.');
    }

    /**
     * Show edit form.
     */
    public function edit(int $id)
    {
        $journal = $this->service->getDetail($id);
        
        // Policy check: only owner murid can edit
        if (auth()->user()->role === 'murid' && $journal->penempatanPkl->murid_id !== auth()->user()->murid->id) {
            abort(403);
        }

        return view('jurnal::edit', compact('journal'));
    }

    /**
     * Update journal entry.
     */
    public function update(Request $request, int $id)
    {
        $journal = $this->service->getDetail($id);

        $rules = [
            'deskripsi_aktivitas' => 'required|string',
        ];

        if (!$journal->foto_kegiatan) {
            $rules['foto'] = 'required|file|mimes:jpeg,png,jpg,pdf|max:2048';
        } else {
            $rules['foto'] = 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048';
        }

        $request->validate($rules, [
            'deskripsi_aktivitas.required' => 'Deskripsi aktivitas wajib diisi.',
            'foto.required' => 'Bukti kegiatan wajib dilampirkan.',
            'foto.mimes' => 'Format bukti kegiatan harus JPG, JPEG, PNG, atau PDF.',
            'foto.max' => 'Ukuran bukti kegiatan maksimal 2MB.',
        ]);

        try {
            $this->service->editEntry($id, $request->only('deskripsi_aktivitas'), $request->file('foto'));
            return redirect()->route('jurnal.index')->with('success', 'Jurnal harian berhasil diperbarui.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Verify/Review journal by Guru bimbingan.
     */
    public function verify(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak,revisi',
            'catatan_verifikasi' => 'required_if:status,revisi,ditolak|nullable|string',
        ]);

        $guruId = auth()->user()->role === 'guru' ? auth()->user()->guru->id : null;

        $this->service->verifyEntry(
            $id,
            $guruId,
            $request->status,
            $request->catatan_verifikasi
        );

        return redirect()->route('jurnal.index')->with('success', 'Jurnal bimbingan berhasil diverifikasi.');
    }

    /**
     * Delete journal.
     */
    public function destroy(int $id)
    {
        if (!in_array(auth()->user()->role, ['guru', 'admin'])) {
            abort(403);
        }

        $journal = $this->service->getDetail($id);
        
        if ($journal->foto_kegiatan && file_exists(public_path('storage/jurnal/' . $journal->foto_kegiatan))) {
            @unlink(public_path('storage/jurnal/' . $journal->foto_kegiatan));
        }

        $journal->delete();

        return redirect()->route('jurnal.index')->with('success', 'Jurnal harian siswa berhasil dihapus.');
    }
}
