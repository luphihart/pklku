@extends('layouts.admin')

@section('title', 'Presensi Harian - PKLku')
@section('page_title', 'Presensi Lapangan Harian')

@section('styles')
<!-- Leaflet maps style already imported in app.css, but just in case we need extra map formatting -->
<style>
    #map {
        height: 250px;
        border-radius: 0.5rem;
        border: 1px solid var(--border-color);
        margin-bottom: 1.25rem;
        z-index: 1;
    }
    #camera-preview {
        width: 100%;
        max-width: 320px;
        aspect-ratio: 4/3;
        height: auto;
        border-radius: 0.5rem;
        background-color: #000;
        object-fit: cover;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        transform: scaleX(-1);
    }
    #selfie-canvas {
        display: none;
    }
    .attendance-card-item {
        transition: all 0.2s ease-in-out;
        border: 1px solid var(--border-color) !important;
        background-color: var(--bg-card);
    }
    .attendance-card-item:hover {
        border-color: rgba(79, 70, 229, 0.3) !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    }
    .attendance-photo-thumb {
        position: relative;
        cursor: pointer;
        display: inline-block;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .attendance-photo-thumb:hover {
        transform: scale(1.06);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
    }
    .attendance-photo-thumb img {
        display: block;
        object-fit: cover;
    }
    .attendance-photo-thumb .photo-badge {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    @media (max-width: 576px) {
        .btn-attendance {
            font-size: 12px !important;
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0" @if($placement) x-data="attendanceHandler()" @endif>
    @if(!$placement)
        <div class="card-premium empty-state text-center py-5">
            <div class="empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h6 class="empty-state-title">Penempatan Belum Aktif</h6>
            <p class="empty-state-text">Akses presensi hanya aktif ketika plotting penempatan Anda telah diselesaikan oleh Admin.</p>
        </div>
    @else
        @php
            $todayHoliday = \App\Modules\MasterData\Models\HariLibur::getHoliday(now()->toDateString());
            $isPlacementHoliday = $placement ? $placement->isPlacementHoliday() : false;
        @endphp

        @if($todayHoliday)
            <!-- Holiday Hero Banner -->
            <div class="card-premium mb-4 p-4 text-center" style="background-color: var(--bg-card); border-left: 4px solid var(--info) !important;">
                <div class="d-inline-flex p-3 rounded-circle bg-info-light text-info mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h5 class="fw-bold font-heading text-dark m-0">Hari Ini Libur Nasional: {{ $todayHoliday->nama }}</h5>
                <p class="text-secondary small mt-1 mb-0">{{ $todayHoliday->keterangan ?: 'Hari libur resmi nasional. Anda tidak perlu melakukan presensi kehadiran hari ini.' }}</p>
            </div>
        @elseif($isPlacementHoliday)
            <!-- Weekly Day Off Hero Banner -->
            <div class="card-premium mb-4 p-4 text-center" style="background-color: var(--bg-card); border-left: 4px solid #64748b !important;">
                <div class="d-inline-flex p-3 rounded-circle bg-secondary-light text-secondary mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h5 class="fw-bold font-heading text-dark m-0">Hari Ini Jadwal Libur Mingguan Anda ({{ \Carbon\Carbon::now()->translatedFormat('l') }})</h5>
                <p class="text-secondary small mt-1 mb-0">Hari ini merupakan jadwal libur rutin penempatan Anda. Anda tidak perlu melakukan presensi dan tidak terhitung Alpha.</p>
            </div>
        @else
            @php
                $isTodayLiburShift = $today && $today->status_masuk === 'libur_shift';
            @endphp
            <!-- Daily Presence Status Hero Banner -->
            <div class="card-premium mb-4 p-3 d-flex flex-row align-items-center justify-content-between flex-wrap gap-2" style="background-color: var(--bg-card); border-left: 4px solid {{ $isTodayLiburShift ? '#0284c7' : ($today && $today->jam_masuk ? 'var(--success)' : 'var(--warning)') }} !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-circle {{ $isTodayLiburShift ? 'bg-info-light text-info' : ($today && $today->jam_masuk ? 'bg-success-light text-success' : 'bg-warning-light text-warning') }} d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        @if($isTodayLiburShift)
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        @elseif($today && $today->jam_masuk)
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fw-bold d-block" style="font-size: 11px; letter-spacing: 0.5px;">Status Hari Ini: {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                        @if($isTodayLiburShift)
                            <span class="fw-bold text-info font-heading" style="font-size: 15px; color: #0284c7 !important;">Jadwal Libur Shift DUDI (Off Day)</span>
                        @elseif($today && $today->jam_pulang)
                            <span class="fw-bold text-success font-heading" style="font-size: 15px;">Selesai Presensi Hari Ini (Masuk: {{ substr($today->jam_masuk, 0, 5) }} | Pulang: {{ substr($today->jam_pulang, 0, 5) }})</span>
                        @elseif($today && $today->jam_masuk)
                            <span class="fw-bold text-primary font-heading" style="font-size: 15px;">Sudah Check In Pagi ({{ substr($today->jam_masuk, 0, 5) }}) — Jangan lupa Check Out saat jam pulang</span>
                        @else
                            <span class="fw-bold text-dark font-heading" style="font-size: 15px;">Belum Melakukan Presensi Masuk Hari Ini</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($isTodayLiburShift)
                        <button type="button" class="btn btn-sm btn-outline-danger font-heading d-flex align-items-center gap-1" @click="cancelLiburShift()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Batalkan Libur (Masuk Kerja)
                        </button>
                    @endif
                    <a href="{{ route('jurnal.index') }}" class="btn btn-sm btn-outline-primary font-heading d-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Tulis Jurnal
                    </a>
                </div>
            </div>
        @endif

        @php
            $isWfaToday = $placement ? $placement->isWfaToday() : false;
            $activeShift = ($today && $today->shift_harian) ? $today->shift_harian : null;
            $shiftInfo = $placement ? $placement->getEffectiveShiftHours($activeShift) : null;
            $globalSettings = \App\Modules\Setting\Models\Setting::pluck('value', 'key');
            $isTodayLiburShift = $today && $today->status_masuk === 'libur_shift';
        @endphp

        <div class="row">
            <!-- Camera & Maps panel -->
            <div class="col-md-7 mb-4">
                <div class="card-premium">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">Panel Presensi Mandiri</h5>
                        @if($isTodayLiburShift)
                            <span class="badge bg-info-light text-info fw-semibold px-2 py-1" style="font-size: 12px; background-color: rgba(14, 165, 233, 0.12); color: #0284c7;">
                                Libur Shift Aktif
                            </span>
                        @elseif($isWfaToday)
                            <span class="badge bg-primary-light text-primary fw-semibold px-2 py-1" style="font-size: 12px;">
                                Mode WFA (Bebas Radius)
                            </span>
                        @else
                            <span class="badge bg-secondary-light text-secondary fw-semibold px-2 py-1" style="font-size: 12px;">
                                Mode WFO (Di Kantor DUDI)
                            </span>
                        @endif
                    </div>

                    @if($isTodayLiburShift)
                        <!-- Libur Shift Active Banner -->
                        <div class="p-4 rounded text-center my-3" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.08) 0%, rgba(99, 102, 241, 0.06) 100%); border: 1px dashed #0284c7;">
                            <div class="p-3 d-inline-flex rounded-circle bg-white shadow-sm mb-3 text-info" style="color: #0284c7 !important;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <h5 class="fw-bold font-heading text-dark mb-1">Hari Ini Libur / Off Shift</h5>
                            <p class="text-secondary small mb-3">Status hari ini tercatat sebagai libur shift resmi dari DUDI. Anda tidak dikenakan catatan Alpha.</p>
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-danger" @click="cancelLiburShift()">
                                    Batalkan Libur (Jika Masuk Bekerja)
                                </button>
                            </div>
                        </div>
                    @else
                        <!-- Shift & Schedule Info Banner -->
                        @if($shiftInfo)
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 p-2 px-3 rounded" style="background-color: var(--bg-canvas); border: 1px solid var(--border-color);">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-indigo-light text-indigo fw-semibold" style="font-size: 12px; background-color: rgba(79, 70, 229, 0.12); color: #4f46e5;">
                                        {{ $shiftInfo['label'] }}
                                    </span>
                                    @if(($placement->tipe_shift ?? 'reguler') === 'rolling' && !$activeShift)
                                        <span class="badge bg-purple-light text-purple" style="font-size: 11px; background-color: rgba(147, 51, 234, 0.1); color: #9333ea;">Auto-Detect Saat Check-In</span>
                                    @endif
                                </div>
                                <div class="small text-muted" style="font-size: 12px;">
                                    @if(($placement->tipe_shift ?? 'reguler') === 'rolling' && !$activeShift)
                                        Buka Pagi: <strong class="text-dark">{{ substr($globalSettings['shift_pagi_masuk'] ?? '07:00', 0, 5) }}</strong> | Buka Siang: <strong class="text-dark">{{ substr($globalSettings['shift_siang_masuk'] ?? '11:00', 0, 5) }}</strong> | Buka Sore: <strong class="text-dark">{{ substr($globalSettings['shift_sore_masuk'] ?? '15:00', 0, 5) }}</strong>
                                    @else
                                        Batas Tepat Waktu: <strong class="text-dark">{{ $shiftInfo['batas_terlambat'] }}</strong> | Mulai Pulang: <strong class="text-dark">{{ $shiftInfo['jam_pulang'] }}</strong>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        <!-- DUDI Info -->
                        @if($isWfaToday)
                            <div class="alert alert-primary border-0 mb-3" style="background-color: rgba(59, 130, 246, 0.08); color: #2563eb;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="font-heading" style="font-size: 14px;">DUDI: {{ $placement->dudi->nama }}</strong>
                                    <span class="badge bg-primary text-white" style="font-size: 11px;">Jadwal Hari Ini: WFA</span>
                                </div>
                                <small class="d-block" style="font-size: 12px;"><strong>Mode Bebas Radius Aktif:</strong> Anda dapat melakukan presensi dari mana saja hari ini.</small>
                                <small class="d-block text-muted mt-1" style="font-size: 11px;">Status GPS Anda: <span class="fw-bold text-dark" x-text="distanceString">Mendeteksi...</span></small>
                            </div>
                        @else
                            <div class="alert alert-info border-0 mb-3" style="background-color: rgba(79, 70, 229, 0.08); color: var(--accent-primary);">
                                <strong class="font-heading" style="font-size: 14px;">DUDI: {{ $placement->dudi->nama }}</strong><br>
                                <small class="d-block mt-1" style="font-size: 12px;">Koordinat Target: {{ $placement->dudi->latitude }}, {{ $placement->dudi->longitude }}</small>
                                <small class="d-block" style="font-size: 12px;">Radius Aman: {{ $placement->dudi->radius_meter }} Meter | Jarak Anda: <span class="fw-bold" x-text="distanceString">Mendeteksi...</span></small>
                            </div>
                        @endif

                        <!-- Leaflet map -->
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted fw-bold font-heading" style="font-size: 11px;">PETA RADAR LOKASI REALTIME</small>
                            <button type="button" class="btn btn-xs btn-outline-primary d-flex align-items-center gap-1 font-heading" @click="refreshGps()" style="font-size: 11px; padding: 2px 8px; border-radius: 6px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Kalibrasi / Refresh GPS</span>
                            </button>
                        </div>
                        <div id="map"></div>

                        <!-- Camera Section -->
                        <div class="text-center mb-3">
                            <div class="d-inline-block position-relative">
                                <video id="camera-preview" autoplay playsinline muted></video>
                                <canvas id="selfie-canvas" width="640" height="480"></canvas>
                            </div>
                            
                            <div class="mt-2 d-flex justify-content-center align-items-center gap-2">
                                <span class="badge" :class="cameraActive ? 'bg-success-light text-success' : 'bg-danger-light text-danger'" style="font-size: 12px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="align-middle me-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span x-text="cameraActive ? 'Kamera Aktif' : 'Kamera Mati / Belum Diizinkan'"></span>
                                </span>

                                <button type="button" class="btn btn-xs btn-outline-secondary font-heading" @click="initCamera()" style="font-size: 11px; padding: 2px 8px; border-radius: 6px;" title="Muat Ulang Kamera">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span>Muat Ulang Kamera</span>
                                </button>
                            </div>
                        </div>

                        <!-- Today Leave Banner -->
                        @if(isset($todayLeave) && $todayLeave)
                            <div class="alert alert-warning border-0 mb-3" style="background-color: rgba(245, 158, 11, 0.12); color: #b45309; border-left: 4px solid #f59e0b !important;">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge {{ $todayLeave->tipe === 'sakit' ? 'bg-danger' : 'bg-primary' }} text-white">
                                        {{ $todayLeave->tipe === 'sakit' ? 'Sakit' : 'Izin' }} (Disetujui)
                                    </span>
                                    <strong class="font-heading" style="font-size: 13px;">Anda Sedang Izin/Sakit Hari Ini</strong>
                                </div>
                                <small class="d-block" style="font-size: 12px;">Alasan: <strong>{{ $todayLeave->alasan }}</strong></small>
                                <small class="d-block text-muted mt-1" style="font-size: 11px;">Periode: {{ \Carbon\Carbon::parse($todayLeave->tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($todayLeave->tanggal_selesai)->format('d/m/Y') }}. Anda tidak perlu presensi hari ini.</small>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="row g-2">
                            <div class="col-6">
                                <button class="btn btn-success w-100 py-3 font-heading fw-bold btn-attendance" 
                                        :disabled="!inRadius || todayHasCheckIn || submitting" 
                                        @click="submitAttendance('checkin')"
                                        aria-label="Lakukan Check In Pagi">
                                    <span x-text="todayHasCheckIn ? 'Sudah Check In' : 'CHECK IN PAGI'"></span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-warning w-100 py-3 font-heading fw-bold btn-attendance" 
                                        :disabled="!inRadius || !todayHasCheckIn || todayHasCheckOut || submitting" 
                                        @click="submitAttendance('checkout')"
                                        aria-label="Lakukan Check Out Sore">
                                    <span x-text="todayHasCheckOut ? 'Sudah Check Out' : 'CHECK OUT SORE'"></span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-3 text-center" x-show="!isWfa && !inRadius">
                            <div class="d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill bg-danger-light text-danger small fw-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Anda berada di luar radius presensi DUDI. Tombol absen dinonaktifkan.</span>
                            </div>
                        </div>

                        <!-- Libur Shift Self-Service Option -->
                        @if(!$today || !$today->jam_masuk)
                            @php
                                $remainingOff = max(0, ($weeklyOffQuota ?? 2) - ($weeklyOffUsed ?? 0));
                            @endphp
                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2 rounded bg-light border">
                                    <div class="text-start">
                                        <small class="d-block fw-semibold text-dark font-heading">Hari Ini Jadwal Libur / Off Shift DUDI?</small>
                                        <small class="text-muted" style="font-size: 11px;">
                                            Sisa kuota libur shift minggu ini: <strong class="text-primary">{{ $remainingOff }} hari</strong> (dari {{ $weeklyOffQuota ?? 2 }} hari)
                                        </small>
                                    </div>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info font-heading d-flex align-items-center gap-1"
                                            @click="markLiburShift()"
                                            {{ $remainingOff <= 0 ? 'disabled' : '' }}
                                            title="{{ $remainingOff <= 0 ? 'Kuota libur shift minggu ini telah habis' : 'Tandai libur shift' }}">
                                        <span>Tandai Libur Shift</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>            <!-- History panel -->
            <div class="col-md-5 mb-4">
                <div class="card-premium">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                        <div>
                            <h5 class="fw-bold font-heading m-0 text-dark">Riwayat Bulan Ini</h5>
                            <small class="text-muted" style="font-size: 11px;">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }} &bull; {{ count($history) }} Catatan</small>
                        </div>
                        <a href="{{ route('laporan.murid_presensi_pdf') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 font-heading" style="font-size: 11.5px; padding: 4px 10px; border-radius: 6px;" aria-label="Unduh Rekap Presensi PDF">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Unduh PDF</span>
                        </a>
                    </div>

                    <!-- Monthly Stats Micro-Bar -->
                    @php
                        $cntHadir = $history->where('status_masuk', 'tepat_waktu')->count();
                        $cntTerlambat = $history->where('status_masuk', 'terlambat')->count();
                        $cntIzinSakit = $history->whereIn('type', ['izin', 'sakit'])->count();
                        $cntLiburShift = $history->where('type', 'libur_shift')->count();
                    @endphp
                    <div class="row g-2 mb-3">
                        <div class="col-3">
                            <div class="p-2 rounded text-center bg-light border" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                                <div class="fw-bold font-heading text-success" style="font-size: 15px; line-height: 1;">{{ $cntHadir }}</div>
                                <span class="text-muted text-uppercase" style="font-size: 9px; font-weight: 600;">Hadir</span>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded text-center bg-light border" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                                <div class="fw-bold font-heading text-danger" style="font-size: 15px; line-height: 1;">{{ $cntTerlambat }}</div>
                                <span class="text-muted text-uppercase" style="font-size: 9px; font-weight: 600;">Telat</span>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded text-center bg-light border" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                                <div class="fw-bold font-heading text-info" style="font-size: 15px; line-height: 1;">{{ $cntIzinSakit }}</div>
                                <span class="text-muted text-uppercase" style="font-size: 9px; font-weight: 600;">Izin/Skt</span>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded text-center bg-light border" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                                <div class="fw-bold font-heading text-primary" style="font-size: 15px; line-height: 1;">{{ $cntLiburShift }}</div>
                                <span class="text-muted text-uppercase" style="font-size: 9px; font-weight: 600;">Off Shift</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- History Cards Feed -->
                    <div class="attendance-history-feed" style="max-height: 520px; overflow-y: auto; padding-right: 2px;">
                        @forelse($history as $h)
                            <div class="attendance-card-item p-3 mb-2.5 rounded-3">
                                <!-- Top Row: Date & Status Badge -->
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="text-center px-2 py-1 rounded bg-light border flex-shrink-0" style="min-width: 44px; background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                                            <div class="text-uppercase fw-bold text-muted" style="font-size: 8.5px; line-height: 1;">{{ \Carbon\Carbon::parse($h->tanggal)->translatedFormat('D') }}</div>
                                            <div class="fw-bold text-dark font-heading" style="font-size: 14px; line-height: 1.2;">{{ \Carbon\Carbon::parse($h->tanggal)->format('d') }}</div>
                                            <div class="text-muted" style="font-size: 8.5px; line-height: 1;">{{ \Carbon\Carbon::parse($h->tanggal)->translatedFormat('M') }}</div>
                                        </div>
                                        <div>
                                            <div class="fw-bold font-heading text-dark" style="font-size: 13px;">
                                                {{ \Carbon\Carbon::parse($h->tanggal)->translatedFormat('l, d F Y') }}
                                            </div>
                                            <div class="d-flex align-items-center gap-1 mt-0.5">
                                                @if(!empty($h->shift_harian))
                                                    <span class="badge bg-secondary-light text-secondary" style="font-size: 9.5px; padding: 2px 6px;">{{ ucfirst($h->shift_harian) }}</span>
                                                @endif
                                                @if(!empty($h->is_wfa))
                                                    <span class="badge bg-primary-light text-primary" style="font-size: 9.5px; padding: 2px 6px;">WFA</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        @if($h->type === 'libur_shift' || $h->status_masuk === 'libur_shift')
                                            <span class="status-badge bg-info-light text-info" style="font-size: 11px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                                Libur Shift
                                            </span>
                                        @elseif($h->type === 'alpha' || $h->status_masuk === 'alpha')
                                            <span class="status-badge bg-danger-light text-danger" style="font-size: 11px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                Alpha
                                            </span>
                                        @elseif($h->type === 'izin')
                                            <span class="status-badge bg-info-light text-info" style="font-size: 11px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                Izin
                                            </span>
                                        @elseif($h->type === 'sakit')
                                            <span class="status-badge bg-danger-light text-danger" style="font-size: 11px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                Sakit
                                            </span>
                                        @elseif($h->status_masuk === 'tepat_waktu')
                                            <span class="status-badge bg-success-light text-success" style="font-size: 11px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Hadir
                                            </span>
                                        @else
                                            <span class="status-badge bg-danger-light text-danger" style="font-size: 11px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Terlambat
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Bottom Row: Check-in / Check-out Details -->
                                @if($h->type === 'izin' || $h->type === 'sakit')
                                    <div class="p-2.5 rounded bg-light border" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important; font-size: 12px;">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div style="min-width: 0; flex: 1;">
                                                <span class="text-muted d-block mb-1" style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px;">Keterangan:</span>
                                                <div class="text-dark fw-medium" style="line-height: 1.45; word-break: break-word;">
                                                    {{ $h->keterangan ?: 'Izin tidak hadir' }}
                                                </div>
                                            </div>
                                            @if($h->surat_pendukung)
                                                <div class="flex-shrink-0 pt-0.5">
                                                    <a href="{{ asset('storage/izin/' . $h->surat_pendukung) }}" target="_blank" class="btn btn-xs btn-outline-primary text-nowrap d-inline-flex align-items-center gap-1 font-heading" style="font-size: 11px; padding: 3px 8px; border-radius: 6px;" title="Lihat Dokumen Surat">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                        </svg>
                                                        <span>Surat</span>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($h->type === 'libur_shift')
                                    <div class="p-2 rounded bg-light border text-muted" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important; font-size: 11.5px; line-height: 1.4;">
                                        Jadwal libur shift mandiri / off day DUDI.
                                    </div>
                                @elseif($h->type === 'alpha')
                                    <div class="p-2 rounded bg-light border text-danger fw-medium" style="background-color: rgba(239, 68, 68, 0.05) !important; border-color: rgba(239, 68, 68, 0.2) !important; font-size: 11.5px; line-height: 1.4;">
                                        Tidak melakukan presensi harian (Alpha).
                                    </div>
                                @else
                                    <div class="row g-2">
                                        <!-- Masuk Box -->
                                        <div class="col-6">
                                            <div class="p-2 rounded border d-flex align-items-center justify-content-between" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($h->foto_masuk)
                                                        <div class="attendance-photo-thumb" 
                                                             data-bs-toggle="modal" 
                                                             data-bs-target="#modalPreviewFotoPresensi"
                                                             data-foto-url="{{ asset('storage/attendance/' . $h->foto_masuk) }}" 
                                                             data-foto-title="Foto Check In Masuk" 
                                                             data-foto-date="{{ \Carbon\Carbon::parse($h->tanggal)->translatedFormat('l, d F Y') }}" 
                                                             data-foto-time="{{ substr($h->jam_masuk, 0, 5) }} WIB"
                                                             onclick="window.previewAttendancePhoto('{{ asset('storage/attendance/' . $h->foto_masuk) }}', 'Foto Check In Masuk', '{{ \Carbon\Carbon::parse($h->tanggal)->translatedFormat('l, d F Y') }}', '{{ substr($h->jam_masuk, 0, 5) }} WIB')" 
                                                             title="Klik untuk melihat foto presensi">
                                                            <img src="{{ asset('storage/attendance/' . $h->foto_masuk) }}" class="rounded-2 border" width="36" height="36" alt="Foto Masuk" loading="lazy">
                                                            <span class="photo-badge bg-success text-white">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                            </span>
                                                        </div>
                                                    @else
                                                        <div class="rounded-2 bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 36px; height: 36px; flex-shrink: 0;">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <span class="text-muted d-block text-uppercase" style="font-size: 9.5px; font-weight: 600;">Masuk</span>
                                                        <span class="fw-bold text-success font-heading" style="font-size: 13px;">{{ $h->jam_masuk ? substr($h->jam_masuk, 0, 5) : '-' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pulang Box -->
                                        <div class="col-6">
                                            <div class="p-2 rounded border d-flex align-items-center justify-content-between" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($h->foto_pulang)
                                                        <div class="attendance-photo-thumb" 
                                                             data-bs-toggle="modal" 
                                                             data-bs-target="#modalPreviewFotoPresensi"
                                                             data-foto-url="{{ asset('storage/attendance/' . $h->foto_pulang) }}" 
                                                             data-foto-title="Foto Check Out Pulang" 
                                                             data-foto-date="{{ \Carbon\Carbon::parse($h->tanggal)->translatedFormat('l, d F Y') }}" 
                                                             data-foto-time="{{ substr($h->jam_pulang, 0, 5) }} WIB"
                                                             onclick="window.previewAttendancePhoto('{{ asset('storage/attendance/' . $h->foto_pulang) }}', 'Foto Check Out Pulang', '{{ \Carbon\Carbon::parse($h->tanggal)->translatedFormat('l, d F Y') }}', '{{ substr($h->jam_pulang, 0, 5) }} WIB')" 
                                                             title="Klik untuk melihat foto presensi">
                                                            <img src="{{ asset('storage/attendance/' . $h->foto_pulang) }}" class="rounded-2 border" width="36" height="36" alt="Foto Pulang" loading="lazy">
                                                            <span class="photo-badge bg-warning text-white">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                            </span>
                                                        </div>
                                                    @else
                                                        <div class="rounded-2 bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 36px; height: 36px; flex-shrink: 0;">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <span class="text-muted d-block text-uppercase" style="font-size: 9.5px; font-weight: 600;">Pulang</span>
                                                        <span class="fw-bold {{ $h->jam_pulang ? 'text-warning' : 'text-muted' }} font-heading" style="font-size: 13px;">
                                                            {{ $h->jam_pulang ? substr($h->jam_pulang, 0, 5) : 'Belum Out' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="empty-state py-4 text-center">
                                <div class="empty-state-icon mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h6 class="empty-state-title" style="font-size: 13px;">Belum Ada Presensi</h6>
                                <p class="empty-state-text" style="font-size: 11.5px;">Riwayat presensi kehadiran Anda bulan ini akan otomatis muncul di sini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal Preview Foto Presensi Lightbox -->
<div class="modal fade" id="modalPreviewFotoPresensi" tabindex="-1" aria-labelledby="modalPreviewFotoPresensiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
            <div class="modal-header py-2.5 px-3 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                <div>
                    <h6 class="modal-title fw-bold font-heading m-0" id="previewFotoTitle" style="font-size: 14px;">Foto Bukti Presensi</h6>
                    <small class="text-muted" id="previewFotoSubtitle" style="font-size: 11px;">-</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="window.closeAttendancePhotoModal()"></button>
            </div>
            <div class="modal-body text-center p-3">
                <div class="rounded-3 overflow-hidden border shadow-sm mx-auto position-relative" style="max-width: 360px; background-color: #0f172a;">
                    <img src="" id="previewFotoImg" class="img-fluid w-100" style="object-fit: cover; max-height: 420px; display: block;" alt="Bukti Presensi">
                </div>
            </div>
            <div class="modal-footer py-2 px-3 border-top d-flex justify-content-between align-items-center" style="border-top-color: var(--border-color) !important;">
                <span class="text-muted small" id="previewFotoTime" style="font-size: 11.5px;">-</span>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="previewFotoDirectLink" target="_blank" class="btn btn-sm btn-outline-primary font-heading d-inline-flex align-items-center gap-1" style="font-size: 11.5px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span>Buka Tab Baru</span>
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary font-heading" data-bs-dismiss="modal" onclick="window.closeAttendancePhotoModal()">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($placement)
<!-- Import Leaflet JS programmatically (CSS is already loaded in app.css) -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    function attendanceHandler() {
        return {
            placementId: "{{ $placement->id }}",
            dudiLat: {{ $placement->dudi?->latitude ?? 0 }},
            dudiLng: {{ $placement->dudi?->longitude ?? 0 }},
            allowedRadius: {{ $placement->dudi?->radius_meter ?? 100 }},
            isWfa: {{ $placement && $placement->isWfaToday() ? 'true' : 'false' }},
            
            userLat: null,
            userLng: null,
            distance: null,
            distanceString: 'Mencari...',
            inRadius: false,
            
            todayHasCheckIn: {{ $today && $today->jam_masuk ? 'true' : 'false' }},
            todayHasCheckOut: {{ $today && $today->jam_pulang ? 'true' : 'false' }},
            submitting: false,
            
            map: null,
            userMarker: null,
            cameraActive: false,
            stream: null,

            init() {
                this.initMap();
                this.trackLocation();
                this.initCamera();
            },

            initMap() {
                // Fix Leaflet broken default marker icons
                delete L.Icon.Default.prototype._getIconUrl;
                L.Icon.Default.mergeOptions({
                    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                });

                // Initialize map centered at DUDI
                this.map = L.map('map').setView([this.dudiLat, this.dudiLng], 16);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(this.map);

                // Add DUDI circle geofence
                L.circle([this.dudiLat, this.dudiLng], {
                    color: 'indigo',
                    fillColor: '#818cf8',
                    fillOpacity: 0.3,
                    radius: this.allowedRadius
                }).addTo(this.map);

                // Add DUDI Marker
                L.marker([this.dudiLat, this.dudiLng]).addTo(this.map)
                    .bindPopup('Lokasi Kantor DUDI')
                    .openPopup();
            },

            gpsAccuracy: null,

            trackLocation() {
                if (navigator.geolocation) {
                    const handlePosition = (position) => {
                        this.userLat = position.coords.latitude;
                        this.userLng = position.coords.longitude;
                        this.gpsAccuracy = Math.round(position.coords.accuracy || 0);
                        
                        // Calculate Distance
                        this.distance = this.calculateDistance(this.userLat, this.userLng, this.dudiLat, this.dudiLng);
                        if (this.isWfa) {
                            this.distanceString = `GPS Terkunci (Mode WFA - Bebas Radius) ±${this.gpsAccuracy}m`;
                            this.inRadius = true;
                        } else {
                            this.distanceString = `${Math.round(this.distance)} Meter (Akurasi: ±${this.gpsAccuracy}m)`;
                            this.inRadius = this.distance <= this.allowedRadius;
                        }

                        // Update Map Marker for user
                        if (this.userMarker) {
                            this.userMarker.setLatLng([this.userLat, this.userLng]);
                        } else {
                            const userIcon = L.icon({
                                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41]
                            });
                            this.userMarker = L.marker([this.userLat, this.userLng], { icon: userIcon }).addTo(this.map)
                                .bindPopup(`Lokasi Anda Sekarang (±${this.gpsAccuracy}m)`)
                                .openPopup();
                        }
                        
                        // Adjust map bounds to show both
                        const group = new L.featureGroup([L.marker([this.dudiLat, this.dudiLng]), this.userMarker]);
                        this.map.fitBounds(group.getBounds().pad(0.2));
                    };

                    const handleError = (error) => {
                        console.warn('GPS error:', error);
                        if (error.code === 1) {
                            this.distanceString = 'Izin lokasi ditolak (Buka pengaturan browser)';
                        } else if (error.code === 2) {
                            this.distanceString = 'Sinyal GPS lemah / tidak tersedia';
                        } else {
                            this.distanceString = 'GPS timeout (Klik tombol Kalibrasi GPS)';
                        }
                        this.inRadius = false;
                    };

                    const options = {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 0 // Pastikan koordinat diambil langsung dari satelit/sensor GPS realtime, bukan cache
                    };

                    // Initial single fetch
                    navigator.geolocation.getCurrentPosition(handlePosition, handleError, options);

                    // Continuous watch
                    navigator.geolocation.watchPosition(handlePosition, handleError, options);
                } else {
                    this.distanceString = 'GPS tidak didukung browser';
                    this.inRadius = false;
                }
            },

            refreshGps() {
                this.distanceString = 'Mencari sinyal GPS realtime...';
                if (!navigator.geolocation) {
                    Swal.fire({
                        icon: 'error',
                        title: 'GPS Tidak Didukung',
                        text: 'Browser Anda tidak mendukung fitur Geolocation GPS.',
                        confirmButtonColor: 'var(--accent-primary)'
                    });
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.userLat = pos.coords.latitude;
                        this.userLng = pos.coords.longitude;
                        this.gpsAccuracy = Math.round(pos.coords.accuracy || 0);
                        this.distance = this.calculateDistance(this.userLat, this.userLng, this.dudiLat, this.dudiLng);
                        if (this.isWfa) {
                            this.distanceString = `GPS Terkunci (Mode WFA - Bebas Radius) ±${this.gpsAccuracy}m`;
                            this.inRadius = true;
                        } else {
                            this.distanceString = `${Math.round(this.distance)} Meter (Akurasi: ±${this.gpsAccuracy}m)`;
                            this.inRadius = this.distance <= this.allowedRadius;
                        }

                        if (this.userMarker) {
                            this.userMarker.setLatLng([this.userLat, this.userLng]);
                            const group = new L.featureGroup([L.marker([this.dudiLat, this.dudiLng]), this.userMarker]);
                            this.map.fitBounds(group.getBounds().pad(0.2));
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'GPS Berhasil Dikalibrasi',
                            text: `Akurasi sensor: ±${this.gpsAccuracy}m. Jarak ke DUDI: ${Math.round(this.distance)} meter.`,
                            timer: 2200,
                            showConfirmButton: false
                        });
                    },
                    (err) => {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal Membaca GPS',
                            text: 'Pastikan fitur Lokasi / GPS di HP sudah dinyalakan dalam mode Akurasi Tinggi dan izin lokasi diaktifkan di browser Chrome.',
                            confirmButtonColor: 'var(--accent-primary)'
                        });
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            },

            calculateDistance(lat1, lon1, lat2, lon2) {
                const R = 6371e3; // Earth radius in meters
                const phi1 = lat1 * Math.PI/180;
                const phi2 = lat2 * Math.PI/180;
                const deltaPhi = (lat2-lat1) * Math.PI/180;
                const deltaLambda = (lon2-lon1) * Math.PI/180;

                const a = Math.sin(deltaPhi/2) * Math.sin(deltaPhi/2) +
                          Math.cos(phi1) * Math.cos(phi2) *
                          Math.sin(deltaLambda/2) * Math.sin(deltaLambda/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

                return R * c; // distance in meters
            },

            initCamera() {
                const video = document.getElementById('camera-preview');
                if (!video) return;

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.cameraActive = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Kamera Tidak Didukung',
                        text: 'Browser Anda tidak mendukung API kamera atau situs diakses tanpa protokol aman (HTTPS).',
                        confirmButtonColor: 'var(--accent-primary)'
                    });
                    return;
                }

                // Stop existing stream if any
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                }

                const constraintsList = [
                    { video: { facingMode: { ideal: 'user' }, width: { ideal: 640 }, height: { ideal: 480 } }, audio: false },
                    { video: { facingMode: 'user' }, audio: false },
                    { video: true, audio: false }
                ];

                const tryStartStream = (index) => {
                    if (index >= constraintsList.length) {
                        this.cameraActive = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Kamera Tidak Dapat Dimulai',
                            text: 'Pastikan izin kamera telah diaktifkan pada pengaturan browser dan tidak sedang dipakai oleh aplikasi lain.',
                            confirmButtonColor: 'var(--accent-primary)'
                        });
                        return;
                    }

                    navigator.mediaDevices.getUserMedia(constraintsList[index])
                        .then((stream) => {
                            this.stream = stream;
                            video.srcObject = stream;
                            video.setAttribute('playsinline', '');
                            video.setAttribute('muted', '');
                            video.muted = true;
                            video.onloadedmetadata = () => {
                                video.play().catch(e => console.warn('Video play catch:', e));
                            };
                            this.cameraActive = true;
                        })
                        .catch((err) => {
                            console.warn('Camera constraint retry', index, err);
                            tryStartStream(index + 1);
                        });
                };

                tryStartStream(0);
            },

            submitAttendance(type) {
                this.submitting = true;
                
                const video = document.getElementById('camera-preview');
                const canvas = document.getElementById('selfie-canvas');
                const context = canvas.getContext('2d');
                
                // Set canvas size to native video size for original aspect ratio
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;

                // Draw video frame to canvas
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const base64Photo = canvas.toDataURL('image/jpeg', 0.85);

                const endpoint = type === 'checkin' ? '{{ route("presensi.checkin") }}' : '{{ route("presensi.checkout") }}';

                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        penempatan_pkl_id: this.placementId,
                        latitude: this.userLat,
                        longitude: this.userLng,
                        photo: base64Photo
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message,
                            confirmButtonColor: 'var(--accent-primary)'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Presensi Gagal',
                            text: data.message,
                            confirmButtonColor: 'var(--accent-primary)'
                        });
                        this.submitting = false;
                    }
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Bermasalah',
                        text: 'Terjadi kesalahan koneksi server.',
                        confirmButtonColor: 'var(--accent-primary)'
                    });
                    this.submitting = false;
                });
            },

            markLiburShift() {
                Swal.fire({
                    title: 'Konfirmasi Libur Shift',
                    text: 'Apakah hari ini adalah jadwal libur / off shift Anda dari DUDI?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Tandai Libur Shift',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#0284c7',
                    cancelButtonColor: '#6b7280'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submitting = true;
                        fetch('{{ route("presensi.libur_shift") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                penempatan_pkl_id: this.placementId
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: data.message,
                                    confirmButtonColor: '#0284c7'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: data.message,
                                    confirmButtonColor: 'var(--accent-primary)'
                                });
                                this.submitting = false;
                            }
                        })
                        .catch(err => {
                            Swal.fire('Error', 'Terjadi kesalahan koneksi server.', 'error');
                            this.submitting = false;
                        });
                    }
                });
            },

            cancelLiburShift() {
                Swal.fire({
                    title: 'Batalkan Libur Shift?',
                    text: 'Apakah Anda ingin membatalkan tanda libur shift hari ini dan melakukan presensi masuk?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Batalkan Libur',
                    cancelButtonText: 'Kembali',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submitting = true;
                        fetch('{{ route("presensi.cancel_libur_shift") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                penempatan_pkl_id: this.placementId
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Dibatalkan',
                                    text: data.message,
                                    confirmButtonColor: '#0284c7'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: data.message,
                                    confirmButtonColor: 'var(--accent-primary)'
                                });
                                this.submitting = false;
                            }
                        })
                        .catch(err => {
                            Swal.fire('Error', 'Terjadi kesalahan koneksi server.', 'error');
                            this.submitting = false;
                        });
                    }
                });
            }
        }
    }

    window.previewAttendancePhoto = function(url, title, date, time) {
        if (!url) return;
        const imgEl = document.getElementById('previewFotoImg');
        const titleEl = document.getElementById('previewFotoTitle');
        const subtitleEl = document.getElementById('previewFotoSubtitle');
        const timeEl = document.getElementById('previewFotoTime');
        const linkEl = document.getElementById('previewFotoDirectLink');
        
        if (imgEl) imgEl.src = url;
        if (titleEl) titleEl.innerText = title || 'Foto Bukti Presensi';
        if (subtitleEl) subtitleEl.innerText = date || '';
        if (timeEl) timeEl.innerText = 'Waktu Rekam: ' + (time || '-');
        if (linkEl) linkEl.href = url;
        
        const modalEl = document.getElementById('modalPreviewFotoPresensi');
        if (!modalEl) return;

        // 1. Try Bootstrap 5 Modal
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            try {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
                return;
            } catch(e) {
                console.warn("Bootstrap modal failed, falling back", e);
            }
        }
        
        // 2. Try jQuery Modal
        if (typeof window.$ !== 'undefined' && typeof window.$.fn.modal !== 'undefined') {
            try {
                window.$(modalEl).modal('show');
                return;
            } catch(e) {
                console.warn("jQuery modal failed", e);
            }
        }

        // 3. Fallback: Native CSS Modal display
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        modalEl.removeAttribute('aria-hidden');
        modalEl.setAttribute('aria-modal', 'true');
        
        let backdrop = document.getElementById('customPhotoBackdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'customPhotoBackdrop';
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
            backdrop.addEventListener('click', window.closeAttendancePhotoModal);
        }
    };

    window.closeAttendancePhotoModal = function() {
        const modalEl = document.getElementById('modalPreviewFotoPresensi');
        if (modalEl) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                try {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                } catch(e) {}
            }
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            modalEl.setAttribute('aria-hidden', 'true');
        }
        const backdrop = document.getElementById('customPhotoBackdrop');
        if (backdrop) {
            backdrop.remove();
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        // Event delegation for clicks on photo thumbs
        document.addEventListener('click', function(e) {
            const thumb = e.target.closest('.attendance-photo-thumb');
            if (!thumb) return;
            const url = thumb.getAttribute('data-foto-url');
            if (url) {
                const title = thumb.getAttribute('data-foto-title');
                const date = thumb.getAttribute('data-foto-date');
                const time = thumb.getAttribute('data-foto-time');
                window.previewAttendancePhoto(url, title, date, time);
            }
        });

        // Bootstrap show.bs.modal listener
        const modalEl = document.getElementById('modalPreviewFotoPresensi');
        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                if (trigger && trigger.hasAttribute('data-foto-url')) {
                    const url = trigger.getAttribute('data-foto-url');
                    const title = trigger.getAttribute('data-foto-title');
                    const date = trigger.getAttribute('data-foto-date');
                    const time = trigger.getAttribute('data-foto-time');
                    window.previewAttendancePhoto(url, title, date, time);
                }
            });
        }
    });
</script>
@endif
@endsection
