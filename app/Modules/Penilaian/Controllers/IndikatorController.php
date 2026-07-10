<?php

namespace App\Modules\Penilaian\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Penilaian\Models\IndikatorPenilaian;
use Illuminate\Http\Request;

class IndikatorController extends Controller
{
    public function index(Request $request)
    {
        $tipe = $request->query('tipe');
        $query = IndikatorPenilaian::with('tujuanPembelajaran')
            ->orderBy('tipe', 'asc')
            ->orderBy('nomor_urut', 'asc')
            ->orderBy('nama', 'asc');

        if (in_array($tipe, ['guru', 'industri'])) {
            $query->where('tipe', $tipe);
        }

        $indikators = $query->paginate(15);
        $tps = \App\Modules\Penilaian\Models\TujuanPembelajaran::orderBy('nomor', 'asc')->get();
        return view('penilaian::indikator.index', compact('indikators', 'tipe', 'tps'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tujuan_pembelajaran_id' => 'nullable|exists:tujuan_pembelajaran,id',
            'nomor_urut' => 'nullable|string|max:50',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tipe' => 'required|in:guru,industri',
        ]);

        IndikatorPenilaian::create($request->only('tujuan_pembelajaran_id', 'nomor_urut', 'nama', 'deskripsi', 'tipe'));

        return redirect()->route('indikator.index')->with('success', 'Indikator penilaian berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $indikator = IndikatorPenilaian::findOrFail($id);

        $request->validate([
            'tujuan_pembelajaran_id' => 'nullable|exists:tujuan_pembelajaran,id',
            'nomor_urut' => 'nullable|string|max:50',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tipe' => 'required|in:guru,industri',
        ]);

        $indikator->update($request->only('tujuan_pembelajaran_id', 'nomor_urut', 'nama', 'deskripsi', 'tipe'));

        return redirect()->route('indikator.index')->with('success', 'Indikator penilaian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $indikator = IndikatorPenilaian::findOrFail($id);
        $indikator->delete();

        return redirect()->route('indikator.index')->with('success', 'Indikator penilaian berhasil dihapus.');
    }
}
