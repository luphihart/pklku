<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\Services\MasterDataService;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    protected $service;

    public function __construct(MasterDataService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only('search', 'sort_by', 'order', 'sort');
        $gurus = $this->service->listGuru($filters);
        return view('masterdata::guru.index', compact('gurus'));
    }

    public function create()
    {
        return view('masterdata::guru.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'nip' => 'nullable|string|max:30|unique:guru,nip,NULL,id,deleted_at,NULL',
            'phone' => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'password' => 'nullable|string|min:6',
        ]);

        $this->service->saveGuru($request->only(['nama', 'email', 'nip', 'phone', 'tanggal_lahir', 'password']));

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $guru = $this->service->getGuru($id);
        return view('masterdata::guru.edit', compact('guru'));
    }

    public function update(Request $request, int $id)
    {
        $guru = $this->service->getGuru($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $guru->user_id . ',id,deleted_at,NULL',
            'nip' => 'nullable|string|max:30|unique:guru,nip,' . $id . ',id,deleted_at,NULL',
            'phone' => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'password' => 'nullable|string|min:6',
        ]);

        $this->service->editGuru($id, $request->only(['nama', 'email', 'nip', 'phone', 'tanggal_lahir', 'password']));

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $this->service->removeGuru($id);
        return redirect()->route('guru.index')->with('success', 'Data guru berhasil dihapus.');
    }

    public function destroyBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu guru untuk dihapus.');
        }

        $count = 0;
        foreach ($ids as $id) {
            try {
                $this->service->removeGuru($id);
                $count++;
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        return redirect()->route('guru.index')->with('success', $count . ' guru berhasil dihapus.');
    }

    public function resetPassword(int $id)
    {
        $guru = \App\Modules\MasterData\Models\Guru::findOrFail($id);
        $defaultPassword = \App\Modules\Setting\Models\Setting::where('key', 'default_password_guru')->value('value')
            ?: (\App\Modules\Setting\Models\Setting::where('key', 'default_password_siswa')->value('value') ?: 'guru123');
        
        $user = $guru->user;
        if ($user) {
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($defaultPassword)
            ]);
        }
        return redirect()->route('guru.index')->with('success', 'Password guru ' . $guru->nama . ' berhasil direset menjadi "' . $defaultPassword . '".');
    }

    public function resetPasswordBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu guru untuk direset password.');
        }

        $defaultPassword = \App\Modules\Setting\Models\Setting::where('key', 'default_password_guru')->value('value')
            ?: (\App\Modules\Setting\Models\Setting::where('key', 'default_password_siswa')->value('value') ?: 'guru123');
        
        $gurus = \App\Modules\MasterData\Models\Guru::whereIn('id', $ids)->get();
        $userIds = $gurus->pluck('user_id')->filter()->toArray();
        $count = 0;

        if (!empty($userIds)) {
            \App\Models\User::whereIn('id', $userIds)->update([
                'password' => \Illuminate\Support\Facades\Hash::make($defaultPassword)
            ]);
            $count = count($userIds);
        }

        return redirect()->route('guru.index')->with('success', $count . ' password guru berhasil direset menjadi "' . $defaultPassword . '".');
    }
}
