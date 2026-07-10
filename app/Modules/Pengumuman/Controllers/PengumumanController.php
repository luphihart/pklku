<?php

namespace App\Modules\Pengumuman\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pengumuman\Services\AnnouncementService;
use App\Models\User;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    protected $service;

    public function __construct(AnnouncementService $service)
    {
        $this->service = $service;
    }

    /**
     * Display announcements list.
     */
    public function index()
    {
        $role = auth()->user()->role;

        if ($role === 'admin') {
            $announcements = $this->service->listAll();
            $users = User::where('role', '!=', 'admin')->get();
            return view('pengumuman::index', compact('announcements', 'users'));
        }

        // Student and Teacher see their active announcements
        $announcements = $this->service->getUserAnnouncements();
        return view('pengumuman::murid_index', compact('announcements'));
    }

    /**
     * Store new announcement.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'target_role' => 'required|in:semua,guru,murid,kustom',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $this->service->create(
            $request->only('judul', 'isi', 'target_role'),
            $request->get('user_ids', [])
        );

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman baru berhasil dipublikasikan.');
    }

    /**
     * Update announcement.
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'target_role' => 'required|in:semua,guru,murid,kustom',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $this->service->update(
            $id,
            $request->only('judul', 'isi', 'target_role'),
            $request->get('user_ids', [])
        );

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Delete announcement.
     */
    public function destroy(int $id)
    {
        $this->service->remove($id);
        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
