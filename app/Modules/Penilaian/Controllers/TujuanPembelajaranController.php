<?php

namespace App\Modules\Penilaian\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Penilaian\Models\TujuanPembelajaran;
use Illuminate\Http\Request;

class TujuanPembelajaranController extends Controller
{
    public function index()
    {
        $tps = TujuanPembelajaran::orderBy('nomor', 'asc')->get();
        return view('penilaian::tujuan_pembelajaran.index', compact('tps'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor' => 'nullable|string|max:50',
            'nama' => 'required|string|max:255',
        ]);

        TujuanPembelajaran::create($request->only('nomor', 'nama'));

        return redirect()->route('tujuan-pembelajaran.index')->with('success', 'Tujuan Pembelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $tp = TujuanPembelajaran::findOrFail($id);

        $request->validate([
            'nomor' => 'nullable|string|max:50',
            'nama' => 'required|string|max:255',
        ]);

        $tp->update($request->only('nomor', 'nama'));

        return redirect()->route('tujuan-pembelajaran.index')->with('success', 'Tujuan Pembelajaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tp = TujuanPembelajaran::findOrFail($id);
        $tp->delete();

        return redirect()->route('tujuan-pembelajaran.index')->with('success', 'Tujuan Pembelajaran berhasil dihapus.');
    }
}
