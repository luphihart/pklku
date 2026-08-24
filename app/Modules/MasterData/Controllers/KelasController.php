<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\Models\Kelas;
use App\Modules\MasterData\Models\Jurusan;
use App\Modules\MasterData\Models\Murid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KelasController extends Controller
{
    public function index()
    {
        $kelases = Kelas::with('jurusan')->orderBy('nama', 'asc')->paginate(15);
        $jurusans = Jurusan::all();
        return view('masterdata::kelas.index', compact('kelases', 'jurusans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:kelas,nama',
            'jurusan_id' => 'required|exists:jurusan,id',
        ]);

        Kelas::create($request->only('nama', 'jurusan_id'));

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100|unique:kelas,nama,' . $id,
            'jurusan_id' => 'required|exists:jurusan,id',
        ]);

        $kelas->update($request->only('nama', 'jurusan_id'));

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            $kelas = Kelas::findOrFail($id);

            // Cek apakah masih ada murid aktif di kelas ini
            $activeMuridCount = $kelas->murid()->count();
            if ($activeMuridCount > 0) {
                return redirect()->back()->with('error', "Tidak dapat menghapus kelas '{$kelas->nama}' karena masih memiliki {$activeMuridCount} murid aktif di dalamnya.");
            }

            DB::transaction(function() use ($kelas, $id) {
                // Hapus permanen murid berstatus soft-deleted yang masih mengikat kelas ini agar tidak melanggar foreign key MySQL
                Murid::onlyTrashed()->where('kelas_id', $id)->forceDelete();
                $kelas->delete();
            });

            return redirect()->route('kelas.index')->with('success', "Kelas '{$kelas->nama}' berhasil dihapus.");
        } catch (\Throwable $e) {
            Log::error("Gagal menghapus kelas ID {$id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus kelas: ' . $e->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu kelas untuk dihapus.');
        }

        $count = 0;
        $failed = 0;
        foreach ($ids as $id) {
            try {
                $kelas = Kelas::find($id);
                if ($kelas) {
                    if ($kelas->murid()->exists()) {
                        $failed++;
                    } else {
                        DB::transaction(function() use ($kelas, $id) {
                            Murid::onlyTrashed()->where('kelas_id', $id)->forceDelete();
                            $kelas->delete();
                        });
                        $count++;
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Gagal menghapus bulk kelas ID {$id}: " . $e->getMessage());
                $failed++;
            }
        }

        if ($failed > 0) {
            return redirect()->route('kelas.index')->with('warning', "{$count} kelas berhasil dihapus. {$failed} kelas gagal dihapus karena masih memiliki murid aktif terikat.");
        }

        return redirect()->route('kelas.index')->with('success', "{$count} kelas berhasil dihapus.");
    }
}

