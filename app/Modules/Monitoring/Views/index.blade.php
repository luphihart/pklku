@extends('layouts.admin')

@section('title', 'Peta Monitoring & Notifikasi - PKLku')
@section('page_title', 'Pusat Pemantauan & Notifikasi')

@section('styles')
<style>
    #monitoringMap {
        height: 400px;
        border-radius: 0.5rem;
        border: 1px solid var(--border-color);
        z-index: 1;
    }
    .feed-container {
        max-height: 400px;
        overflow-y: auto;
    }
    .feed-item {
        border-left: 2px solid var(--accent-primary);
        padding-left: 1rem;
        position: relative;
        margin-bottom: 1rem;
    }
    .feed-item:last-child {
        margin-bottom: 0;
    }
    .feed-marker {
        position: absolute;
        left: -5px;
        top: 4px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: var(--accent-primary);
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <!-- Leaflet Map Column -->
        <div class="col-lg-8 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Peta Lokasi PKL Aktif</h5>
                <div id="monitoringMap"></div>
            </div>
        </div>

        <!-- DUDI List Column -->
        <div class="col-lg-4 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">
                    {{ auth()->user()->role === 'guru' ? 'Daftar DUDI & Bimbingan' : 'Daftar Seluruh DUDI' }}
                </h5>
                
                <div class="feed-container pe-2" style="max-height: 400px; overflow-y: auto;">
                    @forelse($dudiList as $dudiId => $dudiItem)
                        <div class="p-2 mb-3 border rounded bg-light" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="fw-bold text-primary font-heading" style="font-size: 13px;">{{ $dudiItem['dudi']->nama }}</span>
                                <span class="badge bg-secondary" style="font-size: 10px;">{{ count($dudiItem['placements']) }} Siswa</span>
                            </div>
                            <small class="text-muted d-block mt-1 mb-2" style="font-size: 11px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="align-middle me-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>{{ Str::limit($dudiItem['dudi']->alamat, 35) }}
                            </small>
                            <ul class="ps-3 mb-0" style="font-size: 11px; color: var(--text-primary);">
                                @foreach($dudiItem['placements'] as $placement)
                                    <li class="mb-1">
                                        <strong>{{ $placement->murid->nama }}</strong> ({{ $placement->murid->kelas->nama }})
                                        @if(auth()->user()->role === 'admin')
                                            <br><span class="text-muted">Pembimbing: {{ $placement->guru->nama }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted small">
                            Belum ada mitra DUDI aktif saat ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Fix Leaflet broken default marker icons
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        });

        // Group placements by DUDI to prevent duplicate markers
        const placements = @json($placements);
        const dudiGroups = {};

        placements.forEach(p => {
            if (!dudiGroups[p.dudi_id]) {
                dudiGroups[p.dudi_id] = {
                    name: p.dudi.nama,
                    lat: parseFloat(p.dudi.latitude),
                    lng: parseFloat(p.dudi.longitude),
                    students: []
                };
            }
            dudiGroups[p.dudi_id].students.push(p);
        });

        // Initialize map centered at first DUDI or average center
        let mapCenter = [-6.200000, 106.816666]; // Default Jakarta
        const dudiKeys = Object.keys(dudiGroups);
        
        if (dudiKeys.length > 0) {
            mapCenter = [dudiGroups[dudiKeys[0]].lat, dudiGroups[dudiKeys[0]].lng];
        }

        const map = L.map('monitoringMap').setView(mapCenter, 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const markers = [];

        // Draw DUDI markers
        Object.values(dudiGroups).forEach(dudi => {
            let popupContent = `<strong>${dudi.name}</strong><br><hr class="my-1">Siswa Terplotting:<br><ul class="ps-3 mb-0" style="font-size: 11px;">`;
            dudi.students.forEach(studentPlacement => {
                popupContent += `<li><strong>${studentPlacement.murid.nama}</strong> (${studentPlacement.murid.kelas.nama})<br><span class="text-muted">Pembimbing: ${studentPlacement.guru.nama}</span></li>`;
            });
            popupContent += '</ul>';

            const marker = L.marker([dudi.lat, dudi.lng]).addTo(map)
                .bindPopup(popupContent);
            
            markers.push(marker);
        });

        // Adjust bounds
        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.15));
        }
    });
</script>
@endsection
