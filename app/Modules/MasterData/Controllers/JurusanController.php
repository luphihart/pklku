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
        $jurusan = Jurusan::findOrFail($id);
        
        if ($jurusan->kelas()->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus jurusan karena masih memiliki kelas terikat.');
        }

        $jurusan->delete();
        return redirect()->route('jurusan.index')->with('success', 'Jurusan berhasil dihapus.');
    }
}
