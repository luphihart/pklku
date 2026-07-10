<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\Services\MasterDataService;
use App\Modules\MasterData\Models\Kelas;
use Illuminate\Http\Request;

class MuridController extends Controller
{
    protected $service;

    public function __construct(MasterDataService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only('kelas_id', 'search');
        $murids = $this->service->listMurid($filters);
        $kelas = Kelas::all();

        return view('masterdata::murid.index', compact('murids', 'kelas'));
    }

    public function create()
    {
        $kelas = Kelas::all();
        return view('masterdata::murid.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'nis' => 'required|string|max:30|unique:murid,nis,NULL,id,deleted_at,NULL',
            'kelas_id' => 'required|exists:kelas,id',
            'phone' => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'password' => 'nullable|string|min:6',
        ]);

        $this->service->saveMurid($request->all());

        return redirect()->route('murid.index')->with('success', 'Data murid berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $murid = $this->service->getMurid($id);
        $kelas = Kelas::all();
        return view('masterdata::murid.edit', compact('murid', 'kelas'));
    }

    public function update(Request $request, int $id)
    {
        $murid = $this->service->getMurid($id);
        
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $murid->user_id . ',id,deleted_at,NULL',
            'nis' => 'required|string|max:30|unique:murid,nis,' . $id . ',id,deleted_at,NULL',
            'kelas_id' => 'required|exists:kelas,id',
            'phone' => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'password' => 'nullable|string|min:6',
        ]);

        $this->service->editMurid($id, $request->all());

        return redirect()->route('murid.index')->with('success', 'Data murid berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $this->service->removeMurid($id);
        return redirect()->route('murid.index')->with('success', 'Data murid berhasil dihapus.');
    }

    public function destroyBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu murid untuk dihapus.');
        }

        $count = 0;
        foreach ($ids as $id) {
            try {
                $this->service->removeMurid($id);
                $count++;
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        return redirect()->route('murid.index')->with('success', $count . ' murid berhasil dihapus.');
    }

    public function resetPassword(int $id)
    {
        $murid = \App\Modules\MasterData\Models\Murid::findOrFail($id);
        $user = $murid->user;
        if ($user) {
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make('siswa123')
            ]);
        }
        return redirect()->route('murid.index')->with('success', 'Password murid ' . $murid->nama . ' berhasil direset menjadi "siswa123".');
    }
}
