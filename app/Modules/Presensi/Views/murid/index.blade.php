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
        <div class="card-premium text-center py-5">
            <span class="text-muted d-block">Pemberitahuan: Anda belum ditempatkan di DUDI manapun.</span>
            <small class="text-muted">Akses presensi hanya aktif ketika plotting penempatan Anda telah diselesaikan oleh Admin.</small>
        </div>
    @else
        <div class="row">
            <!-- Camera & Maps panel -->
            <div class="col-md-7 mb-4">
                <div class="card-premium">
                    <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Panel Presensi Mandiri</h5>
                    
                    <!-- DUDI Info -->
                    <div class="alert alert-info border-0 mb-3" style="background-color: rgba(79, 70, 229, 0.1); color: var(--accent-primary);">
                        <strong class="font-heading">DUDI: {{ $placement->dudi->nama }}</strong><br>
                        <small class="d-block mt-1">Koordinat Target: {{ $placement->dudi->latitude }}, {{ $placement->dudi->longitude }}</small>
                        <small class="d-block">Radius Aman: {{ $placement->dudi->radius_meter }} Meter | Jarak Anda: <span class="fw-bold" x-text="distanceString">Mendeteksi...</span></small>
                    </div>

                    <!-- Leaflet map -->
                    <div id="map"></div>

                    <!-- Camera Section -->
                    <div class="text-center mb-3">
                        <video id="camera-preview" autoplay playsinline></video>
                        <canvas id="selfie-canvas" width="640" height="480"></canvas>
                        
                        <div class="mt-2" x-show="cameraActive">
                            <span class="badge bg-success">Kamera Aktif</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="row g-2">
                        <div class="col-6">
                            <button class="btn btn-success w-100 py-3 font-heading fw-bold btn-attendance" 
                                    :disabled="!inRadius || todayHasCheckIn || submitting" 
                                    @click="submitAttendance('checkin')">
                                <span x-text="todayHasCheckIn ? 'Sudah Check In' : 'CHECK IN PAGI'"></span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-warning w-100 py-3 font-heading fw-bold btn-attendance" 
                                    :disabled="!inRadius || !todayHasCheckIn || todayHasCheckOut || submitting" 
                                    @click="submitAttendance('checkout')">
                                <span x-text="todayHasCheckOut ? 'Sudah Check Out' : 'CHECK OUT SORE'"></span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-3 text-center" x-show="!inRadius">
                        <span class="text-danger small fw-bold">⚠️ Anda tidak berada di dalam wilayah DUDI. Tombol absen dinonaktifkan.</span>
                    </div>
                </div>
            </div>

            <!-- History panel -->
            <div class="col-md-5 mb-4">
                <div class="card-premium">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold font-heading m-0 text-dark">Riwayat Bulan Ini</h5>
                        <a href="{{ route('laporan.murid_presensi_pdf') }}" class="btn btn-xs btn-outline-primary d-flex align-items-center" style="font-size: 11px; padding: 4px 8px;">
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
                                        <td>{{ \Carbon\Carbon::parse($h->tanggal)->translatedFormat('d F Y') }}</td>
                                        <td>
                                            <span class="text-success fw-semibold">{{ $h->jam_masuk ? substr($h->jam_masuk, 0, 5) : '-' }}</span>
                                            @if($h->foto_masuk)
                                                <div class="mt-1">
                                                    <a href="{{ asset('storage/attendance/' . $h->foto_masuk) }}" target="_blank">
                                                        <img src="{{ asset('storage/attendance/' . $h->foto_masuk) }}" class="rounded border" width="30" height="30" style="object-fit: cover;" title="Foto Check In">
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-warning fw-semibold">{{ $h->jam_pulang ? substr($h->jam_pulang, 0, 5) : '-' }}</span>
                                            @if($h->foto_pulang)
                                                <div class="mt-1">
                                                    <a href="{{ asset('storage/attendance/' . $h->foto_pulang) }}" target="_blank">
                                                        <img src="{{ asset('storage/attendance/' . $h->foto_pulang) }}" class="rounded border" width="30" height="30" style="object-fit: cover;" title="Foto Check Out">
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="badge {{ $h->status_masuk === 'tepat_waktu' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $h->status_masuk === 'tepat_waktu' ? 'Hadir' : 'Terlambat' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Belum ada riwayat presensi.</td>
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
                            this.distanceString = Math.round(this.distance) + ' Meter';
                            this.inRadius = this.distance <= this.allowedRadius;

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
                        alert('Kamera diblokir atau tidak ditemukan. Izin kamera wajib untuk selfie.');
                    });
            },

            submitAttendance(type) {
                this.submitting = true;
                
                const video = document.getElementById('camera-preview');
                const canvas = document.getElementById('selfie-canvas');
                const context = canvas.getContext('2d');

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
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        this.submitting = false;
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan koneksi server.');
                    this.submitting = false;
                });
            }
        }
    }
</script>
@endif
@endsection
