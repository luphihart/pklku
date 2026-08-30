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

    /**
     * Sanitasi input koordinat dari user:
     * - Trim spasi
     * - Ganti koma desimal menjadi titik (e.g. "-6,223056" → "-6.223056")
     * - Batasi hingga 7 digit desimal agar sesuai kolom DECIMAL(10,7)
     */
    private function sanitizeCoordinate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        // Normalkan: ganti koma desimal → titik, hilangkan spasi
        $value = str_replace(',', '.', trim($value));

        // Pastikan hanya karakter numerik yang valid (angka, titik, minus)
        if (!preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            return null;
        }

        // Batasi presisi ke 7 digit desimal
        return number_format((float) $value, 7, '.', '');
    }

    public function index(Request $request)
    {
        $filters = $request->only('search', 'sort', 'sort_by', 'order');
        $dudis = $this->service->listDudi($filters);
        return view('masterdata::dudi.index', compact('dudis'));
    }

    public function export(Request $request)
    {
        $exporter = new \App\Modules\MasterData\Exports\DudiExport($request->only('search', 'sort'));
        return $exporter->generate();
    }

    public function create()
    {
        return view('masterdata::dudi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'         => 'required|string|max:255',
            'alamat'       => 'required|string',
            'latitude'     => 'required|numeric',
            'longitude'    => 'required|numeric',
            'radius_meter' => 'required|integer|min:10',
            'pic_nama'     => 'required|string|max:150',
            'pic_phone'    => 'required|string|max:20',
            'hari_kerja'   => 'nullable|array',
        ]);

        $data = $request->only(['nama', 'alamat', 'radius_meter', 'pic_nama', 'pic_phone']);
        $data['latitude']   = $this->sanitizeCoordinate($request->input('latitude'));
        $data['longitude']  = $this->sanitizeCoordinate($request->input('longitude'));
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
            'nama'         => 'required|string|max:255',
            'alamat'       => 'required|string',
            'latitude'     => 'required|numeric',
            'longitude'    => 'required|numeric',
            'radius_meter' => 'required|integer|min:10',
            'pic_nama'     => 'required|string|max:150',
            'pic_phone'    => 'required|string|max:20',
            'hari_kerja'   => 'nullable|array',
        ]);

        $data = $request->only(['nama', 'alamat', 'radius_meter', 'pic_nama', 'pic_phone']);
        $data['latitude']   = $this->sanitizeCoordinate($request->input('latitude'));
        $data['longitude']  = $this->sanitizeCoordinate($request->input('longitude'));
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
