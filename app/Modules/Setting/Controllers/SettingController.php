<?php

namespace App\Modules\Setting\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Setting\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $service;

    public function __construct(SettingService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $settings = $this->service->getSettings();
        return view('setting::index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_sekolah' => 'nullable|string|max:255',
            'alamat_sekolah' => 'nullable|string',
            'nama_kepala_sekolah' => 'nullable|string|max:255',
            'nip_kepala_sekolah' => 'nullable|string|max:30',
            'kota_sekolah' => 'nullable|string|max:255',
            'footer_rapor' => 'nullable|string',
            'footer_login' => 'nullable|string|max:255',
            'jam_masuk' => 'nullable|string|max:5', // HH:MM
            'batas_terlambat' => 'nullable|string|max:5', // HH:MM
            'jam_pulang' => 'nullable|string|max:5', // HH:MM
            'tutup_jam_pulang' => 'nullable|string|max:5', // HH:MM
            'radius_presensi' => 'nullable|integer|min:10',
            'bobot_nilai_guru' => 'nullable|numeric|min:0|max:100',
            'bobot_nilai_industri' => 'nullable|numeric|min:0|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:1024',
        ]);

        $settings = $request->only([
            'nama_sekolah', 'alamat_sekolah', 'nama_kepala_sekolah', 'nip_kepala_sekolah',
            'jam_masuk', 'batas_terlambat', 'jam_pulang', 'tutup_jam_pulang',
            'radius_presensi', 'bobot_nilai_guru', 'bobot_nilai_industri', 'kota_sekolah', 'footer_rapor', 'footer_login'
        ]);

        // Auto balance weights if only one is updated
        if ($request->has('bobot_nilai_guru') && !$request->has('bobot_nilai_industri')) {
            $settings['bobot_nilai_industri'] = 100 - $request->bobot_nilai_guru;
        } elseif (!$request->has('bobot_nilai_guru') && $request->has('bobot_nilai_industri')) {
            $settings['bobot_nilai_guru'] = 100 - $request->bobot_nilai_industri;
        }

        $this->service->updateSettings($settings, $request->file('logo'));

        return redirect()->route('setting.index')->with('success', 'Konfigurasi parameter sistem berhasil diperbarui.');
    }
}
