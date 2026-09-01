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

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'role', 'kategori', 'tanggal_mulai', 'tanggal_selesai']);
        $logs = $this->service->getLogs($filters);
        $logs->withQueryString();

        return view('system::index', compact('logs', 'filters'));
    }

    /**
     * Export audit logs to CSV (Excel compatible with UTF-8 BOM).
     */
    public function exportLogs(Request $request)
    {
        $filters = $request->only(['search', 'role', 'kategori', 'tanggal_mulai', 'tanggal_selesai']);
        $logs = $this->service->getLogsForExport($filters);

        $filename = 'audit_log_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Header row
            fputcsv($file, ['No', 'Waktu (WIB)', 'Nama Pengguna', 'Email', 'Role', 'Aktivitas', 'IP Address', 'Perangkat (User Agent)', 'Detail Payload']);

            foreach ($logs as $index => $log) {
                $payloadText = '';
                if ($log->payload) {
                    $payloadText = is_array($log->payload) ? json_encode($log->payload, JSON_UNESCAPED_UNICODE) : (string)$log->payload;
                }

                fputcsv($file, [
                    $index + 1,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user ? $log->user->name : 'Sistem Otomatis',
                    $log->user ? $log->user->email : '-',
                    $log->user ? ucfirst($log->user->role) : 'System',
                    $log->aktivitas,
                    $log->ip_address,
                    $log->user_agent,
                    $payloadText
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
     * Wipe logs (with retention options).
     */
    public function clearLogs(Request $request)
    {
        $retention = $request->input('retention', 'all');
        $days = null;

        if (in_array($retention, ['30', '90', '180'])) {
            $days = (int) $retention;
        }

        $this->service->wipeLogs($days);

        $msg = $days 
            ? "Log audit yang lebih lama dari {$days} hari berhasil dibersihkan." 
            : 'Seluruh riwayat log audit berhasil dibersihkan.';

        return redirect()->route('system.index')->with('success', $msg);
    }

    /**
     * Wipe entire database (fresh state).
     */
    public function wipeDatabase(Request $request)
    {
        $request->validate([
            'confirmation_word' => 'required|string',
        ]);

        if (strtoupper($request->confirmation_word) !== 'KOSONGKAN') {
            return back()->with('error', 'Konfirmasi kata salah. Anda harus mengetik kata KOSONGKAN.');
        }

        try {
            $this->service->wipeDatabase();

            // Clear session and log out
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Seluruh database berhasil dikosongkan! Silakan masuk kembali menggunakan akun Administrator default (admin@pklsmk.sch.id / admin123).');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengosongkan database: ' . $e->getMessage());
        }
    }
}
