@extends('layouts.admin')

@section('title', 'Verifikasi Jurnal - PKLku')
@section('page_title', 'Verifikasi Jurnal Bimbingan')

@section('content')
<div class="container-fluid p-0">
    <!-- Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">Verifikasi Jurnal Harian Siswa</h5>
    </div>

    <!-- Filter & Search Card -->
    <div class="card-premium mb-4">
        <!-- Quick Status Pills (Wrap naturally so none are cut off on mobile) -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
            <div class="d-flex flex-wrap align-items-center gap-1.5">
                <span class="small fw-bold text-muted font-heading me-1">Status:</span>
                <a href="{{ route('jurnal.index', request()->except('status')) }}" class="btn btn-xs font-heading {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius: 9999px; padding: 4px 10px;">
                    Semua
                </a>
                <a href="{{ route('jurnal.index', array_merge(request()->query(), ['status' => 'pending'])) }}" class="btn btn-xs font-heading {{ request('status') === 'pending' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius: 9999px; padding: 4px 10px;">
                    Menunggu (Pending)
                </a>
                <a href="{{ route('jurnal.index', array_merge(request()->query(), ['status' => 'disetujui'])) }}" class="btn btn-xs font-heading {{ request('status') === 'disetujui' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius: 9999px; padding: 4px 10px;">
                    Disetujui
                </a>
                <a href="{{ route('jurnal.index', array_merge(request()->query(), ['status' => 'revisi'])) }}" class="btn btn-xs font-heading {{ request('status') === 'revisi' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius: 9999px; padding: 4px 10px;">
                    Revisi
                </a>
                <a href="{{ route('jurnal.index', array_merge(request()->query(), ['status' => 'ditolak'])) }}" class="btn btn-xs font-heading {{ request('status') === 'ditolak' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius: 9999px; padding: 4px 10px;">
                    Ditolak
                </a>
            </div>
            <span class="badge bg-primary-light text-primary font-heading px-2.5 py-1" style="font-size: 11px;">
                Total: {{ $journals->total() }} Jurnal
            </span>
        </div>

        <!-- Filter Form (Organized grid) -->
        <form action="{{ route('jurnal.index') }}" method="GET" class="row g-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold mb-1">Cari Nama Siswa / NIS / DUDI / Laporan</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-0" placeholder="Ketik nama siswa, NIS, atau aktivitas..." value="{{ request('search') ?? request('nama') }}">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Pilih Kelas</label>
                <select name="kelas_id" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" {{ (string)request('kelas_id') === (string)$kelas->id ? 'selected' : '' }}>
                            {{ $kelas->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="{{ request('tanggal_mulai') }}">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control form-control-sm" value="{{ request('tanggal_selesai') }}">
            </div>

            <div class="col-6 col-md-1 d-flex align-items-end gap-1">
                <button type="submit" class="btn btn-sm btn-primary font-heading d-flex align-items-center justify-content-center flex-fill" title="Filter Jurnal" style="height: 31px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </button>
                @if(request()->hasAny(['search', 'nama', 'kelas_id', 'tanggal_mulai', 'tanggal_selesai', 'status']) && (request('search') || request('nama') || request('kelas_id') || request('tanggal_mulai') || request('tanggal_selesai') || request('status')))
                    <a href="{{ route('jurnal.index') }}" class="btn btn-sm btn-outline-secondary font-heading d-flex align-items-center justify-content-center" title="Reset Semua Filter" style="height: 31px; width: 34px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Desktop Table View (lg and up) -->
    <div class="card-premium p-0 overflow-hidden d-none d-lg-block mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 950px; color: var(--text-primary); font-size: 13px;">
                <thead class="table-light">
                    <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                        <th class="ps-4" style="width: 110px;">Tanggal</th>
                        <th style="width: 200px;">Siswa & Kelas</th>
                        <th style="width: 180px;">DUDI Tempat PKL</th>
                        <th style="min-width: 280px;">Isi Laporan Aktivitas</th>
                        <th class="text-center" style="width: 90px;">Foto Bukti</th>
                        <th class="text-center" style="width: 120px;">Status</th>
                        <th class="text-center pe-4" style="width: 120px;">Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $j)
                        <tr>
                            <td class="ps-4 fw-semibold">
                                {{ $j->tanggal ? \Carbon\Carbon::parse($j->tanggal)->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                <div class="fw-bold text-dark dark-text-light">{{ $j->penempatanPkl?->murid?->nama ?? 'Siswa Terhapus' }}</div>
                                <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 10px;">NIS: {{ $j->penempatanPkl?->murid?->nis ?? '-' }}</span>
                                    <span class="badge bg-primary-light text-primary fw-semibold" style="font-size: 10px;">{{ $j->penempatanPkl?->murid?->kelas?->nama ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark dark-text-light">{{ $j->penempatanPkl?->dudi?->nama ?? 'DUDI Terhapus' }}</div>
                                <small class="text-muted" style="font-size: 11px;">{{ $j->penempatanPkl?->dudi?->alamat ? Str::limit($j->penempatanPkl->dudi->alamat, 35) : '-' }}</small>
                            </td>
                            <td>
                                <div class="text-break" style="line-height: 1.5; word-break: break-word; white-space: normal;">{{ $j->deskripsi_aktivitas }}</div>
                                @if($j->catatan_verifikasi)
                                    <small class="text-danger d-block mt-1"><strong>Komentar:</strong> {{ $j->catatan_verifikasi }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($j->foto_kegiatan)
                                    @php
                                        $isPdf = Str::endsWith(strtolower($j->foto_kegiatan), '.pdf');
                                    @endphp
                                    <a href="{{ asset('storage/jurnal/' . $j->foto_kegiatan) }}" target="_blank" aria-label="Lihat lampiran bukti jurnal">
                                        @if($isPdf)
                                            <span class="badge bg-danger-light text-danger p-1 font-heading" style="font-size: 11px;">PDF</span>
                                        @else
                                            <img src="{{ asset('storage/jurnal/' . $j->foto_kegiatan) }}" class="rounded border" width="36" height="36" style="object-fit: cover;" alt="Bukti Foto">
                                        @endif
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($j->status_verifikasi === 'disetujui')
                                    <span class="status-badge bg-success-light text-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Disetujui
                                    </span>
                                @elseif($j->status_verifikasi === 'revisi')
                                    <span class="status-badge bg-warning-light text-warning">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Revisi
                                    </span>
                                @elseif($j->status_verifikasi === 'ditolak')
                                    <span class="status-badge bg-danger-light text-danger">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Ditolak
                                    </span>
                                @else
                                    <span class="status-badge bg-secondary-light text-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center align-items-center">
                                    @if(auth()->user()->role === 'guru' || auth()->user()->role === 'admin')
                                        @if($j->status_verifikasi === 'pending')
                                            <button class="btn btn-sm btn-outline-primary btn-action" data-bs-toggle="modal" data-bs-target="#reviewModal_{{ $j->id }}" title="Tinjau Jurnal" aria-label="Tinjau Jurnal {{ $j->penempatanPkl?->murid?->nama }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        @else
                                            <!-- Tombol Batalkan Verifikasi (Kembalikan ke Pending) -->
                                            <form action="{{ route('jurnal.cancel_verify', $j->id) }}" method="POST" id="cancelVerifyForm_{{ $j->id }}" style="display: inline-block;">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-outline-warning btn-action" title="Batalkan Verifikasi (Kembalikan ke Status Pending)" aria-label="Batalkan Verifikasi Jurnal {{ $j->penempatanPkl?->murid?->nama }}" onclick="confirmCancelVerify({{ $j->id }}, '{{ addslashes($j->penempatanPkl?->murid?->nama ?? 'siswa') }}', 'cancelVerifyForm_')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                                    </svg>
                                                </button>
                                            </form>

                                            <!-- Tombol Ubah Keputusan Verifikasi -->
                                            <button class="btn btn-sm btn-outline-secondary btn-action" data-bs-toggle="modal" data-bs-target="#reviewModal_{{ $j->id }}" title="Ubah Keputusan / Catatan" aria-label="Ubah Keputusan Jurnal {{ $j->penempatanPkl?->murid?->nama }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        @endif

                                        <form action="{{ route('jurnal.destroy', $j->id) }}" method="POST" id="deleteJurnalForm_{{ $j->id }}" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-action" title="Hapus Jurnal" aria-label="Hapus Jurnal {{ $j->penempatanPkl?->murid?->nama }}" onclick="window.confirmDelete('deleteJurnalForm_{{ $j->id }}', 'jurnal kegiatan {{ addslashes($j->penempatanPkl?->murid?->nama ?? 'siswa') }}')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="empty-state py-4">
                                    <div class="empty-state-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h6 class="empty-state-title">Tidak Ada Jurnal</h6>
                                    <p class="empty-state-text">Tidak ditemukan data jurnal siswa untuk kriteria filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card Feed View (Visible on smartphone / tablet < lg) -->
    <div class="d-lg-none mb-4">
        @forelse($journals as $j)
            <div class="card-premium mb-3 p-3 position-relative" style="background-color: var(--bg-card); border-left: 4px solid {{ $j->status_verifikasi === 'disetujui' ? '#10b981' : ($j->status_verifikasi === 'revisi' ? '#f59e0b' : ($j->status_verifikasi === 'ditolak' ? '#ef4444' : '#64748b')) }} !important;">
                <!-- Header: Siswa & Status -->
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                    <div>
                        <div class="fw-bold text-dark font-heading" style="font-size: 14px;">
                            {{ $j->penempatanPkl?->murid?->nama ?? 'Siswa Terhapus' }}
                        </div>
                        <div class="d-flex align-items-center gap-1.5 mt-1">
                            <span class="badge bg-light text-dark border font-monospace" style="font-size: 10px;">NIS: {{ $j->penempatanPkl?->murid?->nis ?? '-' }}</span>
                            <span class="badge bg-primary-light text-primary fw-semibold" style="font-size: 10.5px;">{{ $j->penempatanPkl?->murid?->kelas?->nama ?? '-' }}</span>
                        </div>
                    </div>
                    <div>
                        @if($j->status_verifikasi === 'disetujui')
                            <span class="status-badge bg-success-light text-success" style="font-size: 11px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Disetujui
                            </span>
                        @elseif($j->status_verifikasi === 'revisi')
                            <span class="status-badge bg-warning-light text-warning" style="font-size: 11px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                Revisi
                            </span>
                        @elseif($j->status_verifikasi === 'ditolak')
                            <span class="status-badge bg-danger-light text-danger" style="font-size: 11px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                Ditolak
                            </span>
                        @else
                            <span class="status-badge bg-secondary-light text-secondary" style="font-size: 11px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Pending
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Info Tanggal & DUDI -->
                <div class="d-flex flex-wrap justify-content-between align-items-center text-muted mb-2 gap-1" style="font-size: 12px;">
                    <div class="d-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <strong class="text-dark">{{ $j->tanggal ? \Carbon\Carbon::parse($j->tanggal)->translatedFormat('l, d M Y') : '-' }}</strong>
                    </div>
                    <div class="d-flex align-items-center gap-1 text-truncate" style="max-width: 180px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted flex-shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="text-secondary fw-semibold text-truncate">{{ $j->penempatanPkl?->dudi?->nama ?? 'DUDI Terhapus' }}</span>
                    </div>
                </div>

                <!-- Isi Laporan Aktivitas -->
                <div class="p-2.5 rounded bg-light border mb-2" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important; font-size: 13px; line-height: 1.6; color: var(--text-primary); white-space: pre-line; word-break: break-word;">
                    {{ $j->deskripsi_aktivitas }}
                </div>

                <!-- Catatan / Komentar Guru jika ada -->
                @if($j->catatan_verifikasi)
                    <div class="p-2 rounded mb-2 border" style="background-color: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.2) !important; font-size: 12px; line-height: 1.4;">
                        <strong class="text-danger">Catatan Guru:</strong>
                        <div class="text-dark mt-0.5" style="white-space: pre-line;">{{ $j->catatan_verifikasi }}</div>
                    </div>
                @endif

                <!-- Footer: Bukti & Tombol Aksi -->
                <div class="pt-2.5 border-top d-flex flex-column gap-2" style="border-top-color: var(--border-color) !important;">
                    <!-- Row 1: Bukti Kegiatan Info / Link -->
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small" style="font-size: 11.5px;">Bukti Lampiran:</span>
                        @if($j->foto_kegiatan)
                            @php
                                $isPdf = Str::endsWith(strtolower($j->foto_kegiatan), '.pdf');
                            @endphp
                            <a href="{{ asset('storage/jurnal/' . $j->foto_kegiatan) }}" target="_blank" class="badge bg-light text-dark border d-flex align-items-center gap-1.5 text-decoration-none py-1.5 px-2.5" style="border-color: var(--border-color) !important;">
                                @if($isPdf)
                                    <span class="badge bg-danger text-white p-0.5" style="font-size: 9px;">PDF</span>
                                    <span class="fw-semibold" style="font-size: 11px;">Buka Dokumen PDF ↗</span>
                                @else
                                    <img src="{{ asset('storage/jurnal/' . $j->foto_kegiatan) }}" class="rounded border" width="18" height="18" style="object-fit: cover;" alt="Bukti Foto">
                                    <span class="fw-semibold" style="font-size: 11px;">Buka Foto Bukti ↗</span>
                                @endif
                            </a>
                        @else
                            <span class="text-muted small" style="font-size: 11px;">(Tidak ada lampiran)</span>
                        @endif
                    </div>

                    <!-- Row 2: Tombol Aksi Guru -->
                    <div class="d-flex gap-1.5 align-items-center w-100 pt-1.5 border-top" style="border-top-style: dashed !important; border-top-color: var(--border-color) !important;">
                        @if(auth()->user()->role === 'guru' || auth()->user()->role === 'admin')
                            @if($j->status_verifikasi === 'pending')
                                <button class="btn btn-sm btn-primary font-heading flex-grow-1 d-flex align-items-center justify-content-center gap-1.5 py-1.5" data-bs-toggle="modal" data-bs-target="#reviewModal_{{ $j->id }}" style="font-size: 13px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Tinjau & Verifikasi</span>
                                </button>
                            @else
                                <!-- Batalkan Verifikasi -->
                                <form action="{{ route('jurnal.cancel_verify', $j->id) }}" method="POST" id="cancelVerifyFormMob_{{ $j->id }}" class="flex-grow-1">
                                    @csrf
                                    <button type="button" class="btn btn-sm btn-outline-warning w-100 d-flex align-items-center justify-content-center gap-1 py-1.5" title="Batalkan Verifikasi" onclick="confirmCancelVerify({{ $j->id }}, '{{ addslashes($j->penempatanPkl?->murid?->nama ?? 'siswa') }}', 'cancelVerifyFormMob_')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        <span style="font-size: 12px;">Batal Verif</span>
                                    </button>
                                </form>

                                <!-- Ubah Keputusan -->
                                <button class="btn btn-sm btn-outline-secondary flex-grow-1 d-flex align-items-center justify-content-center gap-1 py-1.5" data-bs-toggle="modal" data-bs-target="#reviewModal_{{ $j->id }}" title="Ubah Keputusan">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span style="font-size: 12px;">Ubah</span>
                                </button>
                            @endif

                            <form action="{{ route('jurnal.destroy', $j->id) }}" method="POST" id="deleteJurnalFormMob_{{ $j->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger py-1.5 px-2.5" title="Hapus Jurnal" onclick="window.confirmDelete('deleteJurnalFormMob_{{ $j->id }}', 'jurnal kegiatan {{ addslashes($j->penempatanPkl?->murid?->nama ?? 'siswa') }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card-premium text-center py-4">
                <div class="empty-state py-3">
                    <div class="empty-state-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h6 class="empty-state-title">Tidak Ada Jurnal</h6>
                    <p class="empty-state-text">Tidak ditemukan data jurnal siswa untuk kriteria filter yang dipilih.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Modals (Rendered once per item so both desktop & mobile triggers work seamlessly) -->
    @foreach($journals as $j)
        @if(auth()->user()->role === 'guru' || auth()->user()->role === 'admin')
            <div class="modal fade text-start" id="reviewModal_{{ $j->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-md modal-dialog-centered">
                    <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                        <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                            <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Verifikasi Jurnal Kegiatan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('jurnal.verify', $j->id) }}" method="POST">
                            @csrf
                            <div class="modal-body" x-data="{ decision: '{{ $j->status_verifikasi !== 'pending' ? $j->status_verifikasi : 'disetujui' }}' }">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Siswa & Tanggal</label>
                                    <div class="fw-semibold text-dark">{{ $j->penempatanPkl?->murid?->nama }} ({{ $j->penempatanPkl?->murid?->kelas?->nama ?? '-' }})</div>
                                    <small class="text-muted">{{ $j->tanggal ? \Carbon\Carbon::parse($j->tanggal)->translatedFormat('d F Y') : '-' }} - {{ $j->penempatanPkl?->dudi?->nama }}</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Aktivitas Siswa</label>
                                    <div class="p-2.5 border rounded bg-light small" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important; font-size: 13px; line-height: 1.6; white-space: pre-line; word-break: break-word;">
                                        {{ $j->deskripsi_aktivitas }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="statusSelect_{{ $j->id }}" class="form-label small fw-semibold">Pilih Keputusan</label>
                                    <select name="status" id="statusSelect_{{ $j->id }}" class="form-select form-select-sm" x-model="decision" required>
                                        <option value="disetujui">Setujui Jurnal</option>
                                        <option value="revisi">Minta Revisi Jurnal</option>
                                        <option value="ditolak">Tolak Jurnal</option>
                                        <option value="pending">Kembalikan ke Status Pending</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="catatan_{{ $j->id }}" class="form-label small fw-semibold">Catatan / Komentar Guru (Wajib untuk revisi/tolak)</label>
                                    <textarea name="catatan_verifikasi" id="catatan_{{ $j->id }}" class="form-control form-control-sm" rows="3" placeholder="Masukkan instruksi revisi atau catatan apresiasi..." :required="decision === 'revisi' || decision === 'ditolak'">{{ $j->catatan_verifikasi }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-sm btn-primary" data-loading-text="Menyimpan...">Simpan Keputusan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    @if($journals->hasPages())
    <div class="card-premium p-2 mb-4">
        {{ $journals->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function confirmCancelVerify(id, namaSiswa, prefix = 'cancelVerifyForm_') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Batalkan Verifikasi?',
                text: `Apakah Anda yakin ingin membatalkan status verifikasi jurnal ${namaSiswa} dan mengembalikannya menjadi Pending?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kembalikan ke Pending',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById(prefix + id);
                    if (form) form.submit();
                }
            });
        } else {
            if (confirm(`Batalkan verifikasi jurnal ${namaSiswa} dan kembalikan ke Pending?`)) {
                const form = document.getElementById(prefix + id);
                if (form) form.submit();
            }
        }
    }
</script>
@endsection
