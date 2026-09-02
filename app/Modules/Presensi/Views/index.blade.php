@extends('layouts.admin')

@section('title', 'Riwayat Presensi - PKLku')
@section('page_title', 'Pemantauan Kehadiran Murid')

@section('styles')
<style>
    .searchable-option-item:hover {
        background-color: var(--bg-canvas, #f1f5f9) !important;
    }
    .searchable-option-item.active-selected {
        background-color: rgba(79, 70, 229, 0.12) !important;
        color: var(--accent-primary, #4f46e5) !important;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">Jurnal Kehadiran Harian Murid</h5>
        @if(auth()->user()->role === 'admin')
            <button class="btn btn-sm btn-primary font-heading fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahManual">
                <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: inline-block; vertical-align: middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Koreksi / Tambah Presensi Manual
            </button>
        @endif
    </div>

    <!-- Search / Filter Card -->
    <div class="card-premium mb-4">
        <form action="{{ route('presensi.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold">Tanggal Presensi</label>
                <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ request('tanggal', now()->toDateString()) }}">
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold">Filter Kelas</label>
                <select name="kelas_id" class="form-select form-select-sm">
                    <option value="">-- Semua Kelas --</option>
                    @if(isset($kelasList))
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-lg-4 col-md-8">
                <label class="form-label small fw-semibold">Cari Nama / NIS Murid</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-0" placeholder="Ketik nama atau NIS murid..." value="{{ request('search', request('nama')) }}">
                </div>
            </div>
            <div class="col-lg-2 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary font-heading d-flex align-items-center justify-content-center gap-1 px-3 flex-fill">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filter</span>
                </button>
                @if(request()->hasAny(['kelas_id', 'nama', 'search', 'tanggal']) && (request('kelas_id') || request('nama') || request('search') || request('tanggal') != now()->toDateString()))
                    <a href="{{ route('presensi.index') }}" class="btn btn-sm btn-outline-secondary font-heading d-flex align-items-center justify-content-center gap-1 px-2.5" title="Reset Filter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Reset</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Attendance Table & Mobile View -->
    <div class="card-premium p-0 overflow-hidden mb-4">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-bottom-color: var(--border-color) !important;">
            <h6 class="fw-bold m-0 text-dark">Data Presensi Harian</h6>
            <span class="badge bg-primary-light text-primary font-heading px-2.5 py-1" style="font-size: 11px;">
                Total: {{ $presensis->total() }} Data
            </span>
        </div>

        <!-- Desktop Table View (md and up) -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0" style="min-width: 820px; color: var(--text-primary); font-size: 13px;">
                <thead class="table-light">
                    <tr class="font-heading text-nowrap" style="font-size: 13px; font-weight: 600;">
                        <th class="ps-4" style="width: 220px;">Murid (Kelas)</th>
                        <th>DUDI Tempat PKL</th>
                        <th class="text-center" style="width: 100px;">Check In</th>
                        <th class="text-center" style="width: 70px;">Foto In</th>
                        <th class="text-center" style="width: 100px;">Check Out</th>
                        <th class="text-center" style="width: 70px;">Foto Out</th>
                        <th class="text-center pe-4" style="width: 160px;">Status @if(auth()->user()->role === 'admin') / Aksi @endif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($presensis as $p)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $p->penempatanPkl?->murid?->nama ?? 'Siswa Terhapus' }}</div>
                                <small class="text-muted">{{ $p->penempatanPkl?->murid?->kelas?->nama ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $p->penempatanPkl?->dudi?->nama ?? 'DUDI Terhapus' }}</div>
                                <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                                    @if($p->is_wfa)
                                        <span class="badge bg-primary-light text-primary fw-semibold" style="font-size: 10.5px;">WFA</span>
                                    @else
                                        <span class="badge bg-secondary-light text-secondary fw-semibold" style="font-size: 10.5px;">WFO</span>
                                    @endif

                                    @if($p->shift_harian === 'pagi')
                                        <span class="badge bg-success-light text-success fw-semibold" style="font-size: 10.5px;">Shift Pagi</span>
                                    @elseif($p->shift_harian === 'siang')
                                        <span class="badge bg-warning-light text-warning fw-semibold" style="font-size: 10.5px;">Shift Siang</span>
                                    @elseif($p->shift_harian === 'sore')
                                        <span class="badge bg-orange-light text-orange fw-semibold" style="font-size: 10.5px;">Shift Sore</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center fw-semibold text-success">
                                {{ $p->jam_masuk ? substr($p->jam_masuk, 0, 5) : '-' }}
                            </td>
                            <td class="text-center">
                                @if($p->foto_masuk)
                                    <a href="{{ asset('storage/attendance/' . $p->foto_masuk) }}" target="_blank" aria-label="Foto Check In">
                                        <img src="{{ asset('storage/attendance/' . $p->foto_masuk) }}" class="rounded border" width="36" height="36" style="object-fit: cover;" alt="Foto Masuk">
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center fw-semibold text-warning">
                                {{ $p->jam_pulang ? substr($p->jam_pulang, 0, 5) : '-' }}
                            </td>
                            <td class="text-center">
                                @if($p->foto_pulang)
                                    <a href="{{ asset('storage/attendance/' . $p->foto_pulang) }}" target="_blank" aria-label="Foto Check Out">
                                        <img src="{{ asset('storage/attendance/' . $p->foto_pulang) }}" class="rounded border" width="36" height="36" style="object-fit: cover;" alt="Foto Pulang">
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="mb-1">
                                    @if($p->status_masuk === 'libur_shift')
                                        <span class="badge bg-info-light text-info fw-semibold">Libur Shift</span>
                                    @elseif($p->status_masuk === 'alpha')
                                        <span class="badge bg-danger-light text-danger fw-semibold">Alpha</span>
                                    @elseif($p->status_masuk === 'tepat_waktu')
                                        <span class="badge bg-success-light text-success fw-semibold">Tepat Waktu</span>
                                    @elseif($p->status_masuk === 'terlambat')
                                        <span class="badge bg-danger-light text-danger fw-semibold">Terlambat</span>
                                    @else
                                        <span class="badge bg-secondary-light text-secondary">-</span>
                                    @endif
                                </div>
                                @if($p->status_pulang === 'pulang_cepat')
                                    <div class="mb-1">
                                        <span class="badge bg-warning-light text-warning fw-semibold">Pulang Cepat</span>
                                    </div>
                                @elseif($p->status_pulang === 'tepat_waktu')
                                    <div class="mb-1">
                                        <span class="badge bg-success-light text-success fw-semibold">Pulang Tepat Waktu</span>
                                    </div>
                                @endif
                                @if(auth()->user()->role === 'admin')
                                    <div class="mt-1 d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-action" title="Koreksi Presensi" data-bs-toggle="modal" data-bs-target="#modalEditManual" onclick="editPresensi({{ json_encode([
                                            'id' => $p->id,
                                            'tanggal' => $p->tanggal,
                                            'jam_masuk' => $p->jam_masuk,
                                            'status_masuk' => $p->status_masuk,
                                            'jam_pulang' => $p->jam_pulang,
                                            'status_pulang' => $p->status_pulang,
                                            'penempatan_pkl' => [
                                                'murid' => ['nama' => $p->penempatanPkl->murid->nama]
                                            ]
                                        ]) }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('presensi.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi ini?');" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Hapus Presensi">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="empty-state py-4">
                                    <h6 class="empty-state-title">Tidak ada data presensi</h6>
                                    <p class="empty-state-text">Belum ada murid yang melakukan presensi pada tanggal ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List View (Visible on smartphone < md) -->
        <div class="d-md-none p-3">
            @forelse($presensis as $p)
                <div class="card p-3 mb-3 border rounded shadow-xs" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                    <!-- Top: Student Name & Class + Status Badge -->
                    <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                        <div>
                            <div class="fw-bold text-dark font-heading" style="font-size: 13.5px;">{{ $p->penempatanPkl?->murid?->nama ?? 'Siswa Terhapus' }}</div>
                            <div class="text-muted" style="font-size: 11.5px;">{{ $p->penempatanPkl?->murid?->kelas?->nama ?? '-' }} &bull; <span class="fw-semibold text-secondary">{{ $p->penempatanPkl?->dudi?->nama ?? 'DUDI Terhapus' }}</span></div>
                        </div>
                        <div class="text-end">
                            @if($p->status_masuk === 'libur_shift')
                                <span class="badge bg-info-light text-info fw-semibold" style="font-size: 10.5px;">Libur Shift</span>
                            @elseif($p->status_masuk === 'alpha')
                                <span class="badge bg-danger-light text-danger fw-semibold" style="font-size: 10.5px;">Alpha</span>
                            @elseif($p->status_masuk === 'tepat_waktu')
                                <span class="badge bg-success-light text-success fw-semibold" style="font-size: 10.5px;">Tepat Waktu</span>
                            @elseif($p->status_masuk === 'terlambat')
                                <span class="badge bg-danger-light text-danger fw-semibold" style="font-size: 10.5px;">Terlambat</span>
                            @else
                                <span class="badge bg-secondary-light text-secondary" style="font-size: 10.5px;">-</span>
                            @endif
                        </div>
                    </div>

                    <!-- Work Mode & Shift -->
                    <div class="d-flex align-items-center gap-1.5 mb-2 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                        @if($p->is_wfa)
                            <span class="badge bg-primary-light text-primary fw-semibold" style="font-size: 10.5px;">WFA</span>
                        @else
                            <span class="badge bg-secondary-light text-secondary fw-semibold" style="font-size: 10.5px;">WFO</span>
                        @endif

                        @if($p->shift_harian === 'pagi')
                            <span class="badge bg-success-light text-success fw-semibold" style="font-size: 10.5px;">Shift Pagi</span>
                        @elseif($p->shift_harian === 'siang')
                            <span class="badge bg-warning-light text-warning fw-semibold" style="font-size: 10.5px;">Shift Siang</span>
                        @elseif($p->shift_harian === 'sore')
                            <span class="badge bg-orange-light text-orange fw-semibold" style="font-size: 10.5px;">Shift Sore</span>
                        @endif

                        @if($p->status_pulang === 'pulang_cepat')
                            <span class="badge bg-warning-light text-warning fw-semibold ms-auto" style="font-size: 10.5px;">Pulang Cepat</span>
                        @elseif($p->status_pulang === 'tepat_waktu')
                            <span class="badge bg-success-light text-success fw-semibold ms-auto" style="font-size: 10.5px;">Pulang Tepat</span>
                        @endif
                    </div>

                    <!-- Attendance Details (In & Out Grid) -->
                    <div class="row g-2 mb-2" style="font-size: 12px;">
                        <!-- Check In Column -->
                        <div class="col-6">
                            <div class="p-2 rounded bg-light border" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                                <div class="text-muted small fw-semibold">Check In</div>
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <span class="fw-bold text-success" style="font-size: 13px;">{{ $p->jam_masuk ? substr($p->jam_masuk, 0, 5) : '-' }}</span>
                                    @if($p->foto_masuk)
                                        <a href="{{ asset('storage/attendance/' . $p->foto_masuk) }}" target="_blank">
                                            <img src="{{ asset('storage/attendance/' . $p->foto_masuk) }}" class="rounded border" width="28" height="28" style="object-fit: cover;" alt="Foto Masuk">
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Check Out Column -->
                        <div class="col-6">
                            <div class="p-2 rounded bg-light border" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                                <div class="text-muted small fw-semibold">Check Out</div>
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <span class="fw-bold text-warning" style="font-size: 13px;">{{ $p->jam_pulang ? substr($p->jam_pulang, 0, 5) : '-' }}</span>
                                    @if($p->foto_pulang)
                                        <a href="{{ asset('storage/attendance/' . $p->foto_pulang) }}" target="_blank">
                                            <img src="{{ asset('storage/attendance/' . $p->foto_pulang) }}" class="rounded border" width="28" height="28" style="object-fit: cover;" alt="Foto Pulang">
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Action Buttons (Mobile) -->
                    @if(auth()->user()->role === 'admin')
                        <div class="d-flex gap-2 pt-2 border-top" style="border-top-color: var(--border-color) !important;">
                            <button type="button" class="btn btn-sm btn-outline-warning font-heading flex-grow-1 d-flex align-items-center justify-content-center gap-1 py-1.5" data-bs-toggle="modal" data-bs-target="#modalEditManual" onclick="editPresensi({{ json_encode([
                                'id' => $p->id,
                                'tanggal' => $p->tanggal,
                                'jam_masuk' => $p->jam_masuk,
                                'status_masuk' => $p->status_masuk,
                                'jam_pulang' => $p->jam_pulang,
                                'status_pulang' => $p->status_pulang,
                                'penempatan_pkl' => [
                                    'murid' => ['nama' => $p->penempatanPkl->murid->nama]
                                ]
                            ]) }})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>Koreksi</span>
                            </button>
                            <form action="{{ route('presensi.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi ini?');" class="flex-shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger font-heading d-flex align-items-center justify-content-center gap-1 px-3 py-1.5" title="Hapus Presensi">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-state py-4 text-center">
                    <h6 class="empty-state-title">Tidak ada data presensi</h6>
                    <p class="empty-state-text">Belum ada murid yang melakukan presensi pada tanggal ini.</p>
                </div>
            @endforelse
        </div>

        @if($presensis->hasPages())
            <div class="border-top px-3 py-2" style="border-top-color: var(--border-color) !important;">
                {{ $presensis->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

@if(auth()->user()->role === 'admin')
<!-- Modal Tambah Manual -->
<div class="modal fade" id="modalTambahManual" tabindex="-1" aria-labelledby="modalTambahManualLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalTambahManualLabel">Input Presensi Manual</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('presensi.store_manual') }}" method="POST">
                @csrf
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Murid & DUDI</label>
                        <div class="searchable-select-wrapper position-relative" id="muridSearchWrapper">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-transparent border-end-0 text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </span>
                                <input type="text" id="muridSearchInput" class="form-control form-control-sm border-start-0 ps-0" placeholder="Ketik nama murid, NIS, kelas, atau DUDI..." autocomplete="off">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" id="muridDropdownToggle" tabindex="-1"></button>
                            </div>
                            
                            <input type="hidden" name="penempatan_pkl_id" id="penempatan_pkl_id" required>

                            <div class="searchable-options-list shadow border rounded mt-1 position-absolute w-100" id="muridOptionsList" style="display: none; max-height: 220px; overflow-y: auto; z-index: 1060; background-color: var(--bg-card, #ffffff); border-color: var(--border-color, #e2e8f0);">
                                @foreach($activePlacements as $item)
                                    <div class="searchable-option-item px-3 py-2 border-bottom small" 
                                         data-value="{{ $item->id }}" 
                                         data-label="{{ $item->murid?->nama ?? 'Siswa' }}" 
                                         data-nis="{{ $item->murid?->nis ?? '' }}"
                                         data-kelas="{{ $item->murid?->kelas?->nama ?? '' }}"
                                         data-dudi="{{ $item->dudi?->nama ?? '' }}"
                                         style="cursor: pointer;">
                                        <div class="d-flex justify-content-between align-items-center mb-0.5">
                                            <span class="fw-semibold text-dark">{{ $item->murid?->nama ?? 'Siswa' }}</span>
                                            @if($item->murid?->kelas)
                                                <span class="badge bg-primary-light text-primary fw-semibold" style="font-size: 10.5px;">{{ $item->murid->kelas->nama }}</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small d-flex justify-content-between" style="font-size: 11px;">
                                            <span>NIS: {{ $item->murid?->nis ?? '-' }}</span>
                                            <span class="text-secondary fw-medium">🏢 {{ $item->dudi?->nama ?? '-' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="no-options-found px-3 py-3 text-muted small text-center" style="display: none;">
                                    Data murid atau DUDI tidak ditemukan
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal" class="form-label small fw-semibold">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="{{ request('tanggal', date('Y-m-d')) }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="jam_masuk" class="form-label small fw-semibold">Jam Masuk (Opsional)</label>
                            <input type="time" name="jam_masuk" id="jam_masuk" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label for="status_masuk" class="form-label small fw-semibold">Status Masuk (Opsional)</label>
                            <select name="status_masuk" id="status_masuk" class="form-select form-select-sm">
                                <option value="">-- Pilih Status Masuk --</option>
                                <option value="tepat_waktu">Tepat Waktu</option>
                                <option value="terlambat">Terlambat</option>
                                <option value="libur_shift">🌴 Libur Shift DUDI</option>
                                <option value="alpha">❌ Alpha</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="jam_pulang" class="form-label small fw-semibold">Jam Pulang (Opsional)</label>
                            <input type="time" name="jam_pulang" id="jam_pulang" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label for="status_pulang" class="form-label small fw-semibold">Status Pulang (Opsional)</label>
                            <select name="status_pulang" id="status_pulang" class="form-select form-select-sm">
                                <option value="">-- Tanpa Status Pulang --</option>
                                <option value="tepat_waktu">Tepat Waktu</option>
                                <option value="pulang_cepat">Pulang Cepat</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-muted small" style="font-size: 11px;">
                        * Isikan Jam Masuk / Jam Pulang, atau pilih status khusus seperti Libur Shift / Alpha.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Presensi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Manual -->
<div class="modal fade" id="modalEditManual" tabindex="-1" aria-labelledby="modalEditManualLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalEditManualLabel">Koreksi Presensi Manual</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditManual" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Murid</label>
                        <input type="text" id="edit_nama_murid" class="form-control form-control-sm" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Tanggal</label>
                        <input type="text" id="edit_tanggal" class="form-control form-control-sm" disabled>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="edit_jam_masuk" class="form-label small fw-semibold">Jam Masuk (Opsional)</label>
                            <input type="time" name="jam_masuk" id="edit_jam_masuk" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_status_masuk" class="form-label small fw-semibold">Status Masuk (Opsional)</label>
                            <select name="status_masuk" id="edit_status_masuk" class="form-select form-select-sm">
                                <option value="">-- Pilih Status Masuk --</option>
                                <option value="tepat_waktu">Tepat Waktu</option>
                                <option value="terlambat">Terlambat</option>
                                <option value="libur_shift">🌴 Libur Shift DUDI</option>
                                <option value="alpha">❌ Alpha</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="edit_jam_pulang" class="form-label small fw-semibold">Jam Pulang (Opsional)</label>
                            <input type="time" name="jam_pulang" id="edit_jam_pulang" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_status_pulang" class="form-label small fw-semibold">Status Pulang (Opsional)</label>
                            <select name="status_pulang" id="edit_status_pulang" class="form-select form-select-sm">
                                <option value="">-- Tanpa Status Pulang --</option>
                                <option value="tepat_waktu">Tepat Waktu</option>
                                <option value="pulang_cepat">Pulang Cepat</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-muted small" style="font-size: 11px;">
                        * Isikan salah satu (Jam Masuk saja / Jam Pulang saja) atau isi keduanya.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
@if(auth()->user()->role === 'admin')
<script>
    function editPresensi(p) {
        const baseUrl = '{{ url("/presensi") }}';
        document.getElementById('formEditManual').action = baseUrl + '/' + p.id + '/manual';
        document.getElementById('edit_nama_murid').value = p.penempatan_pkl ? p.penempatan_pkl.murid.nama : '';
        document.getElementById('edit_tanggal').value = p.tanggal;
        document.getElementById('edit_jam_masuk').value = p.jam_masuk ? p.jam_masuk.substring(0, 5) : '';
        document.getElementById('edit_status_masuk').value = p.status_masuk || '';
        document.getElementById('edit_jam_pulang').value = p.jam_pulang ? p.jam_pulang.substring(0, 5) : '';
        document.getElementById('edit_status_pulang').value = p.status_pulang || '';
    }

    // Searchable select for Murid & DUDI in Tambah Presensi Manual
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('muridSearchInput');
        const toggle = document.getElementById('muridDropdownToggle');
        const hidden = document.getElementById('penempatan_pkl_id');
        const list = document.getElementById('muridOptionsList');
        const wrapper = document.getElementById('muridSearchWrapper');
        if (!input || !list || !hidden) return;

        const items = list.querySelectorAll('.searchable-option-item');
        const noResult = list.querySelector('.no-options-found');

        function filterItems(query) {
            const q = query.toLowerCase().trim();
            let visible = 0;
            items.forEach(item => {
                const name = (item.dataset.label || '').toLowerCase();
                const nis = (item.dataset.nis || '').toLowerCase();
                const kelas = (item.dataset.kelas || '').toLowerCase();
                const dudi = (item.dataset.dudi || '').toLowerCase();
                if (!q || name.includes(q) || nis.includes(q) || kelas.includes(q) || dudi.includes(q)) {
                    item.style.display = '';
                    visible++;
                } else {
                    item.style.display = 'none';
                }
            });
            if (noResult) noResult.style.display = visible === 0 ? '' : 'none';
        }

        input.addEventListener('input', function() {
            list.style.display = 'block';
            hidden.value = ''; // clear selection if user modifies text
            filterItems(this.value);
        });

        input.addEventListener('focus', function() {
            list.style.display = 'block';
            filterItems(this.value);
        });

        if (toggle) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                list.style.display = list.style.display === 'block' ? 'none' : 'block';
                if (list.style.display === 'block') {
                    filterItems(input.value);
                    input.focus();
                }
            });
        }

        items.forEach(item => {
            item.addEventListener('click', function() {
                const val = this.dataset.value;
                const label = this.dataset.label;
                const kelas = this.dataset.kelas;
                const dudi = this.dataset.dudi;
                hidden.value = val;
                input.value = `${label} (${kelas}) - ${dudi}`;
                list.style.display = 'none';

                items.forEach(i => i.classList.remove('active-selected'));
                this.classList.add('active-selected');
            });
        });

        document.addEventListener('click', function(e) {
            if (wrapper && !wrapper.contains(e.target)) {
                list.style.display = 'none';
            }
        });

        // Reset when modal closes/opens
        const modalEl = document.getElementById('modalTambahManual');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function() {
                input.value = '';
                hidden.value = '';
                list.style.display = 'none';
                items.forEach(i => {
                    i.style.display = '';
                    i.classList.remove('active-selected');
                });
                if (noResult) noResult.style.display = 'none';
            });
        }
    });
</script>
@endif
@endsection
