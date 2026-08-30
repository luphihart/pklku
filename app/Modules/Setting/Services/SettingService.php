<?php

namespace App\Modules\Setting\Services;

use App\Modules\Setting\Repositories\SettingRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class SettingService
{
    protected $repo;

    public function __construct(SettingRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getSettings() { return $this->repo->getAllSettings(); }

    /**
     * Update settings and log to audit logs.
     */
    public function updateSettings(array $settings, $logoFile = null, bool $hapusLogo = false)
    {
        $targetDir = public_path('storage/branding');
        if (!file_exists($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        if ($hapusLogo) {
            $settings['logo_sekolah'] = null;
        } elseif ($logoFile) {
            // Process and save logo
            $filename = 'logo_' . time() . '.' . $logoFile->getClientOriginalExtension();
            $logoFile->move($targetDir, $filename);
            
            // Set the logo key setting
            $settings['logo_sekolah'] = $filename;
        }

        $this->repo->saveSettings($settings);

        try {
            \Illuminate\Support\Facades\Cache::forget('global_school_settings');
            \Illuminate\Support\Facades\Cache::forget('global_setting_hari_kerja');
        } catch (\Throwable $e) {
            // Ignore
        }

        $this->logActivity("Mengubah konfigurasi sistem dan parameter branding aplikasi");
    }

    /**
     * Audit log helper.
     */
    private function logActivity(string $aktivitas)
    {
        try {
            \App\Modules\System\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'aktivitas' => $aktivitas,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Unknown',
                'payload' => null,
            ]);
        } catch (\Throwable $e) {
            // Ignore
        }
    }
}
