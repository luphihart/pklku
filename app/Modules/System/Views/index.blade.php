@extends('layouts.admin')

@section('title', 'Utilitas Sistem & Keamanan - PKLku')
@section('page_title', 'Utilitas Sistem & Keamanan')

@section('content')
@php
if (!function_exists('formatAktivitas')) {
    function formatAktivitas($text) {
        if (empty($text)) return '-';
        // Clean IDs and technical text
        $text = preg_replace('/dengan ID: \d+/', '', $text);
        $text = preg_replace('/ID: \d+/', '', $text);
        $text = preg_replace('/penempatan ID: \d+/', '', $text);
        $text = preg_replace('/murid ID: \d+/', '', $text);
        $text = preg_replace('/ke DUDI ID: \d+/', '', $text);

        // Map exact phrases
        $replacements = [
            'Login Sukses' => 'Berhasil masuk ke dalam sistem',
            'Logout' => 'Keluar dari sistem',
            'Mengubah Password' => 'Mengubah password keamanan akun',
            'Memperbarui Foto Profil' => 'Memperbarui foto profil akun',
            'Mengubah konfigurasi sistem dan parameter branding aplikasi' => 'Memperbarui pengaturan sekolah & branding',
            'Menulis jurnal kegiatan harian baru, tanggal:' => 'Membuat laporan jurnal harian baru, tanggal:',
            'Mengubah jurnal kegiatan harian, tanggal:' => 'Memperbarui laporan jurnal harian, tanggal:',
            'Memverifikasi jurnal' => 'Memverifikasi laporan jurnal harian',
            'dengan status: disetujui' => 'dengan status disetujui',
            'dengan status: ditolak' => 'dengan status ditolak',
            'dengan status: revisi' => 'dengan status perlu revisi',
            'Menambahkan murid baru:' => 'Menambahkan data siswa baru:',
            'Mengubah data murid' => 'Memperbarui data profil siswa',
            'Menghapus murid' => 'Menghapus data siswa',
            'Menambahkan guru baru:' => 'Menambahkan guru pembimbing baru:',
            'Mengubah data guru' => 'Memperbarui data guru pembimbing',
            'Menghapus guru' => 'Menghapus data guru pembimbing',
            'Menambahkan mitra DUDI baru:' => 'Menambahkan mitra industri baru:',
            'Mengubah data mitra DUDI' => 'Memperbarui data mitra industri',
            'Menghapus mitra DUDI' => 'Menghapus data mitra industri',
            'Membuat pengumuman baru:' => 'Membuat pengumuman baru:',
            'Mengubah pengumuman:' => 'Memperbarui pengumuman:',
            'Menghapus pengumuman' => 'Menghapus pengumuman',
            'Menginput penilaian akhir PKL' => 'Menginput penilaian rapor PKL siswa',
            'Melakukan penempatan PKL' => 'Melakukan plotting penempatan PKL siswa',
            'Melakukan penempatan PKL massal untuk' => 'Melakukan plotting penempatan PKL massal untuk',
            'Mengubah detail penempatan PKL' => 'Memperbarui detail data penempatan PKL',
            'Menghapus/Membatalkan penempatan PKL' => 'Membatalkan penempatan PKL siswa',
            'Mencatat kunjungan monitoring guru pembimbing' => 'Mencatat kunjungan monitoring PKL'
        ];

        foreach ($replacements as $search => $replace) {
            if (stripos($text, $search) !== false) {
                $text = str_ireplace($search, $replace, $text);
            }
        }

        return trim($text);
    }
}

if (!function_exists('getActivityBadge')) {
    function getActivityBadge($text) {
        if (empty($text)) {
            return '<span class="badge bg-secondary-light text-secondary px-2 py-1 fw-semibold" style="font-size: 10.5px; border-radius: 6px;">Sistem</span>';
        }
        // Critical / Danger actions
        if (stripos($text, 'hapus') !== false || stripos($text, 'membatalkan') !== false || stripos($text, 'wipe') !== false || stripos($text, 'kosongkan') !== false) {
            return '<span class="badge bg-danger-light text-danger px-2 py-1 fw-semibold" style="font-size: 10.5px; border-radius: 6px;">Hapus / Kritis</span>';
        }
        if (stripos($text, 'masuk') !== false || stripos($text, 'Login') !== false) {
            return '<span class="badge bg-success-light text-success px-2 py-1 fw-semibold" style="font-size: 10.5px; border-radius: 6px;">Login</span>';
        }
        if (stripos($text, 'keluar') !== false || stripos($text, 'Logout') !== false) {
            return '<span class="badge bg-secondary-light text-secondary px-2 py-1 fw-semibold" style="font-size: 10.5px; border-radius: 6px;">Logout</span>';
        }
        if (stripos($text, 'password') !== false) {
            return '<span class="badge bg-warning-light text-warning px-2 py-1 fw-semibold" style="font-size: 10.5px; border-radius: 6px;">Sandi</span>';
        }
        if (stripos($text, 'pengaturan') !== false || stripos($text, 'konfigurasi') !== false || stripos($text, 'branding') !== false) {
            return '<span class="badge bg-indigo-light text-indigo px-2 py-1 fw-semibold" style="font-size: 10.5px; border-radius: 6px;">Pengaturan</span>';
        }
        if (stripos($text, 'jurnal') !== false) {
            return '<span class="badge bg-primary-light text-primary px-2 py-1 fw-semibold" style="font-size: 10.5px; border-radius: 6px;">Jurnal</span>';
        }
        if (stripos($text, 'penilaian') !== false || stripos($text, 'nilai') !== false) {
            return '<span class="badge bg-amber-light text-amber px-2 py-1 fw-semibold" style="font-size: 10.5px; border-radius: 6px;">Penilaian</span>';
        }
        if (stripos($text, 'murid') !== false || stripos($text, 'siswa') !== false || stripos($text, 'guru') !== false || stripos($text, 'dudi') !== false || stripos($text, 'industri') !== false || stripos($text, 'penempatan') !== false) {
            return '<span class="badge bg-purple-light text-purple px-2 py-1 fw-semibold" style="font-size: 10.5px; border-radius: 6px;">Master</span>';
        }
        return '<span class="badge bg-secondary-light text-secondary px-2 py-1 fw-semibold" style="font-size: 10.5px; border-radius: 6px;">Sistem</span>';
    }
}

if (!function_exists('parseUserAgent')) {
    function parseUserAgent($agent) {
        if (empty($agent)) {
            return 'Perangkat Tidak Dikenal';
        }

        $os = 'Unknown OS';
        $browser = 'Unknown Browser';

        // 1. Detect OS
        if (stripos($agent, 'windows') !== false) {
            $os = 'Windows';
        } elseif (stripos($agent, 'macintosh') !== false || stripos($agent, 'mac os x') !== false) {
            $os = 'macOS';
        } elseif (stripos($agent, 'android') !== false) {
            $os = 'Android';
        } elseif (stripos($agent, 'iphone') !== false || stripos($agent, 'ipad') !== false) {
            $os = 'iOS';
        } elseif (stripos($agent, 'linux') !== false) {
            $os = 'Linux';
        }

        // 2. Detect Browser
        if (stripos($agent, 'chrome') !== false && stripos($agent, 'safari') !== false && stripos($agent, 'edge') === false && stripos($agent, 'edg') === false) {
            $browser = 'Chrome';
        } elseif (stripos($agent, 'safari') !== false && stripos($agent, 'chrome') === false) {
            $browser = 'Safari';
        } elseif (stripos($agent, 'firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($agent, 'edge') !== false || stripos($agent, 'edg') !== false) {
            $browser = 'Edge';
        } elseif (stripos($agent, 'opera') !== false || stripos($agent, 'opr') !== false) {
            $browser = 'Opera';
        }

        return $browser . ' on ' . $os;
    }
}
@endphp

<div class="container-fluid p-0">
    <div class="row">
        <!-- Database tools -->
        <div class="col-lg-4 mb-4">
            <div class="card-premium mb-4">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Cadangkan Database</h5>
                <p class="small text-secondary mb-4">Download salinan database MySQL dalam bentuk file SQL. Fitur ini dirancang khusus agar aman dan efisien dijalankan pada shared hosting cPanel.</p>
                <a href="{{ route('system.backup') }}" class="btn btn-sm btn-primary w-100 font-heading fw-semibold py-2">
                    <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: inline-block; vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh File Cadangan (.sql)
                </a>
            </div>

            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Pulihkan Database</h5>
                <p class="small text-secondary mb-3">Unggah file SQL cadangan untuk memulihkan seluruh tabel dan data database.</p>
                <div class="alert alert-warning border-0 small mb-3" style="background-color: rgba(245, 158, 11, 0.1); color: #d97706;">
                    ⚠️ Tindakan ini akan menimpa seluruh tabel yang ada saat ini. Pastikan file SQL yang diunggah valid.
                </div>
                
                <form action="{{ route('system.restore') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <input type="file" name="backup_file" class="form-control form-control-sm" accept=".sql" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-warning w-100 py-2 font-heading" onclick="return confirm('Apakah Anda benar-benar yakin ingin menimpa database saat ini?');">
                        Mulai Pemulihan SQL
                    </button>
                </form>
            </div>

            <div class="card-premium mt-4">
                <h5 class="fw-bold font-heading mb-3 text-danger dark-text-danger">Kosongkan Database</h5>
                <p class="small text-secondary mb-3">Wipe seluruh tabel database dan kembalikan ke keadaan kosong awal. Semua data murid, guru, DUDI, presensi, jurnal, dan penilaian akan terhapus.</p>
                <div class="alert alert-danger border-0 small mb-3" style="background-color: rgba(220, 38, 38, 0.1); color: #dc2626;">
                    ⚠️ Tindakan ini bersifat PERMANEN dan tidak dapat dibatalkan. Hanya akun Administrator utama (admin@pklsmk.sch.id / admin123) dan parameter dasar yang akan dipertahankan.
                </div>
                
                <form action="{{ route('system.wipe_db') }}" method="POST" onsubmit="return confirm('APAKAH ANDA BENAR-BENAR YAKIN? Semua data operasional PKL (murid, guru, DUDI, nilai, jurnal, absen) akan terhapus secara permanen!');">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Ketik kata konfirmasi "KOSONGKAN" untuk melanjutkan:</label>
                        <input type="text" name="confirmation_word" class="form-control form-control-sm border-danger" placeholder="KOSONGKAN" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-danger w-100 py-2 font-heading fw-bold">
                        Wipe & Kosongkan Database
                    </button>
                </form>
            </div>
        </div>

        <!-- Audit logs column -->
        <div class="col-lg-8 mb-4">
            <!-- Filter & Search Toolbar -->
            <div class="card-premium mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold font-heading m-0 text-dark dark-text-light">Filter & Pencarian Log Aktivitas</h6>
                        <small class="text-muted" style="font-size: 11px;">Temukan jejak audit pengguna berdasarkan kata kunci, peran, kategori, atau rentang tanggal.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('system.export_logs', request()->query()) }}" class="btn btn-sm btn-outline-success font-heading" title="Unduh hasil filter dalam format Excel CSV">
                            <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: inline-block; vertical-align: middle;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Ekspor CSV
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger font-heading" data-bs-toggle="modal" data-bs-target="#modalClearLogs">
                            <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: inline-block; vertical-align: middle;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Bersihkan Riwayat
                        </button>
                    </div>
                </div>

                <form action="{{ route('system.index') }}" method="GET" class="row g-2">
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-semibold">Kata Kunci (Nama / Email / Aktivitas / IP)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent border-end-0 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </span>
                            <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-0" placeholder="Ketik kata kunci pencarian..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold">Role Pengguna</label>
                        <select name="role" class="form-select form-select-sm">
                            <option value="">-- Semua Role --</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="guru" {{ request('role') === 'guru' ? 'selected' : '' }}>Guru Pembimbing</option>
                            <option value="murid" {{ request('role') === 'murid' ? 'selected' : '' }}>Siswa / Murid</option>
                            <option value="system" {{ request('role') === 'system' ? 'selected' : '' }}>Sistem Otomatis</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">Kategori Aktivitas</label>
                        <select name="kategori" class="form-select form-select-sm">
                            <option value="">-- Semua Kategori --</option>
                            <option value="keamanan" {{ request('kategori') === 'keamanan' ? 'selected' : '' }}>Keamanan & Akun (Login/Sandi)</option>
                            <option value="jurnal" {{ request('kategori') === 'jurnal' ? 'selected' : '' }}>Jurnal Kegiatan Siswa</option>
                            <option value="penilaian" {{ request('kategori') === 'penilaian' ? 'selected' : '' }}>Penilaian & Rapor PKL</option>
                            <option value="master" {{ request('kategori') === 'master' ? 'selected' : '' }}>Master Data & Penempatan</option>
                            <option value="setting" {{ request('kategori') === 'setting' ? 'selected' : '' }}>Pengaturan Sistem & Branding</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control form-control-sm" value="{{ request('tanggal_selesai') }}">
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end gap-2 mt-2 mt-md-0">
                        <button type="submit" class="btn btn-sm btn-primary flex-fill font-heading d-flex align-items-center justify-content-center gap-1 py-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span>Filter</span>
                        </button>
                        @if(request()->hasAny(['search', 'role', 'kategori', 'tanggal_mulai', 'tanggal_selesai']) && (request('search') || request('role') || request('kategori') || request('tanggal_mulai') || request('tanggal_selesai')))
                            <a href="{{ route('system.index') }}" class="btn btn-sm btn-outline-secondary font-heading d-flex align-items-center justify-content-center px-2.5 py-1.5" title="Reset Semua Filter">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Reset</span>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Audit logs table card -->
            <div class="card-premium p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-bottom-color: var(--border-color) !important;">
                    <div>
                        <h6 class="fw-bold m-0 text-dark dark-text-light">Catatan Aktivitas Pengguna</h6>
                        <small class="text-muted" style="font-size: 11px;">Menampilkan jejak aktivitas sistem yang tercatat secara real-time.</small>
                    </div>
                    <span class="badge bg-primary-light text-primary font-heading px-2.5 py-1" style="font-size: 11px;">
                        Total: {{ $logs->total() }} Data
                    </span>
                </div>

                <!-- Desktop Table View (md and up) -->
                <div class="table-responsive d-none d-md-block" style="max-height: 560px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 12.5px; min-width: 780px;">
                        <thead class="table-light sticky-top" style="z-index: 2;">
                            <tr class="text-muted font-heading" style="background-color: var(--bg-canvas);">
                                <th class="ps-4 py-3" style="width: 140px;">Waktu</th>
                                <th class="py-3" style="width: 200px;">Pengguna</th>
                                <th class="py-3">Detail Aktivitas</th>
                                <th class="py-3" style="width: 180px;">Perangkat & IP</th>
                                <th class="py-3 pe-4 text-center" style="width: 80px;">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                @php
                                    $logPayloadData = [
                                        'id' => $log->id,
                                        'waktu' => $log->created_at ? $log->created_at->format('d M Y H:i:s') . ' WIB' : '-',
                                        'user_nama' => $log->user ? $log->user->name : 'Sistem Otomatis',
                                        'user_email' => $log->user ? $log->user->email : '-',
                                        'user_role' => $log->user ? ucfirst($log->user->role) : 'System',
                                        'aktivitas_asli' => $log->aktivitas ?? '-',
                                        'aktivitas_format' => formatAktivitas($log->aktivitas ?? ''),
                                        'ip_address' => $log->ip_address ?? '-',
                                        'user_agent' => $log->user_agent ?? '-',
                                        'device' => parseUserAgent($log->user_agent ?? ''),
                                        'payload' => $log->payload
                                    ];
                                    $encodedPayload = base64_encode(json_encode($logPayloadData, JSON_UNESCAPED_UNICODE));
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark dark-text-light">{{ $log->created_at ? $log->created_at->format('d M Y') : '-' }}</div>
                                        <small class="text-muted" style="font-size: 11px;">{{ $log->created_at ? $log->created_at->format('H:i:s') : '-' }} WIB</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold bg-primary flex-shrink-0" style="width: 32px; height: 32px; font-size: 11px; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;">
                                                {{ $log->user ? strtoupper(substr($log->user->name, 0, 2)) : 'SY' }}
                                            </div>
                                            <div class="text-truncate" style="max-width: 150px;">
                                                <div class="fw-bold text-dark dark-text-light text-truncate" style="font-size: 12.5px;" title="{{ $log->user ? $log->user->name : 'Sistem Otomatis' }}">
                                                    {{ $log->user ? $log->user->name : 'Sistem Otomatis' }}
                                                </div>
                                                @if($log->user)
                                                    @if($log->user->role === 'admin')
                                                        <span class="badge bg-danger-light text-danger fw-semibold px-1.5 py-0.5" style="font-size: 9.5px; border-radius: 4px;">Admin</span>
                                                    @elseif($log->user->role === 'guru')
                                                        <span class="badge bg-primary-light text-primary fw-semibold px-1.5 py-0.5" style="font-size: 9.5px; border-radius: 4px;">Guru</span>
                                                    @elseif($log->user->role === 'industri')
                                                        <span class="badge bg-warning-light text-warning fw-semibold px-1.5 py-0.5" style="font-size: 9.5px; border-radius: 4px;">Industri</span>
                                                    @else
                                                        <span class="badge bg-success-light text-success fw-semibold px-1.5 py-0.5" style="font-size: 9.5px; border-radius: 4px;">Siswa</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary-light text-secondary fw-semibold px-1.5 py-0.5" style="font-size: 9.5px; border-radius: 4px;">System</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                            {!! getActivityBadge($log->aktivitas) !!}
                                            <span class="text-dark dark-text-light fw-medium">{{ formatAktivitas($log->aktivitas) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark font-monospace border px-2 py-0.5" style="font-size: 10.5px; border-radius: 4px;">{{ $log->ip_address }}</span>
                                        <div class="d-flex align-items-center gap-1 mt-1 text-muted text-truncate" style="font-size: 10.5px; max-width: 170px;" title="{{ $log->user_agent }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-secondary flex-shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-truncate">{{ parseUserAgent($log->user_agent) }}</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2 btn-show-detail" data-bs-toggle="modal" data-bs-target="#modalDetailLog" title="Lihat Detail Log" data-log="{{ $encodedPayload }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mb-2 text-secondary opacity-50">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <div class="small fw-semibold">Tidak ditemukan catatan log aktivitas sesuai filter.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List View (Visible on smartphone < md) -->
                <div class="d-md-none p-3">
                    @forelse($logs as $log)
                        @php
                            $logPayloadData = [
                                'id' => $log->id,
                                'waktu' => $log->created_at ? $log->created_at->format('d M Y H:i:s') . ' WIB' : '-',
                                'user_nama' => $log->user ? $log->user->name : 'Sistem Otomatis',
                                'user_email' => $log->user ? $log->user->email : '-',
                                'user_role' => $log->user ? ucfirst($log->user->role) : 'System',
                                'aktivitas_asli' => $log->aktivitas ?? '-',
                                'aktivitas_format' => formatAktivitas($log->aktivitas ?? ''),
                                'ip_address' => $log->ip_address ?? '-',
                                'user_agent' => $log->user_agent ?? '-',
                                'device' => parseUserAgent($log->user_agent ?? ''),
                                'payload' => $log->payload
                            ];
                            $encodedPayload = base64_encode(json_encode($logPayloadData, JSON_UNESCAPED_UNICODE));
                        @endphp
                        <div class="card p-3 mb-3 border rounded shadow-xs" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                            <!-- Top: Waktu & Role -->
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                <div class="text-muted small" style="font-size: 11px;">
                                    <span class="fw-semibold text-dark">{{ $log->created_at ? $log->created_at->format('d M Y, H:i') : '-' }} WIB</span>
                                </div>
                                <div>
                                    @if($log->user)
                                        @if($log->user->role === 'admin')
                                            <span class="badge bg-danger-light text-danger fw-semibold" style="font-size: 10px;">Admin</span>
                                        @elseif($log->user->role === 'guru')
                                            <span class="badge bg-primary-light text-primary fw-semibold" style="font-size: 10px;">Guru</span>
                                        @elseif($log->user->role === 'industri')
                                            <span class="badge bg-warning-light text-warning fw-semibold" style="font-size: 10px;">Industri</span>
                                        @else
                                            <span class="badge bg-success-light text-success fw-semibold" style="font-size: 10px;">Siswa</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary-light text-secondary fw-semibold" style="font-size: 10px;">System</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Pelaku -->
                            <div class="mb-2">
                                <div class="fw-bold text-dark font-heading" style="font-size: 13px;">{{ $log->user ? $log->user->name : 'Sistem Otomatis' }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $log->user ? $log->user->email : 'Proses Otomatis Backend' }}</div>
                            </div>

                            <!-- Aktivitas -->
                            <div class="p-2.5 rounded bg-light border mb-2" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important; font-size: 12px; line-height: 1.4;">
                                <div class="mb-1">{!! getActivityBadge($log->aktivitas) !!}</div>
                                <div class="text-dark fw-medium">{{ formatAktivitas($log->aktivitas) }}</div>
                            </div>

                            <!-- Footer: IP & Tombol Detail -->
                            <div class="pt-2 border-top d-flex align-items-center justify-content-between gap-2" style="border-top-color: var(--border-color) !important;">
                                <div class="text-muted text-truncate" style="font-size: 11px; max-width: 60%;">
                                    <span class="font-monospace text-dark">{{ $log->ip_address }}</span>
                                    <div class="text-truncate" title="{{ parseUserAgent($log->user_agent) }}">{{ parseUserAgent($log->user_agent) }}</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary font-heading d-flex align-items-center gap-1 py-1 px-2.5 btn-show-detail" data-bs-toggle="modal" data-bs-target="#modalDetailLog" data-log="{{ $encodedPayload }}" style="font-size: 12px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>Detail</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-4 text-center">
                            <div class="small fw-semibold text-muted">Tidak ditemukan catatan log aktivitas sesuai filter.</div>
                        </div>
                    @endforelse
                </div>

                @if($logs->hasPages())
                <div class="border-top px-3 py-2" style="border-top-color: var(--border-color) !important;">
                    {{ $logs->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Log & Payload Data -->
<div class="modal fade" id="modalDetailLog" tabindex="-1" aria-labelledby="modalDetailLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalDetailLogLabel">
                    <svg class="me-1 text-primary" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: inline-block; vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Inspeksi Jejak Audit Aktivitas
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-0">Waktu Transaksi</label>
                        <div class="fw-bold" id="detail_waktu">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-0">Pelaku (Pengguna & Role)</label>
                        <div class="fw-bold">
                            <span id="detail_user_nama">-</span>
                            <span class="badge bg-primary-light text-primary ms-1" id="detail_user_role">-</span>
                        </div>
                        <small class="text-muted" id="detail_user_email">-</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-0">Alamat IP & Perangkat</label>
                        <div>
                            <span class="badge bg-light text-dark font-monospace border" id="detail_ip">-</span>
                            <span class="small ms-1 text-muted" id="detail_device">-</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-0">Deskripsi Ringkas</label>
                        <div class="fw-medium text-dark dark-text-light" id="detail_aktivitas">-</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">User Agent Header</label>
                    <div class="p-2 rounded bg-light font-monospace small border text-break" style="font-size: 11px; background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;" id="detail_user_agent">
                        -
                    </div>
                </div>

                <div>
                    <label class="form-label small fw-semibold text-muted mb-1">Rincian Data Payload (Sebelum / Sesudah)</label>
                    <div id="payload_container">
                        <pre class="p-3 rounded bg-light border font-monospace mb-0" style="font-size: 11.5px; max-height: 220px; overflow-y: auto; background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;" id="detail_payload_json">-</pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pembersihan Riwayat Log Bertahap -->
<div class="modal fade" id="modalClearLogs" tabindex="-1" aria-labelledby="modalClearLogsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
            <div class="modal-header">
                <h6 class="modal-title fw-bold text-danger" id="modalClearLogsLabel">
                    <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: inline-block; vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Pembersihan Riwayat Log Audit
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('system.clear_logs') }}" method="POST">
                @csrf
                <div class="modal-body text-start">
                    <p class="small text-muted mb-3">Pilih rentang usia log yang ingin dihapus untuk menjaga performa database tanpa kehilangan data log terbaru:</p>

                    <div class="form-check mb-2 p-2 border rounded">
                        <input class="form-check-input ms-0 me-2" type="radio" name="retention" id="retention_30" value="30" checked>
                        <label class="form-check-label small fw-semibold" for="retention_30">
                            Hapus log yang lebih lama dari <strong>30 Hari</strong>
                            <div class="text-muted fw-normal" style="font-size: 11px;">Menyimpan log audit 1 bulan terakhir.</div>
                        </label>
                    </div>

                    <div class="form-check mb-2 p-2 border rounded">
                        <input class="form-check-input ms-0 me-2" type="radio" name="retention" id="retention_90" value="90">
                        <label class="form-check-label small fw-semibold" for="retention_90">
                            Hapus log yang lebih lama dari <strong>90 Hari (3 Bulan)</strong>
                            <div class="text-muted fw-normal" style="font-size: 11px;">Menyimpan log audit 3 bulan terakhir.</div>
                        </label>
                    </div>

                    <div class="form-check mb-2 p-2 border rounded">
                        <input class="form-check-input ms-0 me-2" type="radio" name="retention" id="retention_180" value="180">
                        <label class="form-check-label small fw-semibold" for="retention_180">
                            Hapus log yang lebih lama dari <strong>180 Hari (6 Bulan)</strong>
                            <div class="text-muted fw-normal" style="font-size: 11px;">Menyimpan log audit 1 semester terakhir.</div>
                        </label>
                    </div>

                    <div class="form-check mb-3 p-2 border rounded border-danger bg-danger-light">
                        <input class="form-check-input ms-0 me-2 text-danger" type="radio" name="retention" id="retention_all" value="all">
                        <label class="form-check-label small fw-semibold text-danger" for="retention_all">
                            Hapus SEMUA riwayat log audit (Kosongkan Total)
                            <div class="text-danger fw-normal opacity-75" style="font-size: 11px;">Seluruh riwayat audit akan dihapus permanen.</div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger fw-semibold" onclick="return confirm('Apakah Anda yakin ingin memproses pembersihan log ini?');">
                        Konfirmasi & Bersihkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function parseSafeJsonLog(encodedData) {
        if (!encodedData) return {};
        try {
            const binString = atob(encodedData);
            const bytes = Uint8Array.from(binString, function(m) { return m.codePointAt(0); });
            const decoded = new TextDecoder().decode(bytes);
            return JSON.parse(decoded);
        } catch(e) {
            try {
                return JSON.parse(atob(encodedData));
            } catch(err2) {
                console.error("Failed to parse log payload:", err2);
                return {};
            }
        }
    }

    function populateLogDetail(data) {
        if (!data || Object.keys(data).length === 0) return;
        
        document.getElementById('detail_waktu').innerText = data.waktu || '-';
        document.getElementById('detail_user_nama').innerText = data.user_nama || 'Sistem Otomatis';
        document.getElementById('detail_user_email').innerText = data.user_email || '-';
        document.getElementById('detail_user_role').innerText = data.user_role || 'System';
        document.getElementById('detail_ip').innerText = data.ip_address || '-';
        document.getElementById('detail_device').innerText = data.device || '-';
        document.getElementById('detail_aktivitas').innerText = data.aktivitas_format || data.aktivitas_asli || '-';
        document.getElementById('detail_user_agent').innerText = data.user_agent || '-';

        const payloadEl = document.getElementById('detail_payload_json');
        if (data.payload && (typeof data.payload === 'object' ? Object.keys(data.payload).length > 0 : String(data.payload).trim() !== '')) {
            payloadEl.innerText = typeof data.payload === 'object' ? JSON.stringify(data.payload, null, 2) : String(data.payload);
            payloadEl.classList.remove('text-muted');
        } else {
            payloadEl.innerText = 'Tidak ada rekaman payload data spesifik untuk aktivitas ini.';
            payloadEl.classList.add('text-muted');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('modalDetailLog');
        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', function(event) {
                const triggerBtn = event.relatedTarget || document.activeElement;
                if (triggerBtn && triggerBtn.hasAttribute('data-log')) {
                    const encodedData = triggerBtn.getAttribute('data-log');
                    const data = parseSafeJsonLog(encodedData);
                    populateLogDetail(data);
                }
            });
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-show-detail');
            if (!btn) return;
            const encodedData = btn.getAttribute('data-log');
            const data = parseSafeJsonLog(encodedData);
            populateLogDetail(data);
        });
    });
</script>
@endsection
