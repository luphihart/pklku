<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\Models\TahunAjaran;
use App\Modules\MasterData\Models\Jurusan;
use App\Modules\MasterData\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahunAjarans = TahunAjaran::all();
        $jurusans = Jurusan::all();
        $kelas = Kelas::with('jurusan')->get();

        return view('masterdata::tahun_ajaran.index', compact('tahunAjarans', 'jurusans', 'kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun' => 'required|string|max:9|unique:tahun_ajaran,tahun',
            'semester' => 'required|in:ganjil,genap',
        ]);

        TahunAjaran::create([
            'tahun' => $request->tahun,
            'semester' => $request->semester,
            'is_aktif' => false,
        ]);

        return back()->with('success', 'Tahun ajaran baru berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        // Toggle active status: only ONE can be active at a time!
        DB::transaction(function() use ($id) {
            // Set all to inactive
            TahunAjaran::query()->update(['is_aktif' => false]);
            
            // Set specific one to active
            $ta = TahunAjaran::findOrFail($id);
            $ta->update(['is_aktif' => true]);
        });

        return back()->with('success', 'Tahun ajaran aktif berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $ta = TahunAjaran::findOrFail($id);
        if ($ta->is_aktif) {
            return back()->with('error', 'Tahun ajaran aktif tidak dapat dihapus.');
        }

        // Check if there are active placements associated with this year
        $hasPlacements = DB::table('penempatan_pkl')->where('tahun_ajaran_id', $id)->exists();
        if ($hasPlacements) {
            return back()->with('error', 'Tidak dapat menghapus tahun ajaran karena masih memiliki bimbingan/penempatan murid terikat.');
        }

        $ta->delete();
        return back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
