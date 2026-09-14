@extends('layouts.admin')

@section('title', 'Verifikasi Jurnal - PKLku')
@section('page_title', 'Verifikasi Jurnal Bimbingan')

@section('content')
<style>
    /* Modern Status Filter Chips */
    .status-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.35rem 0.8rem;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        color: var(--text-secondary, #64748b);
        background-color: var(--bg-card, #ffffff);
        border: 1px solid var(--border-color, #e2e8f0);
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }
    .status-filter-chip:hover {
        background-color: var(--bg-canvas, #f8fafc);
        border-color: #cbd5e1;
        color: var(--text-primary, #0f172a);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.06);
    }
    .status-filter-chip:active {
        transform: translateY(0);
    }
    .status-filter-chip .chip-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .status-filter-chip .chip-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 19px;
        height: 19px;
        padding: 0 5px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 600;
        line-height: 1;
        background-color: #f1f5f9;
        color: #475569;
        transition: all 0.18s ease;
    }

    /* Active States */
    .status-filter-chip.active {
        font-weight: 600;
        box-shadow: 0 2px 5px rgba(15, 23, 42, 0.08);
    }
    
    /* Active: Semua */
    .status-filter-chip.chip-all.active {
        background-color: var(--accent-primary, #4f46e5) !important;
        border-color: var(--accent-primary, #4f46e5) !important;
        color: #ffffff !important;
    }
    .status-filter-chip.chip-all.active .chip-badge {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }

    /* Active: Menunggu */
    .status-filter-chip.chip-pending.active {
        background-color: #fef3c7 !important;
        border-color: #f59e0b !important;
        color: #92400e !important;
    }
    .status-filter-chip.chip-pending.active .chip-badge {
        background-color: #f59e0b !important;
        color: #ffffff !important;
    }
    .status-filter-chip.chip-pending:not(.active) .chip-badge {
        background-color: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    /* Active: Disetujui */
    .status-filter-chip.chip-disetujui.active {
        background-color: #d1fae5 !important;
        border-color: #10b981 !important;
        color: #065f46 !important;
    }
    .status-filter-chip.chip-disetujui.active .chip-badge {
        background-color: #10b981 !important;
        color: #ffffff !important;
    }
    .status-filter-chip.chip-disetujui:not(.active) .chip-badge {
        background-color: rgba(16, 185, 129, 0.12);
        color: #047857;
    }

    /* Active: Revisi */
    .status-filter-chip.chip-revisi.active {
        background-color: #ffedd5 !important;
        border-color: #f97316 !important;
        color: #9a3412 !important;
    }
    .status-filter-chip.chip-revisi.active .chip-badge {
        background-color: #f97316 !important;
        color: #ffffff !important;
    }
    .status-filter-chip.chip-revisi:not(.active) .chip-badge {
        background-color: rgba(249, 115, 22, 0.12);
        color: #c2410c;
    }

    /* Active: Ditolak */
    .status-filter-chip.chip-ditolak.active {
        background-color: #fee2e2 !important;
        border-color: #ef4444 !important;
        color: #991b1b !important;
    }
    .status-filter-chip.chip-ditolak.active .chip-badge {
        background-color: #ef4444 !important;
        color: #ffffff !important;
    }
    .status-filter-chip.chip-ditolak:not(.active) .chip-badge {
        background-color: rgba(239, 68, 68, 0.12);
        color: #b91c1c;
    }

    /* Dark Mode */
    [data-bs-theme="dark"] .status-filter-chip {
        background-color: var(--bg-card);
        border-color: var(--border-color);
        color: #cbd5e1;
    }
    [data-bs-theme="dark"] .status-filter-chip:hover {
        background-color: rgba(255, 255, 255, 0.05);
        border-color: #475569;
        color: #f8fafc;
    }
    [data-bs-theme="dark"] .status-filter-chip.chip-pending.active {
        background-color: rgba(245, 158, 11, 0.25) !important;
        color: #fde68a !important;
    }
    [data-bs-theme="dark"] .status-filter-chip.chip-disetujui.active {
        background-color: rgba(16, 185, 129, 0.25) !important;
        color: #a7f3d0 !important;
    }
    [data-bs-theme="dark"] .status-filter-chip.chip-revisi.active {
        background-color: rgba(249, 115, 22, 0.25) !important;
        color: #fed7aa !important;
    }
    [data-bs-theme="dark"] .status-filter-chip.chip-ditolak.active {
        background-color: rgba(239, 68, 68, 0.25) !important;
        color: #fecaca !important;
    }

    /* ===== Bulk Action Bar (Inline – Non-Floating) ===== */
    #bulkActionBar {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 10px;
        padding: 10px 14px;
        animation: bulkBarFadeIn 0.2s ease both;
    }
    [data-bs-theme="dark"] #bulkActionBar {
        background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.02) 100%);
    }
    @keyframes bulkBarFadeIn {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .bulk-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 7px;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
        font-family: inherit;
    }
    .bulk-action-btn:hover {
        filter: brightness(0.93);
        transform: translateY(-1px);
    }
    .bulk-action-btn:active {
        transform: translateY(0);
    }
    .bulk-action-btn.btn-approve  { background-color: #10b981; color: #fff; }
    .bulk-action-btn.btn-revisi   { background-color: #f59e0b; color: #fff; }
    .bulk-action-btn.btn-tolak    { background-color: #ef4444; color: #fff; }
    .bulk-action-btn.btn-pending  { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    [data-bs-theme="dark"] .bulk-action-btn.btn-pending { background-color: rgba(255,255,255,0.07); color: #94a3b8; border-color: rgba(255,255,255,0.12); }
    .bulk-action-btn.btn-deselect { background-color: transparent; color: #94a3b8; padding: 5px 7px; border: 1px solid transparent; }
    .bulk-action-btn.btn-deselect:hover { background-color: rgba(0,0,0,0.05); color: #64748b; }
    .bulk-sel-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 6px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        background-color: var(--accent-primary, #4f46e5);
        color: #fff;
        line-height: 1;
    }
    /* Mobile bulk bar — NO overflow/max-height clipping */
    #bulkActionBarMob {
        animation: bulkBarFadeIn 0.2s ease both;
    }
</style>

<div class="container-fluid p-0">
    <!-- Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">Verifikasi Jurnal Harian Siswa</h5>
    </div>

    <!-- Filter & Search Card -->
    <div class="card-premium mb-4">
        <!-- Quick Status Filter (Neat, Intuitive, with Icons & Real-Time Counters) -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-3 border-bottom" style="border-bottom-color: var(--border-color) !important;">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="small fw-bold text-muted font-heading d-inline-flex align-items-center gap-1 me-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Status:
                </span>

                @php
                    $curStatus = request('status');
                    $counts = $statusCounts ?? [
                        'all' => $journals->total(),
                        'pending' => 0,
                        'disetujui' => 0,
                        'revisi' => 0,
                        'ditolak' => 0
                    ];
                @endphp

                <!-- Semua -->
                <a href="{{ route('jurnal.index', request()->except('status')) }}" class="status-filter-chip chip-all {{ !$curStatus ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span>Semua</span>
                    <span class="chip-badge">{{ $counts['all'] ?? $journals->total() }}</span>
                </a>

                <!-- Menunggu (Pending) -->
                <a href="{{ route('jurnal.index', array_merge(request()->query(), ['status' => 'pending'])) }}" class="status-filter-chip chip-pending {{ $curStatus === 'pending' ? 'active' : '' }}" title="Jurnal menunggu verifikasi guru">
                    <span class="chip-dot" style="background-color: #f59e0b;"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Menunggu</span>
                    <span class="chip-badge">{{ $counts['pending'] ?? 0 }}</span>
                </a>

                <!-- Disetujui -->
                <a href="{{ route('jurnal.index', array_merge(request()->query(), ['status' => 'disetujui'])) }}" class="status-filter-chip chip-disetujui {{ $curStatus === 'disetujui' ? 'active' : '' }}" title="Jurnal telah disetujui">
                    <span class="chip-dot" style="background-color: #10b981;"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Disetujui</span>
                    <span class="chip-badge">{{ $counts['disetujui'] ?? 0 }}</span>
                </a>

                <!-- Revisi -->
                <a href="{{ route('jurnal.index', array_merge(request()->query(), ['status' => 'revisi'])) }}" class="status-filter-chip chip-revisi {{ $curStatus === 'revisi' ? 'active' : '' }}" title="Jurnal memerlukan revisi dari murid">
                    <span class="chip-dot" style="background-color: #f97316;"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Revisi</span>
                    <span class="chip-badge">{{ $counts['revisi'] ?? 0 }}</span>
                </a>

                <!-- Ditolak -->
                <a href="{{ route('jurnal.index', array_merge(request()->query(), ['status' => 'ditolak'])) }}" class="status-filter-chip chip-ditolak {{ $curStatus === 'ditolak' ? 'active' : '' }}" title="Jurnal ditolak guru">
                    <span class="chip-dot" style="background-color: #ef4444;"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>Ditolak</span>
                    <span class="chip-badge">{{ $counts['ditolak'] ?? 0 }}</span>
                </a>
            </div>

            <span class="badge bg-primary-light text-primary font-heading px-2.5 py-1.5" style="font-size: 12px; border-radius: 6px;">
                Ditampilkan: {{ $journals->total() }} Jurnal
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

    <div class="card-premium p-0 overflow-hidden d-none d-md-block mb-4">
        {{-- Card Header: Default state (title) + Bulk Action Bar state --}}
        <div class="p-3 border-bottom" style="border-bottom-color: var(--border-color) !important;">

            {{-- Default Header (tampil ketika tidak ada yang dicentang) --}}
            <div class="d-flex align-items-center justify-content-between gap-2" id="cardHeaderDefault">
                <div class="d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h6 class="fw-bold m-0 text-dark dark-text-light font-heading">Verifikasi Jurnal Harian</h6>
                </div>
                @if(auth()->user()->role === 'guru' || auth()->user()->role === 'admin')
                <span class="text-muted small font-heading" style="font-size: 12px;">Centang baris untuk verifikasi massal</span>
                @endif
            </div>

            {{-- Bulk Action Bar (tampil ketika ≥1 jurnal dicentang) --}}
            @if(auth()->user()->role === 'guru' || auth()->user()->role === 'admin')
            <div id="bulkActionBar" class="d-none">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    {{-- Kiri: Jumlah terpilih --}}
                    <div class="d-flex align-items-center gap-2">
                        <span class="bulk-sel-badge" id="bulkSelectedCount">0</span>
                        <span class="fw-semibold font-heading text-dark dark-text-light" style="font-size: 13px;">Jurnal dipilih</span>
                        <span class="text-muted" style="font-size: 12px;">— Pilih aksi verifikasi:</span>
                    </div>

                    {{-- Kanan: Tombol aksi --}}
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                        <button type="button" class="bulk-action-btn btn-approve font-heading" onclick="openBulkVerifyModal('disetujui')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Setujui
                        </button>
                        <button type="button" class="bulk-action-btn btn-revisi font-heading" onclick="openBulkVerifyModal('revisi')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Revisi
                        </button>
                        <button type="button" class="bulk-action-btn btn-tolak font-heading" onclick="openBulkVerifyModal('ditolak')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            Tolak
                        </button>
                        <button type="button" class="bulk-action-btn btn-pending font-heading" onclick="openBulkVerifyModal('pending')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            Pending
                        </button>
                        <span class="mx-1" style="width:1px; height:20px; background: var(--border-color); display:inline-block; vertical-align:middle;"></span>
                        <button type="button" class="bulk-action-btn btn-deselect" onclick="deselectAllJournals()" title="Batalkan pilihan">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 1000px; color: var(--text-primary); font-size: 13px;">
                <thead class="table-light">
                    <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                        @if(auth()->user()->role === 'guru' || auth()->user()->role === 'admin')
                            <th class="ps-3 text-center" style="width: 44px;">
                                <input type="checkbox" class="form-check-input" id="selectAllJournals" title="Pilih Semua di Halaman Ini" style="cursor: pointer; width: 1.15em; height: 1.15em;">
                            </th>
                        @endif
                        <th class="{{ (auth()->user()->role === 'guru' || auth()->user()->role === 'admin') ? 'ps-2' : 'ps-4' }}" style="width: 175px; min-width: 160px;">Tanggal</th>
                        <th style="width: 190px;">Siswa & Kelas</th>
                        <th style="width: 180px;">DUDI Tempat PKL</th>
                        <th style="min-width: 270px;">Isi Laporan Aktivitas</th>
                        <th class="text-center" style="width: 85px;">Foto Bukti</th>
                        <th class="text-center" style="width: 115px;">Status</th>
                        <th class="text-center pe-4" style="width: 115px;">Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $j)
                        <tr>
                            @if(auth()->user()->role === 'guru' || auth()->user()->role === 'admin')
                                <td class="ps-3 text-center">
                                    <input type="checkbox" class="form-check-input journal-item-checkbox" value="{{ $j->id }}" id="chk_{{ $j->id }}" data-student="{{ $j->penempatanPkl?->murid?->nama }}" data-status="{{ $j->status_verifikasi }}" style="cursor: pointer; width: 1.15em; height: 1.15em;">
                                </td>
                            @endif
                            <td class="{{ (auth()->user()->role === 'guru' || auth()->user()->role === 'admin') ? 'ps-2' : 'ps-4' }} fw-semibold text-nowrap" style="font-size: 13px;">
                                <label for="chk_{{ $j->id }}" style="cursor: pointer; margin: 0; font-weight: inherit;">
                                    {{ $j->tanggal ? \Carbon\Carbon::parse($j->tanggal)->locale('id')->translatedFormat('l, j F Y') : '-' }}
                                </label>
                            </td>
                            <td>
                                <div class="fw-bold text-dark dark-text-light font-heading" style="font-size: 13px; line-height: 1.3;">{{ $j->penempatanPkl?->murid?->nama ?? 'Siswa Terhapus' }}</div>
                                <div class="text-muted small" style="font-size: 12px; margin-top: 2px;">{{ $j->penempatanPkl?->murid?->kelas?->nama ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark dark-text-light font-heading" style="font-size: 13px; line-height: 1.3;">{{ $j->penempatanPkl?->dudi?->nama ?? 'DUDI Terhapus' }}</div>
                            </td>
                            <td>
                                <div class="text-break" style="line-height: 1.5; word-break: break-word; white-space: normal;">{{ $j->deskripsi_aktivitas }}</div>
                                @if($j->catatan_verifikasi)
                                    @php
                                        $komentarColor = match($j->status_verifikasi) {
                                            'disetujui' => 'text-success',
                                            'revisi'    => 'text-warning',
                                            'ditolak'   => 'text-danger',
                                            default     => 'text-secondary'
                                        };
                                        $komentarStyle = match($j->status_verifikasi) {
                                            'revisi'    => 'color: #d97706 !important;',
                                            default     => ''
                                        };
                                        $komentarLabel = match($j->status_verifikasi) {
                                            'disetujui' => 'Komentar (Disetujui):',
                                            'revisi'    => 'Komentar (Revisi):',
                                            'ditolak'   => 'Komentar (Ditolak):',
                                            default     => 'Komentar:'
                                        };
                                    @endphp
                                    <small class="{{ $komentarColor }} d-block mt-1" style="{{ $komentarStyle }}"><strong>{{ $komentarLabel }}</strong> {{ $j->catatan_verifikasi }}</small>
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Revisi
                                    </span>
                                @elseif($j->status_verifikasi === 'ditolak')
                                    <span class="status-badge bg-danger-light text-danger">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Ditolak
                                    </span>
                                @else
                                    <span class="status-badge" style="background-color: rgba(245, 158, 11, 0.12) !important; color: #b45309 !important;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Menunggu
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
                            <td colspan="{{ (auth()->user()->role === 'guru' || auth()->user()->role === 'admin') ? '8' : '7' }}" class="text-center py-4">
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

    <!-- Mobile Card Feed View (Visible on smartphone / tablet < md) -->
    <div class="d-md-none mb-4">
        @if((auth()->user()->role === 'guru' || auth()->user()->role === 'admin') && $journals->count() > 0)
            {{-- Mobile Control Bar --}}

            {{-- Default state: Pilih Semua (tersembunyi saat ada yang dicentang via JS) --}}
            <div class="d-flex align-items-center justify-content-between px-3 py-2 rounded-3 border mb-3" id="mobControlDefault" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                <div class="form-check m-0 d-flex align-items-center gap-2">
                    <input type="checkbox" class="form-check-input m-0" id="selectAllJournalsMob" style="cursor: pointer; width: 1.1em; height: 1.1em;">
                    <label class="form-check-label fw-semibold font-heading text-dark" for="selectAllJournalsMob" style="cursor: pointer; font-size: 13px;">Pilih Semua</label>
                </div>
                <span class="text-muted font-heading" id="selectedCountMobText" style="font-size: 12px;">0 dipilih</span>
            </div>

            {{-- Bulk Action Bar (tampil saat ada yang dicentang, menggantikan default bar) --}}
            <div id="bulkActionBarMob" class="d-none mb-3" style="border-radius: 10px; border: 1.5px solid var(--border-color, #e2e8f0); background-color: var(--bg-canvas, #f8fafc); padding: 12px;">
                {{-- Baris 1: Jumlah & Batal --}}
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="bulk-sel-badge" id="bulkSelectedCountMob">0</span>
                        <span class="fw-semibold font-heading text-dark dark-text-light" style="font-size: 13px;">jurnal dipilih</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-light border text-muted font-heading d-flex align-items-center gap-1 py-1" onclick="deselectAllJournals()" style="font-size: 12px; border-color: var(--border-color) !important;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        Batalkan
                    </button>
                </div>
                {{-- Baris 2: Tombol Aksi 2x2 Grid --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <button type="button" class="bulk-action-btn btn-approve font-heading justify-content-center" onclick="openBulkVerifyModal('disetujui')" style="border-radius: 8px; padding: 9px 12px; font-size: 13px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Setujui
                    </button>
                    <button type="button" class="bulk-action-btn btn-revisi font-heading justify-content-center" onclick="openBulkVerifyModal('revisi')" style="border-radius: 8px; padding: 9px 12px; font-size: 13px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Revisi
                    </button>
                    <button type="button" class="bulk-action-btn btn-tolak font-heading justify-content-center" onclick="openBulkVerifyModal('ditolak')" style="border-radius: 8px; padding: 9px 12px; font-size: 13px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        Tolak
                    </button>
                    <button type="button" class="bulk-action-btn btn-pending font-heading justify-content-center" onclick="openBulkVerifyModal('pending')" style="border-radius: 8px; padding: 9px 12px; font-size: 13px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        Pending
                    </button>
                </div>
            </div>
        @endif

        @forelse($journals as $j)
            <div class="card-premium mb-3 p-3 position-relative" style="background-color: var(--bg-card); border-left: 4px solid {{ $j->status_verifikasi === 'disetujui' ? '#10b981' : ($j->status_verifikasi === 'revisi' ? '#f59e0b' : ($j->status_verifikasi === 'ditolak' ? '#ef4444' : '#64748b')) }} !important;">
                <!-- Header: Siswa & Status -->
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                    <div class="d-flex align-items-start gap-2">
                        @if(auth()->user()->role === 'guru' || auth()->user()->role === 'admin')
                            <div class="form-check m-0 pt-0.5">
                                <input type="checkbox" class="form-check-input journal-item-checkbox" value="{{ $j->id }}" id="chk_mob_{{ $j->id }}" data-student="{{ $j->penempatanPkl?->murid?->nama }}" data-status="{{ $j->status_verifikasi }}" style="cursor: pointer; width: 1.15em; height: 1.15em;">
                            </div>
                        @endif
                        <div>
                            <label for="chk_mob_{{ $j->id }}" class="fw-bold text-dark font-heading m-0 d-block" style="font-size: 14px; cursor: pointer; line-height: 1.3;">
                                {{ $j->penempatanPkl?->murid?->nama ?? 'Siswa Terhapus' }}
                            </label>
                            <div class="text-muted small" style="font-size: 12px; margin-top: 2px;">
                                {{ $j->penempatanPkl?->murid?->kelas?->nama ?? '-' }}
                            </div>
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Revisi
                            </span>
                        @elseif($j->status_verifikasi === 'ditolak')
                            <span class="status-badge bg-danger-light text-danger" style="font-size: 11px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                Ditolak
                            </span>
                        @else
                            <span class="status-badge" style="background-color: rgba(245, 158, 11, 0.12) !important; color: #b45309 !important; font-size: 11px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Menunggu
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
                        <strong class="text-dark">{{ $j->tanggal ? \Carbon\Carbon::parse($j->tanggal)->locale('id')->translatedFormat('l, j F Y') : '-' }}</strong>
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
                    @php
                        $cardTheme = match($j->status_verifikasi) {
                            'disetujui' => [
                                'bg'     => 'rgba(16, 185, 129, 0.08)',
                                'border' => 'rgba(16, 185, 129, 0.25)',
                                'class'  => 'text-success',
                                'style'  => '',
                                'label'  => 'Komentar Guru (Disetujui):',
                            ],
                            'revisi' => [
                                'bg'     => 'rgba(245, 158, 11, 0.08)',
                                'border' => 'rgba(245, 158, 11, 0.3)',
                                'class'  => 'text-warning',
                                'style'  => 'color: #b45309 !important;',
                                'label'  => 'Komentar Guru (Perlu Revisi):',
                            ],
                            'ditolak' => [
                                'bg'     => 'rgba(239, 68, 68, 0.08)',
                                'border' => 'rgba(239, 68, 68, 0.25)',
                                'class'  => 'text-danger',
                                'style'  => '',
                                'label'  => 'Komentar Guru (Ditolak):',
                            ],
                            default => [
                                'bg'     => 'rgba(100, 116, 139, 0.08)',
                                'border' => 'rgba(100, 116, 139, 0.25)',
                                'class'  => 'text-secondary',
                                'style'  => '',
                                'label'  => 'Komentar Guru:',
                            ],
                        };
                    @endphp
                    <div class="p-2 rounded mb-2 border" style="background-color: {{ $cardTheme['bg'] }}; border-color: {{ $cardTheme['border'] }} !important; font-size: 12px; line-height: 1.4;">
                        <strong class="{{ $cardTheme['class'] }}" style="{{ $cardTheme['style'] }}">{{ $cardTheme['label'] }}</strong>
                        <div class="text-dark mt-0.5" style="white-space: pre-line;">{{ $j->catatan_verifikasi }}</div>
                    </div>
                @endif

                <!-- Footer: Bukti & Tombol Aksi -->
                <div class="pt-2.5 border-top d-flex flex-column gap-2" style="border-top-color: var(--border-color) !important;">
                    <!-- Row 1: Bukti Kegiatan Info / Link -->
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small" style="font-size: 12px;">Bukti Lampiran:</span>
                        @if($j->foto_kegiatan)
                            @php
                                $isPdf = Str::endsWith(strtolower($j->foto_kegiatan), '.pdf');
                            @endphp
                            <a href="{{ asset('storage/jurnal/' . $j->foto_kegiatan) }}" target="_blank" class="badge bg-light text-dark border d-flex align-items-center gap-1.5 text-decoration-none py-1.5 px-2.5" style="border-color: var(--border-color) !important;">
                                @if($isPdf)
                                    <span class="badge bg-danger text-white p-0.5" style="font-size: 11px;">PDF</span>
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

    @if(auth()->user()->role === 'guru' || auth()->user()->role === 'admin')
        {{-- Modal Verifikasi Massal Terpadu (ditaruh di luar card agar tidak kena overflow/backdrop trap) --}}
        <div class="modal fade text-start" id="bulkVerifyModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px;">
                    <div class="modal-header border-bottom py-3 px-4" style="border-bottom-color: var(--border-color) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <div id="bulkModalIconWrapper" class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; background-color: rgba(16, 185, 129, 0.1); color: #10b981;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h5 class="modal-title font-heading fw-bold m-0 text-dark dark-text-light" id="bulkModalTitle" style="font-size: 15px;">Verifikasi Sekaligus</h5>
                                <small class="text-muted d-block" id="bulkModalSubtitle" style="font-size: 12px;">Penerapan keputusan pada jurnal terpilih</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="{{ route('jurnal.bulk_verify') }}" method="POST" id="bulkVerifyForm">
                        @csrf
                        <input type="hidden" name="status" id="bulkStatusInput" value="disetujui">
                        <div id="bulkJournalIdsContainer"></div>

                        <div class="modal-body p-4">
                            {{-- Info Box --}}
                            <div class="p-3 rounded-3 border mb-3" id="bulkInfoBox" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="bulk-sel-badge" id="bulkModalItemCount" style="min-width:28px;">0</span>
                                    <span class="fw-semibold text-dark dark-text-light font-heading" id="bulkModalActionNotice" style="font-size: 13px;">
                                        Jurnal kegiatan akan diproses.
                                    </span>
                                </div>
                                <div class="text-muted mt-1.5" id="bulkModalDetailedNotice" style="font-size: 12px; line-height: 1.5;">
                                    Tindakan ini akan memperbarui status seluruh jurnal terpilih secara bersamaan.
                                </div>
                            </div>

                            {{-- Catatan Field --}}
                            <div class="mb-2" id="bulkCatatanGroup">
                                <label for="bulkCatatanVerifikasi" class="form-label small fw-semibold d-flex justify-content-between align-items-center mb-1" id="bulkCatatanLabel">
                                    <span class="text-secondary">Catatan / Komentar Guru</span>
                                    <span class="badge bg-secondary-light text-secondary fw-normal" id="bulkCatatanBadge" style="font-size: 11px;">Opsional</span>
                                </label>
                                <textarea name="catatan_verifikasi" id="bulkCatatanVerifikasi" class="form-control form-control-sm" rows="3" placeholder="Tulis instruksi revisi, apresiasi, atau alasan penolakan..."></textarea>
                                <div class="form-text text-muted mt-1" style="font-size: 11px;">Catatan ini akan tampil pada riwayat jurnal seluruh siswa yang dipilih.</div>
                            </div>
                        </div>

                        <div class="modal-footer border-top py-3 px-4" style="border-top-color: var(--border-color) !important;">
                            <button type="button" class="btn btn-sm btn-light border font-heading px-3" data-bs-dismiss="modal" style="border-color: var(--border-color) !important;">Batal</button>
                            <button type="submit" class="btn btn-sm btn-success px-4 py-1.5 font-heading fw-semibold" id="bulkModalSubmitBtn" data-loading-text="Memproses...">
                                Simpan Keputusan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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

    // =====================================================
    // Bulk Verification — Inline Action Bar (Non-Floating)
    // =====================================================
    document.addEventListener('DOMContentLoaded', function () {
        const selectAllDesktop  = document.getElementById('selectAllJournals');
        const selectAllMobile   = document.getElementById('selectAllJournalsMob');

        // Desktop inline bar elements
        const bulkActionBar     = document.getElementById('bulkActionBar');
        const cardHeaderDefault = document.getElementById('cardHeaderDefault');
        const bulkCountBadge    = document.getElementById('bulkSelectedCount');

        // Mobile bar elements
        const bulkActionBarMob  = document.getElementById('bulkActionBarMob');
        const mobControlDefault = document.getElementById('mobControlDefault');
        const bulkCountBadgeMob = document.getElementById('bulkSelectedCountMob');
        const selectedCountMobText = document.getElementById('selectedCountMobText');

        function getCheckboxes() {
            return Array.from(document.querySelectorAll('.journal-item-checkbox'));
        }

        function getCheckedIds() {
            const checked = [];
            getCheckboxes().forEach(chk => {
                if (chk.checked && !checked.includes(chk.value)) {
                    checked.push(chk.value);
                }
            });
            return checked;
        }

        function updateSelectionUI() {
            const checkedIds = getCheckedIds();
            const count = checkedIds.length;

            // Update counters
            if (bulkCountBadge)    bulkCountBadge.textContent    = count;
            if (bulkCountBadgeMob) bulkCountBadgeMob.textContent = count;
            if (selectedCountMobText) selectedCountMobText.textContent = count + ' dipilih';

            // Desktop: toggle between default header and bulk bar
            if (bulkActionBar && cardHeaderDefault) {
                if (count > 0) {
                    cardHeaderDefault.classList.add('d-none');
                    bulkActionBar.classList.remove('d-none');
                } else {
                    bulkActionBar.classList.add('d-none');
                    cardHeaderDefault.classList.remove('d-none');
                }
            }

            // Mobile: swap antara default control bar dan bulk action bar
            if (bulkActionBarMob && mobControlDefault) {
                if (count > 0) {
                    mobControlDefault.classList.add('d-none');
                    bulkActionBarMob.classList.remove('d-none');
                } else {
                    bulkActionBarMob.classList.add('d-none');
                    mobControlDefault.classList.remove('d-none');
                }
            }


            // Sync select-all state
            const allBoxes   = getCheckboxes();
            const uniqueTotal = Array.from(new Set(allBoxes.map(c => c.value))).length;
            const isAll = uniqueTotal > 0 && count === uniqueTotal;

            if (selectAllDesktop) selectAllDesktop.checked = isAll;
            if (selectAllMobile)  selectAllMobile.checked  = isAll;
        }

        function toggleSelectAll(checked) {
            getCheckboxes().forEach(chk => { chk.checked = checked; });
            updateSelectionUI();
        }

        if (selectAllDesktop) {
            selectAllDesktop.addEventListener('change', function () {
                toggleSelectAll(this.checked);
            });
        }

        if (selectAllMobile) {
            selectAllMobile.addEventListener('change', function () {
                toggleSelectAll(this.checked);
            });
        }

        document.addEventListener('change', function (e) {
            if (e.target && e.target.classList.contains('journal-item-checkbox')) {
                const val = e.target.value;
                const isChecked = e.target.checked;
                // Sinkronkan checkbox desktop & mobile yang memiliki value sama
                document.querySelectorAll(`.journal-item-checkbox[value="${val}"]`).forEach(c => {
                    c.checked = isChecked;
                });
                updateSelectionUI();
            }
        });

        window.deselectAllJournals = function () {
            toggleSelectAll(false);
        };

        window.openBulkVerifyModal = function (status) {
            const checkedIds = getCheckedIds();
            if (checkedIds.length === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Perhatian', 'Pilih minimal satu jurnal untuk diverifikasi.', 'warning');
                } else {
                    alert('Pilih minimal satu jurnal untuk diverifikasi.');
                }
                return;
            }

            // Masukkan ID yang dicentang ke dalam form hidden inputs
            const container = document.getElementById('bulkJournalIdsContainer');
            container.innerHTML = '';
            checkedIds.forEach(id => {
                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'journal_ids[]';
                input.value = id;
                container.appendChild(input);
            });

            document.getElementById('bulkStatusInput').value       = status;
            document.getElementById('bulkModalItemCount').textContent = checkedIds.length;

            const modalTitle      = document.getElementById('bulkModalTitle');
            const modalSubtitle   = document.getElementById('bulkModalSubtitle');
            const iconWrapper     = document.getElementById('bulkModalIconWrapper');
            const actionNotice    = document.getElementById('bulkModalActionNotice');
            const detailedNotice  = document.getElementById('bulkModalDetailedNotice');
            const catatanGroup    = document.getElementById('bulkCatatanGroup');
            const catatanLabel    = document.getElementById('bulkCatatanLabel');
            const catatanBadge    = document.getElementById('bulkCatatanBadge');
            const catatanInput    = document.getElementById('bulkCatatanVerifikasi');
            const submitBtn       = document.getElementById('bulkModalSubmitBtn');

            catatanInput.value = '';

            const cfg = {
                disetujui: {
                    title:   'Setujui Jurnal Sekaligus',
                    sub:     'Menyetujui semua jurnal yang telah dipilih',
                    iconBg:  'rgba(16, 185, 129, 0.12)',
                    iconClr: '#10b981',
                    notice:  'Seluruh jurnal yang dipilih akan disetujui.',
                    detail:  'Siswa akan melihat status jurnal mereka telah disetujui oleh Guru Pembimbing.',
                    labelTxt:'Catatan / Apresiasi Guru',
                    badgeCls:'badge bg-secondary-light text-secondary fw-normal',
                    badgeTxt:'Opsional',
                    ph:      'Tulis pesan apresiasi atau umpan balik positif (opsional)...',
                    req:     false,
                    btnCls:  'btn btn-sm btn-success px-4 py-1.5 font-heading fw-semibold',
                    btnTxt:  `Ya, Setujui Semua (${checkedIds.length})`,
                },
                revisi: {
                    title:   'Minta Revisi Jurnal Sekaligus',
                    sub:     'Menginstruksikan perbaikan pada jurnal yang dipilih',
                    iconBg:  'rgba(245, 158, 11, 0.12)',
                    iconClr: '#f59e0b',
                    notice:  'Seluruh jurnal yang dipilih akan diminta untuk direvisi.',
                    detail:  'Siswa yang bersangkutan dapat mengubah dan mengirimkan ulang laporan kegiatan.',
                    labelTxt:'Catatan / Instruksi Revisi',
                    badgeCls:'badge bg-warning-light text-warning fw-semibold',
                    badgeTxt:'Wajib Diisi',
                    ph:      'Tuliskan poin-poin yang perlu diperbaiki oleh siswa...',
                    req:     true,
                    btnCls:  'btn btn-sm btn-warning text-white px-4 py-1.5 font-heading fw-semibold',
                    btnTxt:  `Minta Revisi (${checkedIds.length})`,
                    btnStyle:'background-color:#f59e0b;border-color:#f59e0b;',
                },
                ditolak: {
                    title:   'Tolak Jurnal Sekaligus',
                    sub:     'Menolak laporan jurnal yang dipilih',
                    iconBg:  'rgba(239, 68, 68, 0.12)',
                    iconClr: '#ef4444',
                    notice:  'Seluruh jurnal yang dipilih akan ditolak.',
                    detail:  'Siswa tidak dapat mengubah jurnal berstatus ditolak kecuali dibatalkan oleh guru.',
                    labelTxt:'Alasan Penolakan',
                    badgeCls:'badge bg-danger-light text-danger fw-semibold',
                    badgeTxt:'Wajib Diisi',
                    ph:      'Tuliskan alasan penolakan jurnal ini...',
                    req:     true,
                    btnCls:  'btn btn-sm btn-danger px-4 py-1.5 font-heading fw-semibold',
                    btnTxt:  `Tolak Jurnal (${checkedIds.length})`,
                },
                pending: {
                    title:   'Kembalikan ke Status Menunggu',
                    sub:     'Reset status verifikasi jurnal menjadi Pending',
                    iconBg:  'rgba(100, 116, 139, 0.1)',
                    iconClr: '#64748b',
                    notice:  'Status verifikasi akan dikembalikan ke Menunggu (Pending).',
                    detail:  'Catatan verifikasi sebelumnya akan dihapus dan jurnal kembali berstatus belum diverifikasi.',
                    labelTxt:'',
                    badgeCls:'',
                    badgeTxt:'',
                    ph:      '',
                    req:     false,
                    hideCatatan: true,
                    btnCls:  'btn btn-sm btn-secondary px-4 py-1.5 font-heading fw-semibold',
                    btnTxt:  `Kembalikan ke Pending (${checkedIds.length})`,
                },
            };

            const c = cfg[status];
            if (!c) return;

            modalTitle.textContent              = c.title;
            modalSubtitle.textContent           = c.sub;
            iconWrapper.style.backgroundColor   = c.iconBg;
            iconWrapper.style.color             = c.iconClr;
            actionNotice.textContent            = c.notice;
            detailedNotice.textContent          = c.detail;
            submitBtn.className                 = c.btnCls;
            submitBtn.style.cssText             = c.btnStyle || '';
            submitBtn.textContent               = c.btnTxt;
            catatanInput.required               = c.req;

            if (c.hideCatatan) {
                catatanGroup.style.display = 'none';
            } else {
                catatanGroup.style.display = 'block';
                catatanLabel.querySelector('span:first-child').textContent = c.labelTxt;
                catatanBadge.className    = c.badgeCls;
                catatanBadge.textContent  = c.badgeTxt;
                catatanInput.placeholder  = c.ph;
            }

            const modal = new bootstrap.Modal(document.getElementById('bulkVerifyModal'));
            modal.show();
        };
    });
</script>
@endsection