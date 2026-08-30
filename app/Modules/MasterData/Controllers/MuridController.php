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
        $filters = $request->only('kelas_id', 'search', 'sort_by', 'order', 'sort');
        $murids = $this->service->listMurid($filters);
        $kelas = Kelas::all();

        return view('masterdata::murid.index', compact('murids', 'kelas'));
    }

    public function export(Request $request)
    {
        $exporter = new \App\Modules\MasterData\Exports\MuridExport($request->only('kelas_id', 'search'));
        return $exporter->generate();
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

        $this->service->saveMurid($request->only(['nama', 'email', 'nis', 'kelas_id', 'phone', 'tanggal_lahir', 'password']));

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
            'email' => 'required|email|unique:users,email,' . ($murid->user_id ?? 'NULL') . ',id,deleted_at,NULL',
            'nis' => 'required|string|max:30|unique:murid,nis,' . $id . ',id,deleted_at,NULL',
            'kelas_id' => 'required|exists:kelas,id',
            'phone' => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'password' => 'nullable|string|min:6',
        ]);

        $this->service->editMurid($id, $request->only(['nama', 'email', 'nis', 'kelas_id', 'phone', 'tanggal_lahir', 'password']));

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
        $murid = $this->service->getMurid($id);
        $defaultPassword = \App\Modules\Setting\Models\Setting::where('key', 'default_password_siswa')->value('value') ?: 'siswa123';
        
        $user = $murid->user;
        if ($user) {
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($defaultPassword)
            ]);
        }
        return redirect()->route('murid.index')->with('success', 'Password murid ' . $murid->nama . ' berhasil direset menjadi "' . $defaultPassword . '".');
    }

    public function resetPasswordBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu murid untuk direset password.');
        }

        $defaultPassword = \App\Modules\Setting\Models\Setting::where('key', 'default_password_siswa')->value('value') ?: 'siswa123';
        $murids = \App\Modules\MasterData\Models\Murid::whereIn('id', $ids)->get();
        $userIds = $murids->pluck('user_id')->filter()->toArray();
        $count = 0;

        if (!empty($userIds)) {
            \App\Models\User::whereIn('id', $userIds)->update([
                'password' => \Illuminate\Support\Facades\Hash::make($defaultPassword)
            ]);
            $count = count($userIds);
        }

        return redirect()->route('murid.index')->with('success', $count . ' password murid berhasil direset menjadi "' . $defaultPassword . '".');
    }
}
