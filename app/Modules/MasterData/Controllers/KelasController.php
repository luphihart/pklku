<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\Models\Kelas;
use App\Modules\MasterData\Models\Jurusan;
use Illuminate\Http\Request;

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
        $kelas = Kelas::findOrFail($id);

        if ($kelas->murid()->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus kelas karena masih memiliki murid terikat.');
        }

        $kelas->delete();
        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
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
            $kelas = Kelas::find($id);
            if ($kelas) {
                if ($kelas->murid()->exists()) {
                    $failed++;
                } else {
                    $kelas->delete();
                    $count++;
                }
            }
        }

        if ($failed > 0) {
            return redirect()->route('kelas.index')->with('success', $count . ' kelas berhasil dihapus. ' . $failed . ' kelas gagal dihapus karena masih memiliki murid terikat.');
        }

        return redirect()->route('kelas.index')->with('success', $count . ' kelas berhasil dihapus.');
    }
}
