@extends('layouts.admin')

@section('title', 'Plotting Penempatan - PKLku')
@section('page_title', 'Plotting Penempatan Murid')

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
    .student-item:hover {
        background-color: rgba(79, 70, 229, 0.05);
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">Plotting Penempatan Murid & Guru Pembimbing</h5>
        <div class="d-flex gap-2 mt-2 mt-sm-0 flex-wrap">
            <!-- Export Excel Button -->
            <a href="{{ route('penempatan.export', request()->query()) }}" class="btn btn-sm btn-outline-success d-flex align-items-center">
                <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Ekspor Excel
            </a>
            <!-- Trigger Mass Plot Modal -->
            <button class="btn btn-sm btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#massPlotModal">
                <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Plotting Massal Murid
            </button>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card-premium mb-3 p-3">
        <form action="{{ route('penempatan.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3 col-lg-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-0" placeholder="Cari NIS, nama, DUDI, guru..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2 col-lg-2">
                <select name="dudi_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua DUDI --</option>
                    @foreach($dudis as $d)
                        <option value="{{ $d->id }}" {{ request('dudi_id') == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-lg-2">
                <select name="guru_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Guru --</option>
                    @foreach($gurus as $g)
                        <option value="{{ $g->id }}" {{ request('guru_id') == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-3">
                <select name="sort_by_order" class="form-select form-select-sm" onchange="
                    const val = this.value.split(':');
                    document.getElementById('penempatan_sort_by').value = val[0];
                    document.getElementById('penempatan_order').value = val[1];
                    this.form.submit();
                ">
                    <option value="nama:asc" {{ (request('sort_by') == 'nama' && request('order', 'asc') == 'asc') ? 'selected' : '' }}>Nama (A ➔ Z)</option>
                    <option value="nama:desc" {{ (request('sort_by') == 'nama' && request('order') == 'desc') ? 'selected' : '' }}>Nama (Z ➔ A)</option>
                    <option value="nis:asc" {{ (request('sort_by') == 'nis' && request('order', 'asc') == 'asc') ? 'selected' : '' }}>NIS (Terkecil ➔ Terbesar)</option>
                    <option value="nis:desc" {{ (request('sort_by') == 'nis' && request('order') == 'desc') ? 'selected' : '' }}>NIS (Terbesar ➔ Terkecil)</option>
                    <option value="kelas:asc" {{ (request('sort_by') == 'kelas' && request('order', 'asc') == 'asc') ? 'selected' : '' }}>Kelas (A ➔ Z)</option>
                    <option value="kelas:desc" {{ (request('sort_by') == 'kelas' && request('order') == 'desc') ? 'selected' : '' }}>Kelas (Z ➔ A)</option>
                    <option value="dudi:asc" {{ (request('sort_by') == 'dudi' && request('order', 'asc') == 'asc') ? 'selected' : '' }}>Tempat DUDI (A ➔ Z)</option>
                    <option value="dudi:desc" {{ (request('sort_by') == 'dudi' && request('order') == 'desc') ? 'selected' : '' }}>Tempat DUDI (Z ➔ A)</option>
                    <option value="guru:asc" {{ (request('sort_by') == 'guru' && request('order', 'asc') == 'asc') ? 'selected' : '' }}>Guru Pembimbing (A ➔ Z)</option>
                    <option value="guru:desc" {{ (request('sort_by') == 'guru' && request('order') == 'desc') ? 'selected' : '' }}>Guru Pembimbing (Z ➔ A)</option>
                </select>
                <input type="hidden" name="sort_by" id="penempatan_sort_by" value="{{ request('sort_by') }}">
                <input type="hidden" name="order" id="penempatan_order" value="{{ request('order', 'asc') }}">
            </div>
            <div class="col-md-2 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary px-3">Cari</button>
                @if(request()->filled('search') || request()->filled('dudi_id') || request()->filled('guru_id') || request()->filled('sort_by') || request()->filled('order'))
                    <a href="{{ route('penempatan.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Active Placements Table -->
    <div class="card-premium p-0 overflow-hidden">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-bottom-color: var(--border-color) !important;">
            <h6 class="fw-bold m-0 text-dark">Daftar Penempatan Aktif</h6>
            <button type="button" id="btnDeleteSelected" class="btn btn-xs btn-danger font-heading fw-bold btn-bulk-action" style="display: none;" onclick="
                const count = document.querySelectorAll('.row-checkbox:checked').length;
                window.confirmAction({
                    title: 'Batalkan ' + count + ' Penempatan?',
                    text: 'Penempatan murid terpilih akan dibatalkan/dihapus dari sistem.',
                    icon: 'warning',
                    confirmButtonColor: '#e11d48',
                    confirmButtonText: 'Ya, Hapus'
                }).then(r => {
                    if(r.isConfirmed) {
                        const form = document.getElementById('bulkDeleteForm');
                        form.action = '{{ route('penempatan.destroy_bulk') }}';
                        form.submit();
                    }
                });
            ">
                <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: inline-block; vertical-align: middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Terpilih
            </button>
        </div>

        <form action="{{ route('penempatan.destroy_bulk') }}" method="POST" id="bulkDeleteForm">
            @csrf
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="color: var(--text-primary);">
                    <thead class="table-light" style="background-color: var(--bg-canvas);">
                        <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                            <th class="ps-4" style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                            <th>
                                <a href="{{ route('penempatan.index', array_merge(request()->query(), ['sort_by' => 'nis', 'order' => (request('sort_by') === 'nis' && request('order', 'asc') === 'asc') ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark dark-text-light d-inline-flex align-items-center gap-1" title="Klik untuk mengurutkan NIS">
                                    NIS
                                    @if(request('sort_by') === 'nis')
                                        @if(request('order', 'asc') === 'desc')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        @endif
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted opacity-50"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('penempatan.index', array_merge(request()->query(), ['sort_by' => 'nama', 'order' => (request('sort_by') === 'nama' && request('order', 'asc') === 'asc') ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark dark-text-light d-inline-flex align-items-center gap-1" title="Klik untuk mengurutkan Nama Murid">
                                    Nama
                                    @if(request('sort_by') === 'nama')
                                        @if(request('order', 'asc') === 'desc')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        @endif
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted opacity-50"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('penempatan.index', array_merge(request()->query(), ['sort_by' => 'kelas', 'order' => (request('sort_by') === 'kelas' && request('order', 'asc') === 'asc') ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark dark-text-light d-inline-flex align-items-center gap-1" title="Klik untuk mengurutkan Kelas">
                                    Kelas
                                    @if(request('sort_by') === 'kelas')
                                        @if(request('order', 'asc') === 'desc')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        @endif
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted opacity-50"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('penempatan.index', array_merge(request()->query(), ['sort_by' => 'dudi', 'order' => (request('sort_by') === 'dudi' && request('order', 'asc') === 'asc') ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark dark-text-light d-inline-flex align-items-center gap-1" title="Klik untuk mengurutkan Tempat DUDI">
                                    Tempat DUDI
                                    @if(request('sort_by') === 'dudi')
                                        @if(request('order', 'asc') === 'desc')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        @endif
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted opacity-50"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('penempatan.index', array_merge(request()->query(), ['sort_by' => 'guru', 'order' => (request('sort_by') === 'guru' && request('order', 'asc') === 'asc') ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark dark-text-light d-inline-flex align-items-center gap-1" title="Klik untuk mengurutkan Guru Pembimbing">
                                    Guru Pembimbing
                                    @if(request('sort_by') === 'guru')
                                        @if(request('order', 'asc') === 'desc')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        @endif
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted opacity-50"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    @endif
                                </a>
                            </th>
                            <th>Pembimbing Industri</th>
                            <th class="text-center">Model Kerja</th>
                            <th class="text-center">Tanggal Pelaksanaan</th>
                            <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13px;">
                        @forelse($placements as $p)
                            <tr>
                                <td class="ps-4"><input type="checkbox" name="ids[]" value="{{ $p->id }}" class="row-checkbox"></td>
                                <td class="fw-semibold">{{ $p->murid ? $p->murid->nis : '-' }}</td>
                                <td class="fw-bold text-dark">{{ $p->murid ? $p->murid->nama : 'Murid Terhapus' }}</td>
                                <td><span class="badge bg-secondary">{{ $p->murid && $p->murid->kelas ? $p->murid->kelas->nama : '-' }}</span></td>
                                <td><span class="fw-semibold text-primary">{{ $p->dudi ? $p->dudi->nama : 'DUDI Terhapus' }}</span></td>
                                <td>{{ $p->guru ? $p->guru->nama : 'Guru Terhapus' }}</td>
                                <td>{{ $p->pembimbingIndustri ? $p->pembimbingIndustri->nama : ($p->dudi?->pic_nama ?? '-') }}</td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap mb-1">
                                        @if(($p->tipe_kerja ?? 'wfo') === 'wfa')
                                            <span class="badge bg-primary-light text-primary fw-semibold" title="100% WFA (Full Remote)">🏠 WFA</span>
                                        @elseif(($p->tipe_kerja ?? 'wfo') === 'hybrid')
                                            <span class="badge bg-info-light text-info fw-semibold" title="Hybrid: WFA pada {{ $p->hari_wfa }}">🔄 Hybrid ({{ $p->hari_wfa }})</span>
                                        @else
                                            <span class="badge bg-secondary-light text-secondary fw-semibold" title="100% WFO (Di Kantor DUDI)">🏢 WFO</span>
                                        @endif

                                        @php
                                            $shiftInfo = $p->getEffectiveShiftHours();
                                        @endphp
                                        @if(($p->tipe_shift ?? 'reguler') === 'rolling')
                                            <span class="badge bg-purple-light text-purple fw-semibold" style="background-color: rgba(147, 51, 234, 0.12); color: #9333ea;" title="Rolling Shift (Auto-Detect)">🔄 Rolling</span>
                                        @elseif(($p->tipe_shift ?? 'reguler') === 'pagi')
                                            <span class="badge bg-success-light text-success fw-semibold" title="{{ $shiftInfo['label'] }}">🌅 Pagi</span>
                                        @elseif(($p->tipe_shift ?? 'reguler') === 'siang')
                                            <span class="badge bg-warning-light text-warning fw-semibold" title="{{ $shiftInfo['label'] }}">🌆 Siang</span>
                                        @elseif(($p->tipe_shift ?? 'reguler') === 'sore')
                                            <span class="badge bg-orange-light text-orange fw-semibold" style="background-color: rgba(249, 115, 22, 0.12); color: #ea580c;" title="{{ $shiftInfo['label'] }}">🌇 Sore</span>
                                        @elseif(($p->tipe_shift ?? 'reguler') === 'custom')
                                            <span class="badge bg-indigo-light text-indigo fw-semibold" style="background-color: rgba(79, 70, 229, 0.1); color: #4f46e5;" title="Kustom: {{ $shiftInfo['jam_masuk'] }} - {{ $shiftInfo['jam_pulang'] }}">⚙️ {{ $shiftInfo['jam_masuk'] }}-{{ $shiftInfo['jam_pulang'] }}</span>
                                        @endif
                                    </div>
                                    @if(!empty($p->hari_libur))
                                        <small class="text-muted d-block" style="font-size: 11px;">Libur: {{ $p->hari_libur }}</small>
                                    @endif
                                </td>
                                <td class="text-center text-muted">
                                    {{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/y') }} s/d {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/y') }}
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-action" data-bs-toggle="modal" data-bs-target="#editModal_{{ $p->id }}" title="Edit Penempatan" aria-label="Edit Penempatan {{ $p->murid?->nama ?? 'Siswa' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" title="Hapus Penempatan" aria-label="Hapus Penempatan {{ $p->murid?->nama ?? 'Siswa' }}" onclick="window.confirmDelete('deleteForm_{{ $p->id }}', 'penempatan {{ addslashes($p->murid?->nama ?? 'murid') }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="empty-state py-4">
                                        <div class="empty-state-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            </svg>
                                        </div>
                                        <h6 class="empty-state-title">Belum Ada Penempatan Aktif</h6>
                                        <p class="empty-state-text">Gunakan tombol Plotting Massal Murid di atas untuk menempatkan siswa ke DUDI dan guru pembimbing.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
        
        @if($placements->hasPages())
        <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
            {{ $placements->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal: Plotting Massal -->
<div class="modal fade" id="massPlotModal" tabindex="-1" aria-labelledby="massPlotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                <h5 class="modal-title font-heading fw-bold" id="massPlotModalLabel">Plotting Penempatan Murid Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('penempatan.store_massal') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Step 1: Select Class & Students -->
                    <div class="mb-4">
                        <label for="classFilterSelect" class="form-label small fw-semibold text-uppercase text-muted" style="letter-spacing: 0.5px;">1. Pilih Kelas Murid</label>
                        <select id="classFilterSelect" class="form-select form-select-sm mb-3">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasOptions as $kls)
                                <option value="{{ $kls->id }}">{{ $kls->nama }}</option>
                            @endforeach
                        </select>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-semibold text-uppercase text-muted mb-0" style="letter-spacing: 0.5px;">Pilih Murid (Dapat memilih lebih dari satu)</label>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge bg-primary" id="selectedStudentsCount" style="font-size: 11px;">0 Terpilih</span>
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" id="btnSelectAllInClass" style="font-size: 11px; display: none;">Pilih Semua</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" id="btnUnselectAllInClass" style="font-size: 11px; display: none;">Batal Pilih</button>
                            </div>
                        </div>

                        <!-- Live search input for students -->
                        <div class="input-group input-group-sm mb-2" id="muridSearchInputGroup" style="display: none;">
                            <span class="input-group-text bg-transparent border-end-0 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </span>
                            <input type="text" id="searchMuridInput" class="form-control form-control-sm border-start-0 ps-0" placeholder="Ketik nama atau NIS murid...">
                        </div>

                        <div class="border rounded p-3" style="max-height: 190px; overflow-y: auto; background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                            @forelse($kelasOptions as $kls)
                                <div class="class-student-list" id="class_students_{{ $kls->id }}" style="display: none;">
                                    @php
                                        $studentsInClass = $unassignedStudents->where('kelas_id', $kls->id);
                                    @endphp
                                    @foreach($studentsInClass as $s)
                                        <div class="form-check mb-2 p-1 px-2 student-item" data-name="{{ strtolower($s->nama) }}" data-nis="{{ strtolower($s->nis) }}">
                                            <input class="form-check-input student-checkbox" type="checkbox" name="murid_ids[]" value="{{ $s->id }}" id="checkMurid_{{ $s->id }}">
                                            <label class="form-check-label text-dark dark-text-light small w-100" for="checkMurid_{{ $s->id }}" style="cursor: pointer;">
                                                <strong>{{ $s->nama }}</strong> <span class="text-muted">({{ $s->nis }})</span>
                                            </label>
                                        </div>
                                    @endforeach
                                    <div class="no-student-match text-center text-muted py-2 small" style="display: none;">
                                        Tidak ada murid yang cocok dengan pencarian nama/NIS.
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3 small" id="noStudentsPlaceholder">
                                    Semua murid sudah ter-plotting untuk periode aktif ini.
                                </div>
                            @endforelse
                            @if(count($kelasOptions) > 0)
                                <div class="text-center text-muted py-3 small" id="selectClassPlaceholder">
                                    Silakan pilih kelas terlebih dahulu untuk menampilkan daftar murid.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Step 2: Select Dudi & Guru -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-semibold">2. Pilih Mitra DUDI</label>
                            <div class="searchable-select-wrapper position-relative" id="dudiSearchWrapper">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </span>
                                    <input type="text" id="dudiSearchInput" class="form-control form-control-sm border-start-0 ps-0 searchable-input" placeholder="Ketik nama atau pilih Mitra DUDI..." autocomplete="off">
                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" id="dudiDropdownToggle"></button>
                                </div>
                                <select name="dudi_id" id="dudiSelect" class="d-none" required>
                                    <option value="">-- Pilih Mitra DUDI --</option>
                                    @foreach($dudis as $d)
                                        <option value="{{ $d->id }}">{{ $d->nama }}</option>
                                    @endforeach
                                </select>
                                <div class="searchable-options-list shadow border rounded mt-1 position-absolute w-100" id="dudiOptionsList" style="display: none; max-height: 200px; overflow-y: auto; z-index: 1060; background-color: var(--bg-card, #ffffff); border-color: var(--border-color, #e2e8f0);">
                                    @foreach($dudis as $d)
                                        <div class="searchable-option-item px-3 py-2 border-bottom small" data-value="{{ $d->id }}" data-label="{{ $d->nama }}" data-sub="{{ $d->alamat ?? '' }}" style="cursor: pointer;">
                                            <div class="fw-semibold text-dark">{{ $d->nama }}</div>
                                            @if($d->alamat)
                                                <small class="text-muted d-block text-truncate" style="font-size: 11px;">{{ Str::limit($d->alamat, 60) }}</small>
                                            @endif
                                        </div>
                                    @endforeach
                                    <div class="no-options-found px-3 py-2 text-muted small text-center" style="display: none;">Mitra DUDI tidak ditemukan</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-semibold">3. Pilih Guru Pembimbing</label>
                            <div class="searchable-select-wrapper position-relative" id="guruSearchWrapper">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </span>
                                    <input type="text" id="guruSearchInput" class="form-control form-control-sm border-start-0 ps-0 searchable-input" placeholder="Ketik nama atau pilih Guru Pembimbing..." autocomplete="off">
                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" id="guruDropdownToggle"></button>
                                </div>
                                <select name="guru_id" id="guruSelect" class="d-none" required>
                                    <option value="">-- Pilih Guru Pembimbing --</option>
                                    @foreach($gurus as $g)
                                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                                    @endforeach
                                </select>
                                <div class="searchable-options-list shadow border rounded mt-1 position-absolute w-100" id="guruOptionsList" style="display: none; max-height: 200px; overflow-y: auto; z-index: 1060; background-color: var(--bg-card, #ffffff); border-color: var(--border-color, #e2e8f0);">
                                    @foreach($gurus as $g)
                                        <div class="searchable-option-item px-3 py-2 border-bottom small" data-value="{{ $g->id }}" data-label="{{ $g->nama }}" data-sub="{{ $g->nip ?? '' }}" style="cursor: pointer;">
                                            <div class="fw-semibold text-dark">{{ $g->nama }}</div>
                                            @if($g->nip)
                                                <small class="text-muted d-block" style="font-size: 11px;">NIP: {{ $g->nip }}</small>
                                            @endif
                                        </div>
                                    @endforeach
                                    <div class="no-options-found px-3 py-2 text-muted small text-center" style="display: none;">Guru tidak ditemukan</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Dates -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_mulai" class="form-label small fw-semibold">Tanggal Mulai PKL</label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_selesai" class="form-label small fw-semibold">Tanggal Selesai PKL</label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    <!-- Step 5: Work Model (WFO/WFA/Hybrid) -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-semibold">5. Model Kerja Siswa</label>
                            <div class="d-flex gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_kerja" id="mass_tipe_wfo" value="wfo" checked onchange="toggleMassHybridDays(this.value)">
                                    <label class="form-check-label small" for="mass_tipe_wfo">
                                        🏢 100% WFO (Di Kantor DUDI)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_kerja" id="mass_tipe_wfa" value="wfa" onchange="toggleMassHybridDays(this.value)">
                                    <label class="form-check-label small" for="mass_tipe_wfa">
                                        🏠 100% WFA (Full Remote)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_kerja" id="mass_tipe_hybrid" value="hybrid" onchange="toggleMassHybridDays(this.value)">
                                    <label class="form-check-label small" for="mass_tipe_hybrid">
                                        🔄 Hybrid (Kombinasi)
                                    </label>
                                </div>
                            </div>
                            <div id="massHybridDaysContainer" class="p-2 border rounded bg-light" style="display: none;">
                                <span class="small text-muted d-block mb-1 fw-semibold">Pilih Hari Saat Siswa WFA (Bebas Radius):</span>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                                        <div class="form-check form-check-inline m-0">
                                            <input class="form-check-input" type="checkbox" name="hari_wfa[]" id="mass_hari_{{ $hari }}" value="{{ $hari }}">
                                            <label class="form-check-label small" for="mass_hari_{{ $hari }}">{{ $hari }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 6: Custom Weekly Holidays / Days Off -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-semibold">6. Pengaturan Hari Libur Rutin Mingguan Siswa</label>
                            <small class="text-muted d-block mb-2" style="font-size: 12px;">Pilih hari ketika siswa <strong>libur (tidak wajib presensi & tidak terhitung Alpha)</strong>. <em>(Default: Sabtu & Minggu)</em></small>
                            <div class="d-flex flex-wrap gap-3 p-2 border rounded" style="background-color: var(--bg-canvas);">
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hLibur)
                                    <div class="form-check form-check-inline m-0">
                                        <input class="form-check-input" type="checkbox" name="hari_libur[]" id="mass_libur_{{ $hLibur }}" value="{{ $hLibur }}" {{ in_array($hLibur, ['Sabtu', 'Minggu']) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="mass_libur_{{ $hLibur }}">{{ $hLibur }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Step 7: Shift & Working Hours -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-semibold">7. Pengaturan Shift & Jam Kerja Siswa</label>
                            <small class="text-muted d-block mb-2" style="font-size: 12px;">Pilih template shift standar sekolah atau atur jam kerja mandiri khusus untuk penempatan ini:</small>
                            <div class="d-flex flex-column gap-2 mb-3">
                                <!-- Baris 1: Reguler -->
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_shift" id="mass_shift_reguler" value="reguler" checked onchange="toggleMassShiftCustom(this.value)">
                                        <label class="form-check-label small fw-semibold" for="mass_shift_reguler">
                                            🏢 Reguler
                                        </label>
                                    </div>
                                </div>
                                <!-- Baris 2: Shift Pagi, Siang, Sore -->
                                <div class="d-flex flex-wrap align-items-center gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_shift" id="mass_shift_pagi" value="pagi" onchange="toggleMassShiftCustom(this.value)">
                                        <label class="form-check-label small fw-semibold" for="mass_shift_pagi">
                                            🌅 Shift Pagi
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_shift" id="mass_shift_siang" value="siang" onchange="toggleMassShiftCustom(this.value)">
                                        <label class="form-check-label small fw-semibold" for="mass_shift_siang">
                                            🌆 Shift Siang
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_shift" id="mass_shift_sore" value="sore" onchange="toggleMassShiftCustom(this.value)">
                                        <label class="form-check-label small fw-semibold" for="mass_shift_sore">
                                            🌇 Shift Sore
                                        </label>
                                    </div>
                                </div>
                                <!-- Baris 3: Rolling, Kustom Jam -->
                                <div class="d-flex flex-wrap align-items-center gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_shift" id="mass_shift_rolling" value="rolling" onchange="toggleMassShiftCustom(this.value)">
                                        <label class="form-check-label small fw-semibold" for="mass_shift_rolling">
                                            🔄 Rolling (Auto-Detect)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_shift" id="mass_shift_custom" value="custom" onchange="toggleMassShiftCustom(this.value)">
                                        <label class="form-check-label small fw-semibold" for="mass_shift_custom">
                                            ⚙️ Kustom Jam
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Custom hours fields -->
                            <div id="massShiftCustomContainer" class="p-3 border rounded bg-light" style="display: none;">
                                <div class="row g-2">
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small fw-semibold">Buka Jam Masuk</label>
                                        <input type="text" name="jam_masuk" class="form-control form-control-sm" placeholder="08:00">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small fw-semibold">Batas Terlambat</label>
                                        <input type="text" name="batas_terlambat" class="form-control form-control-sm" placeholder="08:15">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small fw-semibold">Buka Jam Pulang</label>
                                        <input type="text" name="jam_pulang" class="form-control form-control-sm" placeholder="16:00">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small fw-semibold">Tutup Jam Pulang</label>
                                        <input type="text" name="tutup_jam_pulang" class="form-control form-control-sm" placeholder="22:00">
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block" style="font-size: 11px;">Format waktu 24 jam (HH:mm, contoh: 08:00).</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Plotting</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($placements as $p)
    <!-- Modal Edit -->
    <div class="modal fade" id="editModal_{{ $p->id }}" tabindex="-1" aria-labelledby="editModalLabel_{{ $p->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                    <h5 class="modal-title font-heading fw-bold" id="editModalLabel_{{ $p->id }}" style="font-size: 15px;">Edit Detail Penempatan PKL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('penempatan.update', $p->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Nama Murid</label>
                            <input type="text" class="form-control" value="{{ $p->murid ? $p->murid->nama : 'Murid Terhapus' }} ({{ $p->murid ? $p->murid->nis : '-' }})" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="dudiSelect_{{ $p->id }}" class="form-label small fw-semibold">Mitra DUDI</label>
                            <select name="dudi_id" id="dudiSelect_{{ $p->id }}" class="form-select form-select-sm" required>
                                @foreach($dudis as $d)
                                    <option value="{{ $d->id }}" {{ $p->dudi_id == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="guruSelect_{{ $p->id }}" class="form-label small fw-semibold">Guru Pembimbing</label>
                            <select name="guru_id" id="guruSelect_{{ $p->id }}" class="form-select form-select-sm" required>
                                @foreach($gurus as $g)
                                    <option value="{{ $g->id }}" {{ $p->guru_id == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="tglMulai_{{ $p->id }}" class="form-label small fw-semibold">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" id="tglMulai_{{ $p->id }}" class="form-control form-control-sm" value="{{ $p->tanggal_mulai }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tglSelesai_{{ $p->id }}" class="form-label small fw-semibold">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" id="tglSelesai_{{ $p->id }}" class="form-control form-control-sm" value="{{ $p->tanggal_selesai }}" required>
                            </div>
                        </div>

                        @php
                            $currentTipe = $p->tipe_kerja ?? 'wfo';
                            $currentHariWfa = array_map('trim', explode(',', $p->hari_wfa ?? ''));
                        @endphp
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Model Kerja Siswa</label>
                            <div class="d-flex gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_kerja" id="edit_tipe_wfo_{{ $p->id }}" value="wfo" {{ $currentTipe === 'wfo' ? 'checked' : '' }} onchange="toggleEditHybridDays({{ $p->id }}, this.value)">
                                    <label class="form-check-label small" for="edit_tipe_wfo_{{ $p->id }}">
                                        🏢 WFO
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_kerja" id="edit_tipe_wfa_{{ $p->id }}" value="wfa" {{ $currentTipe === 'wfa' ? 'checked' : '' }} onchange="toggleEditHybridDays({{ $p->id }}, this.value)">
                                    <label class="form-check-label small" for="edit_tipe_wfa_{{ $p->id }}">
                                        🏠 WFA (Full)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_kerja" id="edit_tipe_hybrid_{{ $p->id }}" value="hybrid" {{ $currentTipe === 'hybrid' ? 'checked' : '' }} onchange="toggleEditHybridDays({{ $p->id }}, this.value)">
                                    <label class="form-check-label small" for="edit_tipe_hybrid_{{ $p->id }}">
                                        🔄 Hybrid
                                    </label>
                                </div>
                            </div>
                            <div id="editHybridDaysContainer_{{ $p->id }}" class="p-2 border rounded bg-light" style="{{ $currentTipe === 'hybrid' ? '' : 'display: none;' }}">
                                <span class="small text-muted d-block mb-1 fw-semibold">Pilih Hari Saat Siswa WFA (Bebas Radius):</span>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                                        <div class="form-check form-check-inline m-0">
                                            <input class="form-check-input" type="checkbox" name="hari_wfa[]" id="edit_hari_{{ $p->id }}_{{ $hari }}" value="{{ $hari }}" {{ in_array($hari, $currentHariWfa) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="edit_hari_{{ $p->id }}_{{ $hari }}">{{ $hari }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        @php
                            $currentHariLibur = array_map('trim', explode(',', $p->hari_libur ?? 'Sabtu,Minggu'));
                        @endphp
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Hari Libur Rutin Mingguan Siswa</label>
                            <small class="text-muted d-block mb-2" style="font-size: 12px;">Pilih hari ketika siswa <strong>libur (bebas presensi & tidak terhitung Alpha)</strong>:</small>
                            <div class="d-flex flex-wrap gap-3 p-2 border rounded" style="background-color: var(--bg-canvas);">
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hLibur)
                                    <div class="form-check form-check-inline m-0">
                                        <input class="form-check-input" type="checkbox" name="hari_libur[]" id="edit_libur_{{ $p->id }}_{{ $hLibur }}" value="{{ $hLibur }}" {{ in_array($hLibur, $currentHariLibur) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="edit_libur_{{ $p->id }}_{{ $hLibur }}">{{ $hLibur }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @php
                            $currentShift = $p->tipe_shift ?? 'reguler';
                        @endphp
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Pengaturan Shift & Jam Kerja Siswa</label>
                            <small class="text-muted d-block mb-2" style="font-size: 12px;">Pilih template shift standar sekolah atau atur jam kerja mandiri khusus:</small>
                            <div class="d-flex flex-column gap-2 mb-3">
                                <!-- Baris 1: Reguler -->
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_shift" id="edit_shift_reguler_{{ $p->id }}" value="reguler" {{ $currentShift === 'reguler' ? 'checked' : '' }} onchange="toggleEditShiftCustom({{ $p->id }}, this.value)">
                                        <label class="form-check-label small fw-semibold" for="edit_shift_reguler_{{ $p->id }}">
                                            🏢 Reguler
                                        </label>
                                    </div>
                                </div>
                                <!-- Baris 2: Shift Pagi, Siang, Sore -->
                                <div class="d-flex flex-wrap align-items-center gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_shift" id="edit_shift_pagi_{{ $p->id }}" value="pagi" {{ $currentShift === 'pagi' ? 'checked' : '' }} onchange="toggleEditShiftCustom({{ $p->id }}, this.value)">
                                        <label class="form-check-label small fw-semibold" for="edit_shift_pagi_{{ $p->id }}">
                                            🌅 Shift Pagi
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_shift" id="edit_shift_siang_{{ $p->id }}" value="siang" {{ $currentShift === 'siang' ? 'checked' : '' }} onchange="toggleEditShiftCustom({{ $p->id }}, this.value)">
                                        <label class="form-check-label small fw-semibold" for="edit_shift_siang_{{ $p->id }}">
                                            🌆 Shift Siang
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_shift" id="edit_shift_sore_{{ $p->id }}" value="sore" {{ $currentShift === 'sore' ? 'checked' : '' }} onchange="toggleEditShiftCustom({{ $p->id }}, this.value)">
                                        <label class="form-check-label small fw-semibold" for="edit_shift_sore_{{ $p->id }}">
                                            🌇 Shift Sore
                                        </label>
                                    </div>
                                </div>
                                <!-- Baris 3: Rolling, Kustom Jam -->
                                <div class="d-flex flex-wrap align-items-center gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_shift" id="edit_shift_rolling_{{ $p->id }}" value="rolling" {{ $currentShift === 'rolling' ? 'checked' : '' }} onchange="toggleEditShiftCustom({{ $p->id }}, this.value)">
                                        <label class="form-check-label small fw-semibold" for="edit_shift_rolling_{{ $p->id }}">
                                            🔄 Rolling (Auto-Detect)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_shift" id="edit_shift_custom_{{ $p->id }}" value="custom" {{ $currentShift === 'custom' ? 'checked' : '' }} onchange="toggleEditShiftCustom({{ $p->id }}, this.value)">
                                        <label class="form-check-label small fw-semibold" for="edit_shift_custom_{{ $p->id }}">
                                            ⚙️ Kustom Jam
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div id="editShiftCustomContainer_{{ $p->id }}" class="p-3 border rounded bg-light" style="{{ $currentShift === 'custom' ? '' : 'display: none;' }}">
                                <div class="row g-2">
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small fw-semibold">Jam Masuk</label>
                                        <input type="text" name="jam_masuk" class="form-control form-control-sm" placeholder="08:00" value="{{ $p->jam_masuk ? substr($p->jam_masuk, 0, 5) : '' }}">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small fw-semibold">Batas Telat</label>
                                        <input type="text" name="batas_terlambat" class="form-control form-control-sm" placeholder="08:15" value="{{ $p->batas_terlambat ? substr($p->batas_terlambat, 0, 5) : '' }}">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small fw-semibold">Jam Pulang</label>
                                        <input type="text" name="jam_pulang" class="form-control form-control-sm" placeholder="16:00" value="{{ $p->jam_pulang ? substr($p->jam_pulang, 0, 5) : '' }}">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small fw-semibold">Tutup Pulang</label>
                                        <input type="text" name="tutup_jam_pulang" class="form-control form-control-sm" placeholder="22:00" value="{{ $p->tutup_jam_pulang ? substr($p->tutup_jam_pulang, 0, 5) : '' }}">
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block" style="font-size: 11px;">Format waktu 24 jam (HH:mm, contoh: 08:00).</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden delete form for single delete -->
    <form action="{{ route('penempatan.destroy', $p->id) }}" method="POST" id="deleteForm_{{ $p->id }}" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endforeach
@endsection

@section('scripts')
<script>
    function toggleMassHybridDays(val) {
        const el = document.getElementById('massHybridDaysContainer');
        if (el) {
            el.style.display = (val === 'hybrid') ? 'block' : 'none';
        }
    }

    function toggleEditHybridDays(placementId, val) {
        const el = document.getElementById('editHybridDaysContainer_' + placementId);
        if (el) {
            el.style.display = (val === 'hybrid') ? 'block' : 'none';
        }
    }

    function toggleMassShiftCustom(val) {
        const el = document.getElementById('massShiftCustomContainer');
        if (el) {
            el.style.display = (val === 'custom') ? 'block' : 'none';
        }
    }

    function toggleEditShiftCustom(placementId, val) {
        const el = document.getElementById('editShiftCustomContainer_' + placementId);
        if (el) {
            el.style.display = (val === 'custom') ? 'block' : 'none';
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // --- 1. SEARCHABLE SELECT HELPER (DUDI & GURU) ---
        function initSearchableSelect(wrapperId, inputId, listId, selectId, toggleId) {
            const wrapper = document.getElementById(wrapperId);
            const input = document.getElementById(inputId);
            const list = document.getElementById(listId);
            const select = document.getElementById(selectId);
            const toggle = document.getElementById(toggleId);
            if (!wrapper || !input || !list || !select) return;

            const items = list.querySelectorAll('.searchable-option-item');
            const noFound = list.querySelector('.no-options-found');

            function openList() {
                list.style.display = 'block';
            }

            function closeList() {
                list.style.display = 'none';
            }

            function filterOptions() {
                const query = input.value.toLowerCase().trim();
                let matchCount = 0;
                items.forEach(item => {
                    const label = (item.getAttribute('data-label') || '').toLowerCase();
                    const sub = (item.getAttribute('data-sub') || '').toLowerCase();
                    if (label.includes(query) || sub.includes(query)) {
                        item.style.display = 'block';
                        matchCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });
                if (noFound) {
                    noFound.style.display = matchCount === 0 ? 'block' : 'none';
                }
                openList();
            }

            input.addEventListener('focus', function() {
                filterOptions();
            });

            input.addEventListener('input', function() {
                filterOptions();
                if (input.value.trim() === '') {
                    select.value = '';
                    items.forEach(it => it.classList.remove('active-selected'));
                }
            });

            if (toggle) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (list.style.display === 'block') {
                        closeList();
                    } else {
                        input.focus();
                        filterOptions();
                    }
                });
            }

            items.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const val = this.getAttribute('data-value');
                    const label = this.getAttribute('data-label');
                    select.value = val;
                    input.value = label;
                    
                    items.forEach(it => it.classList.remove('active-selected'));
                    this.classList.add('active-selected');
                    
                    closeList();
                });
            });

            // Close on click outside
            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) {
                    closeList();
                    if (select.value) {
                        const selectedItem = list.querySelector(`.searchable-option-item[data-value="${select.value}"]`);
                        if (selectedItem) {
                            input.value = selectedItem.getAttribute('data-label');
                        }
                    } else {
                        input.value = '';
                    }
                }
            });
        }

        // Initialize DUDI and Guru Searchable Dropdowns
        initSearchableSelect('dudiSearchWrapper', 'dudiSearchInput', 'dudiOptionsList', 'dudiSelect', 'dudiDropdownToggle');
        initSearchableSelect('guruSearchWrapper', 'guruSearchInput', 'guruOptionsList', 'guruSelect', 'guruDropdownToggle');

        // --- 2. CLASS & STUDENT FILTER LOGIC ---
        const classFilterSelect = document.getElementById('classFilterSelect');
        const selectClassPlaceholder = document.getElementById('selectClassPlaceholder');
        const searchMuridInput = document.getElementById('searchMuridInput');
        const muridSearchInputGroup = document.getElementById('muridSearchInputGroup');
        const selectedStudentsCount = document.getElementById('selectedStudentsCount');
        const btnSelectAllInClass = document.getElementById('btnSelectAllInClass');
        const btnUnselectAllInClass = document.getElementById('btnUnselectAllInClass');

        function updateSelectedCount() {
            const checked = document.querySelectorAll('.student-checkbox:checked').length;
            if (selectedStudentsCount) {
                selectedStudentsCount.textContent = checked + ' Terpilih';
            }
        }

        if (classFilterSelect) {
            classFilterSelect.addEventListener('change', function() {
                const selectedClassId = this.value;
                
                // Hide all class student lists
                document.querySelectorAll('.class-student-list').forEach(function(el) {
                    el.style.display = 'none';
                });
                
                if (searchMuridInput) searchMuridInput.value = '';
                
                if (selectedClassId) {
                    const targetList = document.getElementById('class_students_' + selectedClassId);
                    if (targetList) {
                        targetList.style.display = 'block';
                        targetList.querySelectorAll('.student-item').forEach(it => it.style.display = 'block');
                        const noMatch = targetList.querySelector('.no-student-match');
                        if (noMatch) noMatch.style.display = 'none';

                        if (selectClassPlaceholder) selectClassPlaceholder.style.display = 'none';
                        if (muridSearchInputGroup) muridSearchInputGroup.style.display = 'flex';
                        if (btnSelectAllInClass) btnSelectAllInClass.style.display = 'inline-block';
                        if (btnUnselectAllInClass) btnUnselectAllInClass.style.display = 'inline-block';
                    } else {
                        if (selectClassPlaceholder) selectClassPlaceholder.style.display = 'block';
                        if (muridSearchInputGroup) muridSearchInputGroup.style.display = 'none';
                        if (btnSelectAllInClass) btnSelectAllInClass.style.display = 'none';
                        if (btnUnselectAllInClass) btnUnselectAllInClass.style.display = 'none';
                    }
                } else {
                    if (selectClassPlaceholder) selectClassPlaceholder.style.display = 'block';
                    if (muridSearchInputGroup) muridSearchInputGroup.style.display = 'none';
                    if (btnSelectAllInClass) btnSelectAllInClass.style.display = 'none';
                    if (btnUnselectAllInClass) btnUnselectAllInClass.style.display = 'none';
                }
            });
        }

        // Live search filter for murid inside active class
        if (searchMuridInput) {
            searchMuridInput.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                const selectedClassId = classFilterSelect ? classFilterSelect.value : '';
                if (!selectedClassId) return;

                const targetList = document.getElementById('class_students_' + selectedClassId);
                if (!targetList) return;

                const studentItems = targetList.querySelectorAll('.student-item');
                let visibleCount = 0;
                studentItems.forEach(item => {
                    const name = (item.getAttribute('data-name') || '').toLowerCase();
                    const nis = (item.getAttribute('data-nis') || '').toLowerCase();
                    if (name.includes(q) || nis.includes(q)) {
                        item.style.display = 'block';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                const noMatch = targetList.querySelector('.no-student-match');
                if (noMatch) {
                    noMatch.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            });
        }

        // Select All visible students in active class
        if (btnSelectAllInClass) {
            btnSelectAllInClass.addEventListener('click', function() {
                const selectedClassId = classFilterSelect ? classFilterSelect.value : '';
                if (!selectedClassId) return;
                const targetList = document.getElementById('class_students_' + selectedClassId);
                if (!targetList) return;

                targetList.querySelectorAll('.student-item').forEach(item => {
                    if (item.style.display !== 'none') {
                        const cb = item.querySelector('.student-checkbox');
                        if (cb) cb.checked = true;
                    }
                });
                updateSelectedCount();
            });
        }

        // Unselect All students in active class
        if (btnUnselectAllInClass) {
            btnUnselectAllInClass.addEventListener('click', function() {
                const selectedClassId = classFilterSelect ? classFilterSelect.value : '';
                if (!selectedClassId) return;
                const targetList = document.getElementById('class_students_' + selectedClassId);
                if (!targetList) return;

                targetList.querySelectorAll('.student-checkbox').forEach(cb => {
                    cb.checked = false;
                });
                updateSelectedCount();
            });
        }

        // Student checkbox change listener
        document.querySelectorAll('.student-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });

        // --- 3. BULK DELETE TABLE LOGIC ---
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const btnDeleteSelected = document.getElementById('btnDeleteSelected');

        function toggleActionButtons() {
            const anyChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
            if (btnDeleteSelected) {
                btnDeleteSelected.style.display = anyChecked ? 'inline-block' : 'none';
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                rowCheckboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                toggleActionButtons();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
                if (selectAll) selectAll.checked = allChecked;
                toggleActionButtons();
            });
        });
    });
</script>
@endsection
