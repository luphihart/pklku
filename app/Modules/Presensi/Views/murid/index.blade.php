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
            <!-- Daily Presence Status Hero Banner -->
            <div class="card-premium mb-4 p-3 d-flex flex-row align-items-center justify-content-between flex-wrap gap-2" style="background-color: var(--bg-card); border-left: 4px solid {{ $today && $today->jam_masuk ? 'var(--success)' : 'var(--warning)' }} !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-circle {{ $today && $today->jam_masuk ? 'bg-success-light text-success' : 'bg-warning-light text-warning' }} d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        @if($today && $today->jam_masuk)
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
                        @if($today && $today->jam_pulang)
                            <span class="fw-bold text-success font-heading" style="font-size: 15px;">Selesai Presensi Hari Ini (Masuk: {{ substr($today->jam_masuk, 0, 5) }} | Pulang: {{ substr($today->jam_pulang, 0, 5) }})</span>
                        @elseif($today && $today->jam_masuk)
                            <span class="fw-bold text-primary font-heading" style="font-size: 15px;">Sudah Check In Pagi ({{ substr($today->jam_masuk, 0, 5) }}) — Jangan lupa Check Out saat jam pulang</span>
                        @else
                            <span class="fw-bold text-dark font-heading" style="font-size: 15px;">Belum Melakukan Presensi Masuk Hari Ini</span>
                        @endif
                    </div>
                </div>
                <div>
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
        @endphp

        <div class="row">
            <!-- Camera & Maps panel -->
            <div class="col-md-7 mb-4">
                <div class="card-premium">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">Panel Presensi Mandiri</h5>
                        @if($isWfaToday)
                            <span class="badge bg-primary-light text-primary fw-semibold px-2 py-1" style="font-size: 12px;">
                                🏠 Mode WFA (Bebas Radius)
                            </span>
                        @else
                            <span class="badge bg-secondary-light text-secondary fw-semibold px-2 py-1" style="font-size: 12px;">
                                🏢 Mode WFO (Di Kantor DUDI)
                            </span>
                        @endif
                    </div>

                    <!-- Shift & Schedule Info Banner -->
                    @if($shiftInfo)
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 p-2 px-3 rounded" style="background-color: var(--bg-canvas); border: 1px solid var(--border-color);">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-indigo-light text-indigo fw-semibold" style="font-size: 12px; background-color: rgba(79, 70, 229, 0.12); color: #4f46e5;">
                                    ⏱️ {{ $shiftInfo['label'] }}
                                </span>
                                @if(($placement->tipe_shift ?? 'reguler') === 'rolling' && !$activeShift)
                                    <span class="badge bg-purple-light text-purple" style="font-size: 11px; background-color: rgba(147, 51, 234, 0.1); color: #9333ea;">Auto-Detect Saat Check-In</span>
                                @endif
                            </div>
                            <div class="small text-muted" style="font-size: 12px;">
                                @if(($placement->tipe_shift ?? 'reguler') === 'rolling' && !$activeShift)
                                    Buka Pagi: <strong class="text-dark">{{ substr($globalSettings['shift_pagi_masuk'] ?? '06:30', 0, 5) }}</strong> | Buka Siang: <strong class="text-dark">{{ substr($globalSettings['shift_siang_masuk'] ?? '13:00', 0, 5) }}</strong>
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
                            <small class="d-block" style="font-size: 12px;">🏠 <strong>Mode Bebas Radius Aktif:</strong> Anda dapat melakukan presensi dari mana saja hari ini.</small>
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
                    <div id="map"></div>

                    <!-- Camera Section -->
                    <div class="text-center mb-3">
                        <video id="camera-preview" autoplay playsinline></video>
                        <canvas id="selfie-canvas" width="640" height="480"></canvas>
                        
                        <div class="mt-2" x-show="cameraActive">
                            <span class="badge bg-success-light text-success fw-semibold" style="font-size: 12px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="align-middle me-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Kamera Aktif
                            </span>
                        </div>
                    </div>
                                 <!-- Today Leave Banner -->
                    @if(isset($todayLeave) && $todayLeave)
                        <div class="alert alert-warning border-0 mb-3" style="background-color: rgba(245, 158, 11, 0.12); color: #b45309; border-left: 4px solid #f59e0b !important;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge {{ $todayLeave->tipe === 'sakit' ? 'bg-danger' : 'bg-primary' }} text-white">
                                    {{ $todayLeave->tipe === 'sakit' ? '🏥 Sakit' : '📝 Izin' }} (Disetujui)
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
                        <span class="text-danger small fw-bold">⚠️ Anda tidak berada di dalam wilayah DUDI. Tombol absen dinonaktifkan.</span>
                    </div>
                </div>
            </div>

            <!-- History panel -->
            <div class="col-md-5 mb-4">
                <div class="card-premium">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold font-heading m-0 text-dark">Riwayat Bulan Ini</h5>
                        <a href="{{ route('laporan.murid_presensi_pdf') }}" class="btn btn-sm btn-outline-primary d-flex align-items-center" style="font-size: 12px; padding: 4px 8px;" aria-label="Unduh Rekap Presensi PDF">
                            <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Unduh PDF
                        </a>
                    </div>
                    
                    <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                        <table class="table table-sm align-middle text-dark dark-text-light mb-0" style="font-size: 13px;">
                            <thead>
                                <tr class="text-muted">
                                    <th>Tanggal</th>
                                    <th>Masuk</th>
                                    <th>Pulang</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $h)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($h->tanggal)->translatedFormat('d M Y') }}</td>
                                        <td>
                                            @if($h->type === 'izin' || $h->type === 'sakit')
                                                <span class="text-muted" style="font-size: 11px;">-</span>
                                            @else
                                                <span class="text-success fw-semibold">{{ $h->jam_masuk ? substr($h->jam_masuk, 0, 5) : '-' }}</span>
                                                @if($h->foto_masuk)
                                                    <div class="mt-1">
                                                        <a href="{{ asset('storage/attendance/' . $h->foto_masuk) }}" target="_blank">
                                                            <img src="{{ asset('storage/attendance/' . $h->foto_masuk) }}" class="rounded border" width="30" height="30" style="object-fit: cover;" title="Foto Check In" alt="Foto Masuk">
                                                        </a>
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if($h->type === 'izin' || $h->type === 'sakit')
                                                <span class="text-muted" style="font-size: 11px;">-</span>
                                            @else
                                                <span class="text-warning fw-semibold">{{ $h->jam_pulang ? substr($h->jam_pulang, 0, 5) : '-' }}</span>
                                                @if($h->foto_pulang)
                                                    <div class="mt-1">
                                                        <a href="{{ asset('storage/attendance/' . $h->foto_pulang) }}" target="_blank">
                                                            <img src="{{ asset('storage/attendance/' . $h->foto_pulang) }}" class="rounded border" width="30" height="30" style="object-fit: cover;" title="Foto Check Out" alt="Foto Pulang">
                                                        </a>
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($h->type === 'izin')
                                                <span class="badge bg-info-light text-info fw-semibold py-1 px-2" style="background-color: rgba(14, 165, 233, 0.12); color: #0284c7; font-size: 11px;" title="{{ $h->keterangan }}">
                                                    📝 Izin
                                                </span>
                                                @if($h->surat_pendukung)
                                                    <div class="mt-1">
                                                        <a href="{{ asset('storage/izin/' . $h->surat_pendukung) }}" target="_blank" class="badge bg-light text-muted border text-decoration-none" style="font-size: 10px;">
                                                            📄 Surat
                                                        </a>
                                                    </div>
                                                @endif
                                            @elseif($h->type === 'sakit')
                                                <span class="badge bg-danger-light text-danger fw-semibold py-1 px-2" style="background-color: rgba(239, 68, 68, 0.12); color: #dc2626; font-size: 11px;" title="{{ $h->keterangan }}">
                                                    🏥 Sakit
                                                </span>
                                                @if($h->surat_pendukung)
                                                    <div class="mt-1">
                                                        <a href="{{ asset('storage/izin/' . $h->surat_pendukung) }}" target="_blank" class="badge bg-light text-muted border text-decoration-none" style="font-size: 10px;">
                                                            📄 Surat
                                                        </a>
                                                    </div>
                                                @endif
                                            @elseif($h->status_masuk === 'tepat_waktu')
                                                <span class="status-badge bg-success-light text-success">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    Hadir
                                                </span>
                                            @else
                                                <span class="status-badge bg-danger-light text-danger">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Terlambat
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="empty-state py-3">
                                                <span class="empty-state-text d-block m-0">Belum ada riwayat presensi bulan ini.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
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
            dudiLat: {{ $placement->dudi->latitude }},
            dudiLng: {{ $placement->dudi->longitude }},
            allowedRadius: {{ $placement->dudi->radius_meter }},
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

            trackLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.watchPosition(
                        (position) => {
                            this.userLat = position.coords.latitude;
                            this.userLng = position.coords.longitude;
                            
                            // Calculate Distance
                            this.distance = this.calculateDistance(this.userLat, this.userLng, this.dudiLat, this.dudiLng);
                            if (this.isWfa) {
                                this.distanceString = 'GPS Terkunci (Mode WFA - Bebas Radius)';
                                this.inRadius = true;
                            } else {
                                this.distanceString = Math.round(this.distance) + ' Meter';
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
                                    .bindPopup('Lokasi Anda Sekarang')
                                    .openPopup();
                            }
                            
                            // Adjust map bounds to show both
                            const group = new L.featureGroup([L.marker([this.dudiLat, this.dudiLng]), this.userMarker]);
                            this.map.fitBounds(group.getBounds().pad(0.2));
                        },
                        (error) => {
                            this.distanceString = 'GPS Error (Buka izin lokasi)';
                            this.inRadius = false;
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                } else {
                    this.distanceString = 'GPS tidak didukung browser';
                    this.inRadius = false;
                }
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
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false })
                    .then((stream) => {
                        this.stream = stream;
                        video.srcObject = stream;
                        this.cameraActive = true;
                    })
                    .catch((err) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Akses Kamera Gagal',
                            text: 'Kamera diblokir atau tidak ditemukan. Izin kamera wajib untuk selfie.',
                            confirmButtonColor: 'var(--accent-primary)'
                        });
                    });
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
            }
        }
    }
</script>
@endif
@endsection
