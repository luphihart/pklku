<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\Services\MasterDataService;
use Illuminate\Http\Request;

class DudiController extends Controller
{
    protected $service;

    public function __construct(MasterDataService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $dudis = $this->service->listDudi();
        return view('masterdata::dudi.index', compact('dudis'));
    }

    public function create()
    {
        return view('masterdata::dudi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meter' => 'required|integer|min:10',
            'pic_nama' => 'required|string|max:150',
            'pic_phone' => 'required|string|max:20',
            'hari_kerja' => 'nullable|array',
        ]);

        $data = $request->all();
        $data['hari_kerja'] = implode(',', $request->input('hari_kerja', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']));

        $this->service->saveDudi($data);

        return redirect()->route('dudi.index')->with('success', 'Data mitra DUDI berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $dudi = $this->service->getDudi($id);
        return view('masterdata::dudi.edit', compact('dudi'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meter' => 'required|integer|min:10',
            'pic_nama' => 'required|string|max:150',
            'pic_phone' => 'required|string|max:20',
            'hari_kerja' => 'nullable|array',
        ]);

        $data = $request->all();
        $data['hari_kerja'] = implode(',', $request->input('hari_kerja', []));

        $this->service->editDudi($id, $data);

        return redirect()->route('dudi.index')->with('success', 'Data mitra DUDI berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $this->service->removeDudi($id);
        return redirect()->route('dudi.index')->with('success', 'Data mitra DUDI berhasil dihapus.');
    }
}
