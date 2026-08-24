<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Audit log helper.
     */
    protected function logActivity(string $aktivitas, ?int $userId = null): void
    {
        $uId = $userId ?? Auth::id();
        
        try {
            \App\Modules\System\Models\AuditLog::create([
                'user_id' => $uId,
                'aktivitas' => $aktivitas,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Unknown',
                'payload' => null,
            ]);
        } catch (\Throwable $e) {
            // Ignore if audit_logs table does not exist or fails
        }
    }
}
