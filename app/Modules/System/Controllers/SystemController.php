<?php

namespace App\Modules\System\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\System\Services\SystemService;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    protected $service;

    public function __construct(SystemService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $logs = $this->service->getLogs();
        return view('system::index', compact('logs'));
    }

    /**
     * Download database backup.
     */
    public function downloadBackup()
    {
        try {
            $sql = $this->service->generateBackupSql();
            $filename = 'backup_db_' . time() . '.sql';

            return response($sql)
                ->header('Content-Type', 'application/sql')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses backup database: ' . $e->getMessage());
        }
    }

    /**
     * Restore database.
     */
    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:10240', // Max 10MB
        ], [
            'backup_file.required' => 'Pilih file SQL cadangan terlebih dahulu.',
            'backup_file.max' => 'Ukuran file maksimal adalah 10MB.',
        ]);

        try {
            $file = $request->file('backup_file');
            
            // Validate extension is sql
            if ($file->getClientOriginalExtension() !== 'sql') {
                return back()->with('error', 'Format file tidak valid. Harus berupa file .sql');
            }

            $this->service->restoreFromSql($file->getRealPath());

            return back()->with('success', 'Database berhasil di-restore dari cadangan!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses restore: ' . $e->getMessage());
        }
    }

    /**
     * Wipe logs.
     */
    public function clearLogs()
    {
        $this->service->wipeLogs();
        return redirect()->route('system.index')->with('success', 'Log audit berhasil dibersihkan.');
    }
}
