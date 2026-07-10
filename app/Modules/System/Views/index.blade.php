@extends('layouts.admin')

@section('title', 'Utilitas Sistem & Keamanan - PKLku')
@section('page_title', 'Utilitas Sistem & Keamanan')

@section('content')
@php
if (!function_exists('formatAktivitas')) {
    function formatAktivitas($text) {
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
        if (stripos($text, 'masuk') !== false || stripos($text, 'Login') !== false) {
            return '<span class="badge bg-success-light text-success px-2 py-1 fw-semibold" style="font-size: 10px; border-radius: 6px;"><i class="bi bi-shield-check me-1"></i>Keamanan</span>';
        }
        if (stripos($text, 'keluar') !== false || stripos($text, 'Logout') !== false) {
            return '<span class="badge bg-secondary-light text-secondary px-2 py-1 fw-semibold" style="font-size: 10px; border-radius: 6px;"><i class="bi bi-box-arrow-left me-1"></i>Keamanan</span>';
        }
        if (stripos($text, 'pengaturan') !== false || stripos($text, 'konfigurasi') !== false || stripos($text, 'branding') !== false) {
            return '<span class="badge bg-indigo-light text-indigo px-2 py-1 fw-semibold" style="font-size: 10px; border-radius: 6px;"><i class="bi bi-gear me-1"></i>Sistem</span>';
        }
        if (stripos($text, 'jurnal') !== false) {
            return '<span class="badge bg-blue-light text-blue px-2 py-1 fw-semibold" style="font-size: 10px; border-radius: 6px;"><i class="bi bi-journal-text me-1"></i>Jurnal</span>';
        }
        if (stripos($text, 'penilaian') !== false || stripos($text, 'nilai') !== false) {
            return '<span class="badge bg-amber-light text-amber px-2 py-1 fw-semibold" style="font-size: 10px; border-radius: 6px;"><i class="bi bi-star me-1"></i>Penilaian</span>';
        }
        if (stripos($text, 'murid') !== false || stripos($text, 'siswa') !== false || stripos($text, 'guru') !== false || stripos($text, 'dudi') !== false || stripos($text, 'industri') !== false || stripos($text, 'penempatan') !== false) {
            return '<span class="badge bg-purple-light text-purple px-2 py-1 fw-semibold" style="font-size: 10px; border-radius: 6px;"><i class="bi bi-folder2-open me-1"></i>Master</span>';
        }
        return '<span class="badge bg-light text-dark px-2 py-1 fw-semibold border" style="font-size: 10px; border-radius: 6px;"><i class="bi bi-info-circle me-1"></i>Sistem</span>';
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
        <div class="col-md-4 mb-4">
            <div class="card-premium mb-4">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Cadangkan Database</h5>
                <p class="small text-secondary mb-4">Download salinan database MySQL dalam bentuk file SQL terkompresi. Fitur ini dirancang khusus agar aman dijalankan pada shared hosting biasa.</p>
                <a href="{{ route('system.backup') }}" class="btn btn-sm btn-primary w-100 font-heading fw-semibold py-2">
                    Unduh File Cadangan (.sql)
                </a>
            </div>

            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Pulihkan Database</h5>
                <p class="small text-secondary mb-3">Unggah file SQL cadangan untuk memulihkan seluruh tabel dan isi database.</p>
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
        </div>

        <!-- Audit logs -->
        <div class="col-md-8 mb-4">
            <div class="card-premium p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-bottom-color: var(--border-color) !important;">
                    <div>
                        <h6 class="fw-bold m-0 text-dark dark-text-light">Catatan Aktivitas Pengguna</h6>
                        <small class="text-muted" style="font-size: 11px;">Daftar aktivitas log masuk, perubahan data, dan operasional sistem.</small>
                    </div>
                    <form action="{{ route('system.clear_logs') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh log aktivitas?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-3 fw-semibold font-heading" style="font-size: 11px; border-radius: 6px;">
                            Bersihkan Riwayat
                        </button>
                    </form>
                </div>

                <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 12.5px;">
                        <thead>
                            <tr class="text-muted" style="background-color: var(--bg-canvas);">
                                <th class="ps-4 py-3" style="width: 150px;">Tanggal & Waktu</th>
                                <th class="py-3" style="width: 220px;">Pengguna</th>
                                <th class="py-3">Detail Aktivitas</th>
                                <th class="py-3 pe-4" style="width: 200px;">Perangkat & IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark dark-text-light">{{ $log->created_at->format('d M Y') }}</div>
                                        <small class="text-muted" style="font-size: 11px;">{{ $log->created_at->format('H:i:s') }} WIB</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold bg-primary" style="width: 32px; height: 32px; font-size: 12px; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;">
                                                {{ $log->user ? strtoupper(substr($log->user->name, 0, 2)) : 'SY' }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark dark-text-light" style="font-size: 12.5px;">{{ $log->user ? $log->user->name : 'Sistem Otomatis' }}</div>
                                                @if($log->user)
                                                    @if($log->user->role === 'admin')
                                                        <span class="badge bg-danger-light text-danger fw-semibold px-2 py-0.5" style="font-size: 10px; border-radius: 4px;">Admin</span>
                                                    @elseif($log->user->role === 'guru')
                                                        <span class="badge bg-primary-light text-primary fw-semibold px-2 py-0.5" style="font-size: 10px; border-radius: 4px;">Guru</span>
                                                    @elseif($log->user->role === 'industri')
                                                        <span class="badge bg-warning-light text-warning fw-semibold px-2 py-0.5" style="font-size: 10px; border-radius: 4px;">Industri</span>
                                                    @else
                                                        <span class="badge bg-success-light text-success fw-semibold px-2 py-0.5" style="font-size: 10px; border-radius: 4px;">Siswa</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary-light text-secondary fw-semibold px-2 py-0.5" style="font-size: 10px; border-radius: 4px;">System</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            {!! getActivityBadge($log->aktivitas) !!}
                                            <span class="text-dark dark-text-light fw-medium">{{ formatAktivitas($log->aktivitas) }}</span>
                                        </div>
                                    </td>
                                    <td class="pe-4">
                                        <span class="badge bg-light text-dark font-monospace border px-2 py-1" style="font-size: 11px; border-radius: 4px;">{{ $log->ip_address }}</span>
                                        <div class="d-flex align-items-center gap-1 mt-1 text-muted" style="font-size: 11px;" title="{{ $log->user_agent }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-secondary">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <span>{{ parseUserAgent($log->user_agent) }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mb-2 text-secondary opacity-50">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <div class="small fw-semibold">Belum ada catatan log aktivitas.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
                    {{ $logs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
