@extends('layouts.admin')

@section('title', 'Kunjungan Monitoring - PKLku')
@section('page_title', 'Kunjungan Monitoring Pembimbing')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <!-- Logging Form Card -->
        @if(auth()->user()->role === 'guru')
        <div class="col-md-4 mb-4">
            <div class="card-premium" x-data="{
                gettingLocation: false,
                lat: '',
                lng: '',
                getLocation() {
                    this.gettingLocation = true;
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                this.lat = position.coords.latitude;
                                this.lng = position.coords.longitude;
                                this.gettingLocation = false;
                            },
                            (error) => {
                                alert('Gagal mendapatkan lokasi GPS. Pastikan izin lokasi aktif.');
                                this.gettingLocation = false;
                            }
                        );
                    } else {
                        alert('Browser tidak mendukung pendeteksian lokasi GPS.');
                        this.gettingLocation = false;
                    }
                }
            }">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Catat Kunjungan Baru</h5>
                
                <form action="{{ route('kunjungan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="penempatan_pkl_id" class="form-label small fw-semibold">Siswa Bimbingan & DUDI</label>
                        <select name="penempatan_pkl_id" id="penempatan_pkl_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($placements as $p)
                                <option value="{{ $p->id }}">{{ $p->murid->nama }} ({{ $p->dudi->nama }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal" class="form-label small fw-semibold">Tanggal Kunjungan</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi_kunjungan" class="form-label small fw-semibold">Hasil Diskusi / Monitoring</label>
                        <textarea name="deskripsi_kunjungan" id="deskripsi_kunjungan" class="form-control form-control-sm" rows="4" placeholder="Tulis catatan perkembangan murid, disiplin, atau kendala teknis di lapangan..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold d-block">Koordinat Kunjungan (Lokasi Guru)</label>
                        <button type="button" class="btn btn-sm btn-outline-secondary mb-2 d-flex align-items-center" @click="getLocation()">
                            <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            <span x-text="gettingLocation ? 'Mendapatkan Posisi...' : 'Ambil Koordinat GPS'"></span>
                        </button>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="text" name="latitude" x-model="lat" class="form-control form-control-sm" placeholder="Latitude" readonly>
                            </div>
                            <div class="col-6">
                                <input type="text" name="longitude" x-model="lng" class="form-control form-control-sm" placeholder="Longitude" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="foto" class="form-label small fw-semibold">Foto Bukti Kunjungan (Opsional)</label>
                        <input type="file" name="foto" id="foto" class="form-control form-control-sm" accept="image/*">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 font-heading">Simpan Catatan Kunjungan</button>
                </form>
            </div>
        </div>
        @endif

        <!-- Visitations History Card -->
        <div class="{{ auth()->user()->role === 'guru' ? 'col-md-8' : 'col-md-12' }} mb-4">
            <div class="card-premium p-0 overflow-hidden">
                <div class="p-3 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                    <h6 class="fw-bold m-0 text-dark dark-text-light">Riwayat Kunjungan Monitoring</h6>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-primary);">
                        <thead class="table-light">
                            <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                                <th class="ps-4" style="width: 110px;">Tanggal</th>
                                <th>Siswa Bimbingan</th>
                                <th>DUDI & Guru Pembimbing</th>
                                <th>Catatan Monitoring</th>
                                <th class="text-center pe-4" style="width: 100px;">Foto Bukti</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 13px;">
                            @forelse($kunjungans as $k)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $k->penempatanPkl->murid->nama }}</div>
                                        <small class="text-muted">{{ $k->penempatanPkl->murid->kelas->nama }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary">{{ $k->penempatanPkl->dudi->nama }}</div>
                                        <small class="text-muted">Oleh: {{ $k->penempatanPkl->guru->nama }}</small>
                                    </td>
                                    <td>{{ Str::limit($k->deskripsi_kunjungan, 120) }}</td>
                                    <td class="text-center pe-4">
                                        @if($k->foto_kunjungan)
                                            <a href="{{ asset('storage/kunjungan/' . $k->foto_kunjungan) }}" target="_blank">
                                                <img src="{{ asset('storage/kunjungan/' . $k->foto_kunjungan) }}" class="rounded border" width="40" height="40" style="object-fit: cover;">
                                            </a>
                                        @else
                                            <span class="text-muted small">Tidak ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat catatan kunjungan monitoring.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($kunjungans->hasPages())
                <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
                    {{ $kunjungans->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
