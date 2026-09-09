@extends('layouts.admin')

@section('title', 'Kunjungan Pembimbing - PKLku')
@section('page_title', 'Kunjungan Pembimbing')

@section('content')
<div class="container-fluid p-0">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <strong>Berhasil!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <div class="row">
        <!-- Logging Form Card -->
        @if(auth()->user()->role === 'guru')
        <div class="col-md-4 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark">Catat Kunjungan Baru</h5>
                
                <form action="{{ route('kunjungan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="penempatan_pkl_id" class="form-label small fw-semibold">Mitra DUDI Bimbingan</label>
                        <select name="penempatan_pkl_id" id="penempatan_pkl_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih DUDI --</option>
                            @foreach($dudiPlacements as $p)
                                <option value="{{ $p->id }}">{{ $p->dudi->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="jenis_kunjungan" class="form-label small fw-semibold">Jenis Kunjungan</label>
                        <select name="jenis_kunjungan" id="jenis_kunjungan" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Jenis Kunjungan --</option>
                            <option value="Penjajakan Kerja Sama">Penjajakan Kerja Sama</option>
                            <option value="Penyerahan Murid">Penyerahan Murid</option>
                            <option value="Monitoring Berkala">Monitoring Berkala</option>
                            <option value="Penarikan PKL">Penarikan PKL</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal" class="form-label small fw-semibold">Tanggal Kunjungan</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi_kunjungan" class="form-label small fw-semibold">Catatan Kunjungan</label>
                        <textarea name="deskripsi_kunjungan" id="deskripsi_kunjungan" class="form-control form-control-sm" rows="4" placeholder="Tulis catatan kunjungan pembimbing, agenda diskusi, atau kendala lapangan..." required></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="foto" class="form-label small fw-semibold">Foto Bukti Kunjungan (Wajib)</label>
                        <input type="file" name="foto" id="foto" class="form-control form-control-sm" accept="image/*" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 font-heading">Simpan Catatan Kunjungan</button>
                </form>
            </div>
        </div>
        @endif

        <!-- Visitations History Card -->
        <div class="{{ auth()->user()->role === 'guru' ? 'col-md-8' : 'col-md-12' }} mb-4">
            @php
                $getJenisKunjunganBadge = function ($jenis) {
                    return match($jenis) {
                        'Penjajakan Kerja Sama' => [
                            'class'  => 'bg-purple-light text-purple',
                            'border' => 'rgba(147, 51, 234, 0.25)',
                            'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
                        ],
                        'Penyerahan Murid' => [
                            'class'  => 'bg-info-light text-info',
                            'border' => 'rgba(14, 165, 233, 0.25)',
                            'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                        ],
                        'Monitoring Berkala' => [
                            'class'  => 'bg-success-light text-success',
                            'border' => 'rgba(16, 185, 129, 0.25)',
                            'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                        ],
                        'Penarikan PKL' => [
                            'class'  => 'bg-orange-light text-orange',
                            'border' => 'rgba(249, 115, 22, 0.25)',
                            'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>',
                        ],
                        default => [
                            'class'  => 'bg-secondary-light text-secondary',
                            'border' => 'rgba(100, 116, 139, 0.25)',
                            'icon'   => '',
                        ],
                    };
                };
            @endphp
            <div class="card-premium p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-bottom-color: var(--border-color) !important;">
                    <h6 class="fw-bold m-0 text-dark">Riwayat Kunjungan Pembimbing</h6>
                    <a href="{{ route('kunjungan.export_pdf') }}" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 font-heading fw-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export PDF
                    </a>
                </div>

                <!-- Desktop Table View (md and up) -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); min-width: 760px; font-size: 13px;">
                        <thead class="table-light">
                            <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                                <th class="ps-4" style="width: 110px;">Tanggal</th>
                                <th>Mitra DUDI / Jenis</th>
                                <th>Guru Pembimbing</th>
                                <th>Catatan Kunjungan</th>
                                <th class="text-center" style="width: 100px;">Foto Bukti</th>
                                <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kunjungans as $k)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="fw-semibold text-primary mb-1">{{ $k->penempatanPkl?->dudi?->nama ?? 'DUDI Terhapus' }}</div>
                                        @php $badge = $getJenisKunjunganBadge($k->jenis_kunjungan); @endphp
                                        <span class="badge rounded-pill {{ $badge['class'] }} fw-semibold d-inline-flex align-items-center" style="font-size: 11px; padding: 3px 8px; border: 1px solid {{ $badge['border'] }};">
                                            {!! $badge['icon'] !!}{{ $k->jenis_kunjungan ?? 'Monitoring Berkala' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $k->penempatanPkl?->guru?->nama ?? 'Guru Terhapus' }}</div>
                                    </td>
                                    <td>{{ Str::limit($k->deskripsi_kunjungan, 120) }}</td>
                                    <td class="text-center">
                                        @if($k->foto_kunjungan)
                                            <a href="{{ asset('storage/kunjungan/' . $k->foto_kunjungan) }}" target="_blank" aria-label="Foto Bukti Kunjungan">
                                                <img src="{{ asset('storage/kunjungan/' . $k->foto_kunjungan) }}" class="rounded border" width="40" height="40" style="object-fit: cover;" alt="Bukti Kunjungan">
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-action btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#sppdModal_{{ $k->id }}" title="Cetak Laporan SPPD" aria-label="Cetak SPPD {{ $k->penempatanPkl?->dudi?->nama }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-action btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal_{{ $k->id }}" title="Edit Kunjungan" aria-label="Edit Catatan Kunjungan {{ $k->penempatanPkl?->dudi?->nama }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <form action="{{ route('kunjungan.destroy', $k->id) }}" method="POST" id="deleteKunjunganForm_{{ $k->id }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-action btn-sm btn-outline-danger" title="Hapus Kunjungan" aria-label="Hapus Kunjungan {{ $k->penempatanPkl?->dudi?->nama }}" onclick="window.confirmDelete('deleteKunjunganForm_{{ $k->id }}', 'catatan kunjungan {{ addslashes($k->penempatanPkl?->dudi?->nama ?? '') }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="empty-state py-4">
                                            <div class="empty-state-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                            </div>
                                            <h6 class="empty-state-title">Belum Ada Catatan Kunjungan</h6>
                                            <p class="empty-state-text">Gunakan form di sebelah kiri untuk mencatat kunjungan pembimbing ke mitra DUDI.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List View (Visible on smartphone < md) -->
                <div class="d-md-none p-3">
                    @forelse($kunjungans as $k)
                        <div class="card p-3 mb-3 border rounded shadow-xs" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                            <!-- Top: Tanggal & Jenis -->
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                <div class="d-flex align-items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="fw-bold font-heading text-dark" style="font-size: 13px;">
                                        {{ \Carbon\Carbon::parse($k->tanggal)->translatedFormat('d M Y') }}
                                    </span>
                                </div>
                                @php $badge = $getJenisKunjunganBadge($k->jenis_kunjungan); @endphp
                                <span class="badge rounded-pill {{ $badge['class'] }} fw-semibold d-inline-flex align-items-center" style="font-size: 11px; padding: 3px 8px; border: 1px solid {{ $badge['border'] }};">
                                    {!! $badge['icon'] !!}{{ $k->jenis_kunjungan ?? 'Monitoring' }}
                                </span>
                            </div>

                            <!-- DUDI & Guru -->
                            <div class="mb-2">
                                <div class="fw-bold text-dark font-heading" style="font-size: 13px;">{{ $k->penempatanPkl?->dudi?->nama ?? 'DUDI Terhapus' }}</div>
                                <div class="text-muted small">Pembimbing: <span class="fw-semibold text-secondary">{{ $k->penempatanPkl?->guru?->nama ?? 'Guru Terhapus' }}</span></div>
                            </div>

                            <!-- Catatan -->
                            <div class="p-2.5 rounded bg-light border mb-2" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important; font-size: 13px; line-height: 1.5; color: var(--text-primary); white-space: pre-line; word-break: break-word;">
                                {{ $k->deskripsi_kunjungan }}
                            </div>

                            <!-- Footer: Bukti & Tombol Aksi -->
                            <div class="pt-2 border-top d-flex flex-wrap align-items-center justify-content-between gap-2" style="border-top-color: var(--border-color) !important;">
                                <div>
                                    @if($k->foto_kunjungan)
                                        <a href="{{ asset('storage/kunjungan/' . $k->foto_kunjungan) }}" target="_blank" class="badge bg-light text-dark border d-flex align-items-center gap-1.5 text-decoration-none py-1.5 px-2.5" style="border-color: var(--border-color) !important;">
                                            <img src="{{ asset('storage/kunjungan/' . $k->foto_kunjungan) }}" class="rounded" width="18" height="18" style="object-fit: cover;" alt="Thumbnail">
                                            <span class="fw-semibold" style="font-size: 11px;">Lihat Foto Bukti</span>
                                        </a>
                                    @else
                                        <span class="text-muted small" style="font-size: 11px;">Tanpa foto</span>
                                    @endif
                                </div>

                                <div class="d-flex gap-1.5 align-items-center ms-auto">
                                    <button type="button" class="btn btn-sm btn-outline-danger font-heading d-flex align-items-center gap-1 px-2 py-1" data-bs-toggle="modal" data-bs-target="#sppdModal_{{ $k->id }}" style="font-size: 12px;" title="Cetak SPPD">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>SPPD</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning font-heading d-flex align-items-center gap-1 px-2.5 py-1" data-bs-toggle="modal" data-bs-target="#editModal_{{ $k->id }}" style="font-size: 12px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Edit</span>
                                    </button>
                                    <form action="{{ route('kunjungan.destroy', $k->id) }}" method="POST" id="deleteKunjunganFormMob_{{ $k->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger px-2.5 py-1" title="Hapus Kunjungan" onclick="window.confirmDelete('deleteKunjunganFormMob_{{ $k->id }}', 'catatan kunjungan {{ addslashes($k->penempatanPkl?->dudi?->nama ?? '') }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-4 text-center">
                            <h6 class="empty-state-title">Belum Ada Catatan Kunjungan</h6>
                            <p class="empty-state-text">Gunakan form di atas untuk mencatat kunjungan pembimbing ke mitra DUDI.</p>
                        </div>
                    @endforelse
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

<!-- ================= MODALS SECTION (Outside Card/Container to prevent backdrop trap) ================= -->

<!-- Edit Modals -->
@foreach($kunjungans as $k)
    <div class="modal fade text-start" id="editModal_{{ $k->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px;">
                <div class="modal-header border-bottom py-3 px-4" style="border-bottom-color: var(--border-color) !important;">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; background-color: rgba(245, 158, 11, 0.1); color: var(--warning);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="modal-title font-heading fw-bold m-0 text-dark" style="font-size: 15px;">Edit Catatan Kunjungan</h5>
                            <small class="text-muted d-block" style="font-size: 12px;">Perbarui data atau foto bukti kunjungan pembimbing.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('kunjungan.update', $k->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4 text-start">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Mitra DUDI Bimbingan</label>
                            <select name="penempatan_pkl_id" class="form-select form-select-sm" required>
                                @foreach($dudiPlacements as $p)
                                    <option value="{{ $p->id }}" {{ $p->id == $k->penempatan_pkl_id ? 'selected' : '' }}>
                                        {{ $p->dudi->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary mb-1">Tanggal Kunjungan</label>
                                <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $k->tanggal }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary mb-1">Jenis Kunjungan</label>
                                <select name="jenis_kunjungan" class="form-select form-select-sm" required>
                                    <option value="Penjajakan Kerja Sama" {{ $k->jenis_kunjungan === 'Penjajakan Kerja Sama' ? 'selected' : '' }}>Penjajakan Kerja Sama</option>
                                    <option value="Penyerahan Murid" {{ $k->jenis_kunjungan === 'Penyerahan Murid' ? 'selected' : '' }}>Penyerahan Murid</option>
                                    <option value="Monitoring Berkala" {{ $k->jenis_kunjungan === 'Monitoring Berkala' ? 'selected' : '' }}>Monitoring Berkala</option>
                                    <option value="Penarikan PKL" {{ $k->jenis_kunjungan === 'Penarikan PKL' ? 'selected' : '' }}>Penarikan PKL</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Catatan Kunjungan</label>
                            <textarea name="deskripsi_kunjungan" class="form-control form-control-sm" rows="4" placeholder="Tulis rincian catatan kunjungan..." required>{{ $k->deskripsi_kunjungan }}</textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-secondary mb-1">Foto Bukti Kunjungan (Opsional ubah)</label>
                            <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                            @if($k->foto_kunjungan)
                                <div class="mt-2.5 p-2 rounded border d-flex align-items-center gap-2.5" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                    <img src="{{ asset('storage/kunjungan/' . $k->foto_kunjungan) }}" class="rounded border" width="48" height="48" style="object-fit: cover;" alt="Foto Kunjungan">
                                    <div>
                                        <span class="d-block fw-semibold text-dark" style="font-size: 12px;">Foto saat ini terlampir</span>
                                        <small class="text-muted" style="font-size: 11px;">Pilih file baru di atas jika ingin mengganti foto bukti ini.</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer border-top py-3 px-4" style="border-top-color: var(--border-color) !important;">
                        <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3 py-1.5 font-heading fw-semibold" data-loading-text="Menyimpan...">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- SPPD Modals (Redesigned Layout) -->
@foreach($kunjungans as $k)
    <div class="modal fade text-start" id="sppdModal_{{ $k->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px;">
                <div class="modal-header border-bottom py-3 px-4" style="border-bottom-color: var(--border-color) !important;">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; background-color: rgba(225, 29, 72, 0.1); color: var(--danger);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="modal-title font-heading fw-bold m-0 text-dark" style="font-size: 15px;">Cetak Laporan Perjalanan Dinas (Format SPPD)</h5>
                            <small class="text-muted d-block" style="font-size: 12px;">Lengkapi atau sesuaikan rincian tugas untuk mengunduh lembar SPPD resmi.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('kunjungan.export_sppd', $k->id) }}" method="POST" target="_blank">
                    @csrf
                    <div class="modal-body p-4 text-start">
                        <!-- Bagian 1: Data Pegawai / Guru Pelaksana -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-1 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                <span class="badge bg-primary-light text-primary fw-semibold" style="font-size: 10.5px; padding: 2.5px 8px;">Bagian 1</span>
                                <span class="fw-bold font-heading text-dark" style="font-size: 13px;">Data Pegawai / Guru Pelaksana Tugas</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary mb-1">Nama Pegawai / Guru (Pelaksana Tugas)</label>
                                    <input type="text" name="nama" class="form-control form-control-sm sppd-nama" value="{{ old('nama', $k->penempatanPkl?->guru?->nama ?? auth()->user()->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary mb-1">NIP Pegawai / Guru</label>
                                    <input type="text" name="nip" class="form-control form-control-sm sppd-nip" value="{{ old('nip', $k->penempatanPkl?->guru?->nip ?? '-') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary mb-1">Pangkat / Golongan</label>
                                    <input type="text" name="pangkat_golongan" class="form-control form-control-sm sppd-pangkat" placeholder="Contoh: Penata Muda / III/a atau Pembina / IV/a" value="{{ old('pangkat_golongan', '') }}">
                                    <div class="text-muted mt-1" style="font-size: 11px;">*Tersimpan otomatis untuk unduhan berikutnya</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary mb-1">Lama Perjalanan Dinas</label>
                                    <input type="text" name="lama_perjalanan" class="form-control form-control-sm" value="1 (satu) Hari" required>
                                    <div class="text-muted mt-1" style="font-size: 11px;">Contoh: 1 (satu) Hari / 2 (dua) Hari</div>
                                </div>
                            </div>
                        </div>

                        <!-- Bagian 2: Tempat & Uraian Kegiatan -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-1 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                <span class="badge bg-primary-light text-primary fw-semibold" style="font-size: 10.5px; padding: 2.5px 8px;">Bagian 2</span>
                                <span class="fw-bold font-heading text-dark" style="font-size: 13px;">Tempat Tujuan & Uraian Laporan Kunjungan</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-secondary mb-1">Tempat / Mitra DUDI Tujuan</label>
                                    @php
                                        $defaultTujuan = ($k->penempatanPkl?->dudi?->nama ?? 'Mitra DUDI');
                                        if ($k->penempatanPkl?->dudi?->alamat) {
                                            $defaultTujuan .= ', ' . $k->penempatanPkl->dudi->alamat;
                                        }
                                    @endphp
                                    <textarea name="tempat_tujuan" class="form-control form-control-sm" rows="2" placeholder="Nama mitra DUDI dan alamat lengkap..." required>{{ $defaultTujuan }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary mb-1">Kota Penandatanganan</label>
                                    <input type="text" name="kota" class="form-control form-control-sm sppd-kota" value="{{ $branding['kota_sekolah'] ?? 'Pati' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary mb-1">Tanggal Pelaksanaan / Laporan</label>
                                    <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $k->tanggal }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-secondary mb-1">Laporan Pelaksanaan Kegiatan</label>
                                    <textarea name="laporan_kegiatan" class="form-control form-control-sm" rows="3" placeholder="Uraian hasil kunjungan atau agenda pembimbingan..." required>{{ $k->deskripsi_kunjungan }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Bagian 3: Lampiran Foto Switch -->
                        <div class="p-3 rounded border" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded p-2 bg-white border d-flex align-items-center justify-content-center text-muted flex-shrink-0" style="width: 36px; height: 36px; border-color: var(--border-color) !important;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <label class="form-check-label fw-bold font-heading text-dark d-block" for="foto_sppd_{{ $k->id }}" style="font-size: 13px; cursor: pointer;">
                                            Sertakan Lembar Dokumentasi Foto Kunjungan
                                        </label>
                                        <small class="text-muted d-block" style="font-size: 11.5px;">Menyertakan lembar dokumentasi foto kunjungan di halaman kedua (Lampiran SPPD)</small>
                                        @if(!$k->foto_kunjungan)
                                            <small class="text-danger d-block mt-0.5 fw-semibold" style="font-size: 11px;">* Kunjungan ini belum memiliki foto bukti yang diunggah.</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-check form-switch m-0 flex-shrink-0">
                                    <input class="form-check-input" type="checkbox" name="lampirkan_foto" value="1" id="foto_sppd_{{ $k->id }}" style="width: 2.5em; height: 1.35em; cursor: pointer;" {{ $k->foto_kunjungan ? 'checked' : 'disabled' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-3 px-4" style="border-top-color: var(--border-color) !important;">
                        <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-danger d-flex align-items-center gap-1.5 font-heading fw-semibold px-3 py-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Unduh PDF SPPD</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const savedPangkat = localStorage.getItem('sppd_saved_pangkat');
        if (savedPangkat) {
            document.querySelectorAll('.sppd-pangkat').forEach(function(el) {
                if (!el.value) {
                    el.value = savedPangkat;
                }
            });
        }

        document.querySelectorAll('.sppd-pangkat').forEach(function(el) {
            el.addEventListener('input', function() {
                if (this.value.trim()) {
                    localStorage.setItem('sppd_saved_pangkat', this.value.trim());
                    document.querySelectorAll('.sppd-pangkat').forEach(other => {
                        if (other !== el) other.value = this.value.trim();
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection
