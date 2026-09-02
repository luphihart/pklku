@extends('layouts.admin')

@section('title', 'Hari Libur Nasional - PKLku')
@section('page_title', 'Hari Libur & Tanggal Merah')

@section('content')
<div class="container-fluid p-0">
    <!-- Header & Action Toolbar -->
    <div class="card-premium mb-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
                <h5 class="fw-bold font-heading m-0 text-dark">Manajemen Hari Libur & Tanggal Merah</h5>
                <p class="text-secondary small m-0">Hari libur terdaftar otomatis dikecualikan dari presensi harian, cron auto-absent, dan perhitungan kehadiran rapor.</p>
            </div>
            
            <div class="d-flex align-items-center gap-2 flex-wrap w-100 w-sm-auto justify-content-sm-end">
                <!-- Year Filter Form -->
                <form action="{{ route('hari-libur.index') }}" method="GET" class="d-flex align-items-center gap-2">
                    <label for="filterYear" class="small fw-semibold text-secondary text-nowrap d-none d-sm-inline">Tahun:</label>
                    <select name="year" id="filterYear" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 100px;">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>

                <!-- Auto Sync Button -->
                <form action="{{ route('hari-libur.sync_national') }}" method="POST" id="syncNationalForm" class="flex-grow-1 flex-sm-grow-0">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="button" class="btn btn-sm btn-outline-primary font-heading fw-semibold d-inline-flex align-items-center justify-content-center gap-1 w-100 w-sm-auto" onclick="window.confirmAction({ title: 'Sinkronisasi Libur Nasional {{ $year }}?', text: 'Sistem akan otomatis menambahkan daftar hari libur nasional resmi Indonesia untuk tahun {{ $year }}.', confirmButtonText: 'Ya, Sinkronkan' }).then(r => { if(r.isConfirmed) document.getElementById('syncNationalForm').submit(); });">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Sinkronkan Libur {{ $year }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Input Form Column -->
        <div class="col-lg-4 mb-4">
            <div class="card-premium">
                <h6 class="fw-bold font-heading mb-3 pb-2 border-bottom text-dark" style="border-bottom-color: var(--border-color) !important;">
                    Tambah Hari Libur
                </h6>

                <form action="{{ route('hari-libur.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="nama" class="form-label small fw-semibold text-secondary">Nama Libur / Hari Peringatan</label>
                        <input type="text" name="nama" id="nama" class="form-control form-control-sm" placeholder="Contoh: Hari Kemerdekaan RI" required>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="tanggal_mulai" class="form-label small fw-semibold text-secondary">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control form-control-sm" value="{{ $year }}-01-01" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="tanggal_selesai" class="form-label small fw-semibold text-secondary">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control form-control-sm" value="{{ $year }}-01-01">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label small fw-semibold text-secondary">Keterangan (Opsional)</label>
                        <textarea name="keterangan" id="keterangan" class="form-control form-control-sm" rows="3" placeholder="Contoh: Libur nasional resmi pemerintah atau cuti bersama"></textarea>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="is_nasional" value="1" id="is_nasional" checked>
                        <label class="form-check-label small" for="is_nasional">
                            Hari Libur Nasional / Tanggal Merah Resmi
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 font-heading fw-semibold py-2" data-loading-text="Menyimpan...">
                        Simpan Hari Libur
                    </button>
                </form>
            </div>
        </div>

        <!-- History List Column -->
        <div class="col-lg-8 mb-4">
            <div class="card-premium p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-bottom-color: var(--border-color) !important;">
                    <h6 class="fw-bold m-0 text-dark">Daftar Hari Libur Tahun {{ $year }}</h6>
                    <span class="badge bg-primary-light text-primary fw-semibold" style="font-size: 11px;">Total: {{ $holidays->total() }} Hari Libur</span>
                </div>

                <!-- Desktop Table View (md and up) -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px; min-width: 580px;">
                        <thead class="table-light">
                            <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                                <th class="ps-4" style="width: 140px;">Tanggal</th>
                                <th>Nama Hari Libur</th>
                                <th>Kategori</th>
                                <th class="text-center pe-4" style="width: 110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $h)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-primary d-flex align-items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ \Carbon\Carbon::parse($h->tanggal_mulai)->format('d/m/Y') }}
                                        </div>
                                        @if($h->tanggal_mulai != $h->tanggal_selesai)
                                            <small class="text-muted d-block ps-3">s/d {{ \Carbon\Carbon::parse($h->tanggal_selesai)->format('d/m/Y') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $h->nama }}</div>
                                        @if($h->keterangan)
                                            <small class="text-secondary d-block">{{ $h->keterangan }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($h->is_nasional)
                                            <span class="status-badge bg-danger-light text-danger">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                                Nasional
                                            </span>
                                        @else
                                            <span class="status-badge bg-info-light text-info">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                Khusus
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-outline-warning btn-action" data-bs-toggle="modal" data-bs-target="#editModal_{{ $h->id }}" title="Edit Hari Libur" aria-label="Edit Hari Libur {{ $h->nama }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <form action="{{ route('hari-libur.destroy', $h->id) }}" method="POST" id="deleteHolidayForm_{{ $h->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-action" title="Hapus Hari Libur" aria-label="Hapus Hari Libur {{ $h->nama }}" onclick="window.confirmDelete('deleteHolidayForm_{{ $h->id }}', 'hari libur {{ addslashes($h->nama) }}')">
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
                                    <td colspan="4" class="text-center py-4">
                                        <div class="empty-state py-4">
                                            <div class="empty-state-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <h6 class="empty-state-title">Belum Ada Hari Libur di Tahun {{ $year }}</h6>
                                            <p class="empty-state-text">Gunakan tombol <strong>"Sinkronkan Libur {{ $year }}"</strong> di atas untuk mengisi kalender otomatis atau gunakan form di sebelah kiri.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List View (Visible on smartphone < md) -->
                <div class="d-md-none p-3">
                    @forelse($holidays as $h)
                        <div class="card p-3 mb-3 border rounded shadow-xs" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                            <!-- Top: Tanggal & Kategori -->
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                <div class="d-flex align-items-center gap-1.5 text-primary fw-bold font-heading" style="font-size: 13px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>{{ \Carbon\Carbon::parse($h->tanggal_mulai)->translatedFormat('d M Y') }}</span>
                                    @if($h->tanggal_mulai != $h->tanggal_selesai)
                                        <span class="text-muted fw-normal" style="font-size: 11.5px;">s/d {{ \Carbon\Carbon::parse($h->tanggal_selesai)->translatedFormat('d M Y') }}</span>
                                    @endif
                                </div>
                                <div>
                                    @if($h->is_nasional)
                                        <span class="status-badge bg-danger-light text-danger" style="font-size: 10px;">
                                            Nasional
                                        </span>
                                    @else
                                        <span class="status-badge bg-info-light text-info" style="font-size: 10px;">
                                            Khusus
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Nama Libur & Keterangan -->
                            <div class="mb-2">
                                <h6 class="fw-bold font-heading text-dark mb-1" style="font-size: 13.5px;">{{ $h->nama }}</h6>
                                @if($h->keterangan)
                                    <p class="text-secondary small m-0" style="font-size: 12px; line-height: 1.4;">{{ $h->keterangan }}</p>
                                @endif
                            </div>

                            <!-- Footer: Action buttons -->
                            <div class="pt-2 border-top d-flex align-items-center justify-content-end gap-2" style="border-top-color: var(--border-color) !important;">
                                <button type="button" class="btn btn-sm btn-outline-warning font-heading d-flex align-items-center gap-1 px-2.5 py-1" data-bs-toggle="modal" data-bs-target="#editModal_{{ $h->id }}" style="font-size: 12px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Edit</span>
                                </button>
                                <form action="{{ route('hari-libur.destroy', $h->id) }}" method="POST" id="deleteHolidayFormMob_{{ $h->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger px-2.5 py-1" title="Hapus Hari Libur" onclick="window.confirmDelete('deleteHolidayFormMob_{{ $h->id }}', 'hari libur {{ addslashes($h->nama) }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-4 text-center">
                            <h6 class="empty-state-title">Belum Ada Hari Libur di Tahun {{ $year }}</h6>
                            <p class="empty-state-text">Gunakan tombol <strong>"Sinkronkan Libur {{ $year }}"</strong> di atas untuk mengisi kalender otomatis atau gunakan form di atas.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Modals Edit -->
                @foreach($holidays as $h)
                    <div class="modal fade text-start" id="editModal_{{ $h->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                    <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Edit Hari Libur</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('hari-libur.update', $h->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-secondary">Nama Hari Libur</label>
                                            <input type="text" name="nama" class="form-control form-control-sm" value="{{ $h->nama }}" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="form-label small fw-semibold text-secondary">Tanggal Mulai</label>
                                                <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($h->tanggal_mulai)->format('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="form-label small fw-semibold text-secondary">Tanggal Selesai</label>
                                                <input type="date" name="tanggal_selesai" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($h->tanggal_selesai)->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-secondary">Keterangan</label>
                                            <textarea name="keterangan" class="form-control form-control-sm" rows="3">{{ $h->keterangan }}</textarea>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="is_nasional" value="1" id="edit_is_nasional_{{ $h->id }}" {{ $h->is_nasional ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="edit_is_nasional_{{ $h->id }}">
                                                Hari Libur Nasional / Tanggal Merah Resmi
                                            </label>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-sm btn-primary" data-loading-text="Menyimpan...">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($holidays->hasPages())
                <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
                    {{ $holidays->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
