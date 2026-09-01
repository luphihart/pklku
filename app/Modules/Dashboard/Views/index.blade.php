@extends('layouts.admin')

@section('title', 'Dashboard - PKLku')
@section('page_title', 'Dashboard')

@section('styles')
@if(auth()->user()->role === 'admin' || auth()->user()->role === 'guru')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #monitoringMap { z-index: 1; }
    .leaflet-container { font-family: inherit; }
</style>
@endif
@endsection

@section('content')
<div class="container-fluid p-0">
    @php
        $user = auth()->user();
        $hour = (int) date('H');
        if ($hour >= 4 && $hour < 11) {
            $greeting = 'Selamat Pagi';
        } elseif ($hour >= 11 && $hour < 15) {
            $greeting = 'Selamat Siang';
        } elseif ($hour >= 15 && $hour < 18) {
            $greeting = 'Selamat Sore';
        } else {
            $greeting = 'Selamat Malam';
        }
        $isBirthday = $user->tanggal_lahir && $user->tanggal_lahir->isBirthday();
        $murid = $user->murid;
        $penempatan = $murid ? $murid->penempatanAktif : null;
    @endphp

    <!-- Birthday Alert Pill (Compact) -->
    @if($isBirthday)
        <div class="alert border-0 shadow-sm d-flex align-items-center justify-content-between mb-4 p-3" style="background: linear-gradient(135deg, rgba(244, 63, 94, 0.12) 0%, rgba(217, 70, 239, 0.12) 100%); border-left: 4px solid #f43f5e !important; color: #be123c;">
            <div class="d-flex align-items-center gap-2">
                <span style="font-size: 20px;">🎂</span>
                <div>
                    <strong class="font-heading d-block" style="font-size: 13.5px;">Selamat Ulang Tahun, {{ $user->name }}! 🎉</strong>
                    <small class="text-secondary" style="font-size: 12px;">Semoga senantiasa diberikan kesehatan, kelancaran aktivitas, dan kesuksesan dalam setiap langkah!</small>
                </div>
            </div>
            <span class="badge bg-danger-light text-danger font-heading px-2 py-1 d-none d-sm-inline" style="font-size: 11px;">Milad Hari Ini</span>
        </div>
    @endif

    <!-- Welcome Header Card -->
    <div class="card-premium mb-4 p-3 p-md-4" style="border-left: 4px solid var(--accent-primary) !important;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">{{ $greeting }}, {{ $user->name }}</h5>
                    <span class="badge bg-primary-light text-primary text-uppercase font-heading" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">
                        {{ $user->role === 'murid' ? 'Siswa PKL' : ($user->role === 'guru' ? 'Guru Pembimbing' : 'Administrator') }}
                    </span>
                </div>
                <small class="text-muted d-block mt-1" style="font-size: 12.5px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="align-middle me-1">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </small>
            </div>

            @if($user->role === 'guru' || $user->role === 'admin')
                @if(($pendingJournalsCount ?? 0) > 0 || ($pendingIzinCount ?? 0) > 0)
                    <div class="d-flex gap-2 flex-wrap">
                        @if(($pendingJournalsCount ?? 0) > 0)
                            <a href="{{ route('jurnal.index', ['status' => 'pending']) }}" class="btn btn-xs btn-outline-warning font-heading d-flex align-items-center gap-1" style="border-radius: 6px; padding: 5px 10px; font-size: 11px;">
                                <span class="badge bg-warning text-dark">{{ $pendingJournalsCount }}</span> Jurnal Pending
                            </a>
                        @endif
                        @if(($pendingIzinCount ?? 0) > 0)
                            <a href="{{ route('izin.index', ['status' => 'pending']) }}" class="btn btn-xs btn-outline-info font-heading d-flex align-items-center gap-1" style="border-radius: 6px; padding: 5px 10px; font-size: 11px;">
                                <span class="badge bg-info text-white">{{ $pendingIzinCount }}</span> Izin Pending
                            </a>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- ============================== ROLE: MURID ============================== --}}
    {{-- ========================================================================= --}}
    @if($user->role === 'murid')
        @if(!$penempatan)
            <div class="card-premium empty-state text-center py-5">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h6 class="empty-state-title">Belum Ada Penempatan Aktif</h6>
                <p class="empty-state-text">Akun Anda belum di-plotting ke mitra industri DUDI. Silakan hubungi koordinator PKL atau Admin Sekolah.</p>
            </div>
        @else
            @php
                $todayPres = $muridTodayPresensi;
                $todayHoliday = $todayHoliday ?? null;
                $isPlacementHoliday = $penempatan ? $penempatan->isPlacementHoliday() : false;
                $isLiburShift = $todayPres && $todayPres->status_masuk === 'libur_shift';
            @endphp

            <!-- Hero Status Presensi Hari Ini (Interactive) -->
            <div class="card-premium mb-4 p-3 p-md-4" style="background-color: var(--bg-card); border-left: 4px solid {{ $isLiburShift || $todayHoliday || $isPlacementHoliday ? '#0284c7' : ($todayPres && $todayPres->jam_pulang ? 'var(--success)' : ($todayPres && $todayPres->jam_masuk ? 'var(--accent-primary)' : 'var(--warning)')) }} !important;">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px; background-color: {{ $isLiburShift || $todayHoliday || $isPlacementHoliday ? 'rgba(14, 165, 233, 0.12)' : ($todayPres && $todayPres->jam_pulang ? 'rgba(16, 185, 129, 0.12)' : ($todayPres && $todayPres->jam_masuk ? 'rgba(79, 70, 229, 0.12)' : 'rgba(245, 158, 11, 0.12)')) }}; color: {{ $isLiburShift || $todayHoliday || $isPlacementHoliday ? '#0284c7' : ($todayPres && $todayPres->jam_pulang ? 'var(--success)' : ($todayPres && $todayPres->jam_masuk ? 'var(--accent-primary)' : 'var(--warning)')) }};">
                            @if($todayHoliday || $isPlacementHoliday || $isLiburShift)
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            @elseif($todayPres && $todayPres->jam_pulang)
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @elseif($todayPres && $todayPres->jam_masuk)
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <span class="text-muted text-uppercase fw-bold d-block" style="font-size: 11px; letter-spacing: 0.5px;">Status Presensi Hari Ini</span>
                            @if($todayHoliday)
                                <h6 class="fw-bold font-heading m-0 text-info" style="font-size: 15px;">Libur Nasional: {{ $todayHoliday->nama }}</h6>
                                <small class="text-muted d-block" style="font-size: 11.5px;">Tidak ada kewajiban presensi kehadiran hari ini.</small>
                            @elseif($isPlacementHoliday)
                                <h6 class="fw-bold font-heading m-0 text-info" style="font-size: 15px;">Jadwal Libur Rutin DUDI ({{ \Carbon\Carbon::now()->translatedFormat('l') }})</h6>
                                <small class="text-muted d-block" style="font-size: 11.5px;">Hari ini adalah jadwal libur rutin penempatan Anda.</small>
                            @elseif($isLiburShift)
                                <h6 class="fw-bold font-heading m-0 text-info" style="font-size: 15px;">Jadwal Libur Shift Kerja (Off Day)</h6>
                                <small class="text-muted d-block" style="font-size: 11.5px;">Status libur shift aktif dan tidak dihitung alpha.</small>
                            @elseif($todayPres && $todayPres->jam_pulang)
                                <h6 class="fw-bold font-heading m-0 text-success" style="font-size: 15px;">Presensi Hari Ini Lengkap ✓</h6>
                                <small class="text-muted d-block" style="font-size: 11.5px;">Masuk: <strong>{{ substr($todayPres->jam_masuk, 0, 5) }}</strong> &bull; Pulang: <strong>{{ substr($todayPres->jam_pulang, 0, 5) }}</strong></small>
                            @elseif($todayPres && $todayPres->jam_masuk)
                                <h6 class="fw-bold font-heading m-0 text-primary" style="font-size: 15px;">Sudah Check-In Masuk ({{ substr($todayPres->jam_masuk, 0, 5) }})</h6>
                                <small class="text-muted d-block" style="font-size: 11.5px;">Jangan lupa melakukan Check-Out saat jam kerja selesai.</small>
                            @else
                                <h6 class="fw-bold font-heading m-0 text-warning" style="font-size: 15px;">Belum Presensi Masuk</h6>
                                <small class="text-muted d-block" style="font-size: 11.5px;">Buka kamera & GPS di lokasi DUDI untuk check-in.</small>
                            @endif
                        </div>
                    </div>

                    <!-- Direct Action Button -->
                    <div class="d-flex gap-2 flex-wrap">
                        @if(!$todayHoliday && !$isPlacementHoliday && !$isLiburShift)
                            @if(!$todayPres || !$todayPres->jam_masuk)
                                <a href="{{ route('presensi.index') }}" class="btn btn-sm btn-primary font-heading px-3 py-2 d-flex align-items-center gap-1.5 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>Presensi Masuk Sekarang</span>
                                </a>
                            @elseif($todayPres->jam_masuk && !$todayPres->jam_pulang)
                                <a href="{{ route('presensi.index') }}" class="btn btn-sm btn-success font-heading px-3 py-2 d-flex align-items-center gap-1.5 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    <span>Presensi Pulang</span>
                                </a>
                            @endif
                        @endif
                        <a href="{{ route('jurnal.index') }}" class="btn btn-sm btn-outline-primary font-heading px-3 py-2 d-flex align-items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <span>Tulis Jurnal</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Info Penempatan Compact -->
                <div class="col-lg-6 mb-4">
                    <div class="card-premium h-100 p-3">
                        <h6 class="fw-bold font-heading mb-3 text-dark dark-text-light d-flex align-items-center gap-2">
                            <svg class="text-primary" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>Informasi Penempatan PKL</span>
                        </h6>
                        <div class="row g-2">
                            <div class="col-12 p-2.5 rounded border" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 10px;">Mitra Industri (DUDI)</small>
                                <span class="fw-bold text-dark font-heading" style="font-size: 13.5px;">{{ $penempatan->dudi?->nama ?? '-' }}</span>
                                <small class="text-muted d-block mt-0.5" style="font-size: 11px;">{{ $penempatan->dudi?->alamat ?? '-' }}</small>
                            </div>
                            <div class="col-6 p-2.5 rounded border" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 10px;">Guru Pembimbing</small>
                                <span class="fw-semibold text-dark font-heading" style="font-size: 12.5px;">{{ $penempatan->guru?->nama ?? '-' }}</span>
                            </div>
                            <div class="col-6 p-2.5 rounded border" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 10px;">Periode Pelaksanaan</small>
                                <span class="fw-semibold text-dark font-heading" style="font-size: 12px;">
                                    {{ \Carbon\Carbon::parse($penempatan->tanggal_mulai)->format('d/m/y') }} - {{ \Carbon\Carbon::parse($penempatan->tanggal_selesai)->format('d/m/y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jurnal Terakhir Widget -->
                <div class="col-lg-6 mb-4">
                    <div class="card-premium h-100 p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold font-heading m-0 text-dark dark-text-light d-flex align-items-center gap-2">
                                    <svg class="text-primary" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span>Jurnal Harian Terbaru</span>
                                </h6>
                                <a href="{{ route('jurnal.index') }}" class="small text-primary text-decoration-none font-heading" style="font-size: 11.5px;">Lihat Semua &rarr;</a>
                            </div>

                            @forelse($recentJournals as $jurnal)
                                <div class="p-2.5 mb-2 rounded border d-flex justify-content-between align-items-center gap-2" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                    <div style="min-width: 0;">
                                        <div class="d-flex align-items-center gap-2">
                                            <strong class="text-dark font-heading" style="font-size: 12.5px;">{{ \Carbon\Carbon::parse($jurnal->tanggal)->translatedFormat('d M Y') }}</strong>
                                        </div>
                                        <p class="text-muted small m-0 text-truncate" style="font-size: 11px; max-width: 260px;">{{ $jurnal->deskripsi_aktivitas }}</p>
                                    </div>
                                    <span class="badge {{ $jurnal->status_verifikasi === 'disetujui' ? 'bg-success-light text-success' : ($jurnal->status_verifikasi === 'revisi' ? 'bg-warning-light text-warning' : ($jurnal->status_verifikasi === 'ditolak' ? 'bg-danger-light text-danger' : 'bg-primary-light text-primary')) }} font-heading px-2 py-1 flex-shrink-0" style="font-size: 10px;">
                                        {{ ucfirst($jurnal->status_verifikasi ?? 'pending') }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted small">
                                    Belum ada jurnal yang ditulis. Mulai catat kegiatan PKL Anda!
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

    {{-- ========================================================================= --}}
    {{-- ========================= ROLE: GURU & ADMIN ============================ --}}
    {{-- ========================================================================= --}}
    @else
        <!-- Metrics Grid (4 Minimalist KPI Cards) -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="quick-metric-box h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold font-heading" style="font-size: 10.5px; letter-spacing: 0.5px;">
                            {{ $user->role === 'guru' ? 'Murid Bimbingan' : 'Total Murid' }}
                        </span>
                        <h3 class="fw-bold m-0 mt-1 text-dark dark-text-light">{{ $counts['murid'] ?? 0 }}</h3>
                    </div>
                    <div class="p-2.5 rounded bg-primary-light text-primary d-none d-sm-flex align-items-center justify-content-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="quick-metric-box h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold font-heading" style="font-size: 10.5px; letter-spacing: 0.5px;">
                            {{ $user->role === 'guru' ? 'Mitra DUDI Aktif' : 'Total Guru' }}
                        </span>
                        <h3 class="fw-bold m-0 mt-1 text-dark dark-text-light">
                            {{ $user->role === 'guru' ? ($counts['dudi'] ?? 0) : ($counts['guru'] ?? 0) }}
                        </h3>
                    </div>
                    <div class="p-2.5 rounded bg-success-light text-success d-none d-sm-flex align-items-center justify-content-center">
                        @if($user->role === 'guru')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m-6 4h6"/>
                            </svg>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="quick-metric-box h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold font-heading" style="font-size: 10.5px; letter-spacing: 0.5px;">
                            {{ $user->role === 'guru' ? 'Penempatan Aktif' : 'Mitra DUDI' }}
                        </span>
                        <h3 class="fw-bold m-0 mt-1 text-dark dark-text-light">
                            {{ $user->role === 'guru' ? ($counts['penempatan_aktif'] ?? 0) : ($counts['dudi'] ?? 0) }}
                        </h3>
                    </div>
                    <div class="p-2.5 rounded bg-warning-light text-warning d-none d-sm-flex align-items-center justify-content-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="quick-metric-box h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold font-heading" style="font-size: 10.5px; letter-spacing: 0.5px;">
                            {{ $user->role === 'guru' ? 'Jurnal Menunggu' : 'Plotting Aktif' }}
                        </span>
                        <h3 class="fw-bold m-0 mt-1 text-dark dark-text-light">
                            {{ $user->role === 'guru' ? ($pendingJournalsCount ?? 0) : ($counts['penempatan_aktif'] ?? 0) }}
                        </h3>
                    </div>
                    <div class="p-2.5 rounded bg-danger-light text-danger d-none d-sm-flex align-items-center justify-content-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kehadiran Hari Ini & Pintasan Cepat -->
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="card-premium h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold font-heading m-0 text-dark dark-text-light">
                            Kehadiran Siswa Hari Ini ({{ \Carbon\Carbon::now()->translatedFormat('d M Y') }})
                        </h6>
                        <a href="{{ route('presensi.index') }}" class="small text-primary text-decoration-none font-heading" style="font-size: 11.5px;">Monitoring Detail &rarr;</a>
                    </div>
                    <div class="row text-center g-2 pt-2">
                        <div class="col-4">
                            <div class="p-2.5 rounded border" style="background-color: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.2) !important;">
                                <small class="text-muted d-block fw-semibold" style="font-size: 11px;">Hadir Tepat Waktu</small>
                                <h3 class="fw-bold text-success font-heading m-0 mt-1">{{ $attendance['hadir'] ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2.5 rounded border" style="background-color: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.2) !important;">
                                <small class="text-muted d-block fw-semibold" style="font-size: 11px;">Terlambat</small>
                                <h3 class="fw-bold text-warning font-heading m-0 mt-1">{{ $attendance['terlambat'] ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2.5 rounded border" style="background-color: rgba(225, 29, 72, 0.05); border-color: rgba(225, 29, 72, 0.2) !important;">
                                <small class="text-muted d-block fw-semibold" style="font-size: 11px;">Belum Presensi</small>
                                <h3 class="fw-bold text-danger font-heading m-0 mt-1">{{ $attendance['belum_hadir'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-premium h-100 p-3 d-flex flex-column justify-content-between">
                    <h6 class="fw-bold font-heading mb-3 text-dark dark-text-light">Pintasan Cepat</h6>
                    <div class="d-flex flex-column gap-2">
                        @if($user->role === 'admin')
                            <a href="{{ route('murid.index') }}" class="btn btn-sm btn-outline-primary font-heading d-flex align-items-center gap-2 py-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Kelola Data Murid</span>
                            </a>
                            <a href="{{ route('penempatan.index') }}" class="btn btn-sm btn-outline-success font-heading d-flex align-items-center gap-2 py-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                <span>Plotting Penempatan PKL</span>
                            </a>
                        @else
                            <a href="{{ route('jurnal.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-warning font-heading d-flex align-items-center gap-2 py-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Verifikasi Jurnal Bimbingan</span>
                            </a>
                            <a href="{{ route('kunjungan.index') }}" class="btn btn-sm btn-outline-primary font-heading d-flex align-items-center gap-2 py-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                                <span>Catatan Kunjungan DUDI</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Peta Monitoring Lokasi PKL Aktif -->
        <div class="row g-3">
            <div class="col-lg-8 mb-4">
                <div class="card-premium p-3">
                    <h6 class="fw-bold font-heading mb-3 text-dark dark-text-light">Peta Lokasi PKL Aktif</h6>
                    <div id="monitoringMap" style="height: 360px; border-radius: 0.5rem; border: 1px solid var(--border-color); z-index: 1;"></div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card-premium p-3">
                    <h6 class="fw-bold font-heading mb-3 text-dark dark-text-light">
                        {{ $user->role === 'guru' ? 'Daftar DUDI Bimbingan' : 'Daftar Mitra DUDI Aktif' }}
                    </h6>
                    <div class="pe-1" style="max-height: 360px; overflow-y: auto;">
                        @forelse($dudiList as $dudiId => $dudiItem)
                            <div class="p-2.5 mb-2 border rounded" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="fw-bold text-primary font-heading" style="font-size: 13px;">{{ $dudiItem['dudi']->nama }}</span>
                                    <span class="badge bg-primary-light text-primary" style="font-size: 10px; font-weight: 700;">{{ count($dudiItem['placements']) }} Siswa</span>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="align-middle me-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>{{ Str::limit($dudiItem['dudi']->alamat, 45) }}
                                </small>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">
                                Belum ada data penempatan DUDI aktif saat ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Pengumuman Singkat (Semua Role) -->
    @if(count($announcements) > 0)
        <div class="card-premium p-3 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold font-heading m-0 text-dark dark-text-light d-flex align-items-center gap-2">
                    <svg class="text-primary" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <span>Pengumuman & Informasi Terbaru</span>
                </h6>
                <a href="{{ route('pengumuman.index') }}" class="small text-primary text-decoration-none font-heading" style="font-size: 11.5px;">Lihat Semua &rarr;</a>
            </div>
            <div class="row g-2">
                @foreach($announcements->take(2) as $announce)
                    <div class="col-md-6">
                        <div class="p-2.5 rounded border h-100" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-dark font-heading" style="font-size: 13px;">{{ $announce->judul }}</strong>
                                <small class="text-muted" style="font-size: 10px;">{{ $announce->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="text-secondary small m-0" style="font-size: 11.5px; line-height: 1.4;">{{ Str::limit(strip_tags($announce->isi), 120) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@if(auth()->user()->role === 'admin' || auth()->user()->role === 'guru')
@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const mapEl = document.getElementById('monitoringMap');
        if (!mapEl || typeof L === 'undefined') return;

        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        });

        const placements = @json($placements);
        const dudiGroups = {};

        placements.forEach(p => {
            if (p.dudi && p.dudi.latitude && p.dudi.longitude) {
                if (!dudiGroups[p.dudi_id]) {
                    dudiGroups[p.dudi_id] = {
                        name: p.dudi.nama,
                        lat: parseFloat(p.dudi.latitude),
                        lng: parseFloat(p.dudi.longitude),
                        students: []
                    };
                }
                dudiGroups[p.dudi_id].students.push(p);
            }
        });

        const keys = Object.keys(dudiGroups);
        let centerLat = -6.7487;
        let centerLng = 111.0378;

        if (keys.length > 0) {
            centerLat = dudiGroups[keys[0]].lat;
            centerLng = dudiGroups[keys[0]].lng;
        }

        const map = L.map('monitoringMap').setView([centerLat, centerLng], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        keys.forEach(dudiId => {
            const group = dudiGroups[dudiId];
            if (!isNaN(group.lat) && !isNaN(group.lng)) {
                let popupHtml = `<div style="font-family: inherit; font-size: 12px; min-width: 160px;">
                    <strong style="color: #4f46e5; font-size: 13px;">${group.name}</strong><br>
                    <span style="color: #64748b; font-size: 11px;">Total Siswa: ${group.students.length}</span>
                </div>`;
                L.marker([group.lat, group.lng]).addTo(map).bindPopup(popupHtml);
            }
        });
    });
</script>
@endsection
@endif
