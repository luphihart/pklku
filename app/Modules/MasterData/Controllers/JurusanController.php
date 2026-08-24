<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusans = Jurusan::orderBy('kode', 'asc')->paginate(15);
        return view('masterdata::jurusan.index', compact('jurusans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:50|unique:jurusan,kode',
            'nama' => 'required|string|max:255',
        ]);

        Jurusan::create($request->only('kode', 'nama'));

        return redirect()->route('jurusan.index')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $jurusan = Jurusan::findOrFail($id);
        
        $request->validate([
            'kode' => 'required|string|max:50|unique:jurusan,kode,' . $id,
            'nama' => 'required|string|max:255',
        ]);

        $jurusan->update($request->only('kode', 'nama'));

        return redirect()->route('jurusan.index')->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            $jurusan = Jurusan::findOrFail($id);
            
            $activeKelasCount = $jurusan->kelas()->count();
            if ($activeKelasCount > 0) {
                return redirect()->back()->with('error', "Tidak dapat menghapus jurusan '{$jurusan->nama}' karena masih memiliki {$activeKelasCount} kelas terikat.");
            }

            $jurusan->delete();
            return redirect()->route('jurusan.index')->with('success', "Jurusan '{$jurusan->nama}' berhasil dihapus.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal menghapus jurusan: ' . $e->getMessage());
        }
    }
}
