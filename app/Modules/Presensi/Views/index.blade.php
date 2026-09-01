@extends('layouts.admin')

@section('title', 'Riwayat Presensi - PKLku')
@section('page_title', 'Pemantauan Kehadiran Murid')

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

    <!-- Attendance Table -->
    <div class="card-premium p-0 overflow-hidden">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-bottom-color: var(--border-color) !important;">
            <h6 class="fw-bold m-0 text-dark">Data Presensi Harian</h6>
            <span class="badge bg-primary-light text-primary font-heading px-2.5 py-1" style="font-size: 11px;">
                Total: {{ $presensis->total() }} Data
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="color: var(--text-primary);">
                <thead class="table-light">
                    <tr class="font-heading text-nowrap" style="font-size: 13px; font-weight: 600;">
                        <th class="ps-4">Murid (Kelas)</th>
                        <th>DUDI Tempat PKL</th>
                        <th class="text-center">Jam Check In</th>
                        <th class="text-center">Foto Check In</th>
                        <th class="text-center">Jam Check Out</th>
                        <th class="text-center">Foto Check Out</th>
                        <th class="text-center pe-4" style="width: 150px;">Status @if(auth()->user()->role === 'admin') / Aksi @endif</th>
                    </tr>
                </thead>
                <tbody style="font-size: 13px;">
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
                                        <span class="badge bg-primary-light text-primary fw-semibold" style="font-size: 11px;">🏠 WFA</span>
                                    @else
                                        <span class="badge bg-secondary-light text-secondary fw-semibold" style="font-size: 11px;">🏢 WFO</span>
                                    @endif

                                    @if($p->shift_harian === 'pagi')
                                        <span class="badge bg-success-light text-success fw-semibold" style="font-size: 11px;">🌅 Shift Pagi</span>
                                    @elseif($p->shift_harian === 'siang')
                                        <span class="badge bg-warning-light text-warning fw-semibold" style="font-size: 11px;">🌆 Shift Siang</span>
                                    @elseif($p->shift_harian === 'sore')
                                        <span class="badge bg-orange-light text-orange fw-semibold" style="font-size: 11px;">🌇 Shift Sore</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center fw-semibold text-success">
                                {{ $p->jam_masuk ? substr($p->jam_masuk, 0, 5) : '-' }}
                            </td>
                            <td class="text-center">
                                @if($p->foto_masuk)
                                    <a href="{{ asset('storage/attendance/' . $p->foto_masuk) }}" target="_blank">
                                        <img src="{{ asset('storage/attendance/' . $p->foto_masuk) }}" class="rounded border" width="40" height="40" style="object-fit: cover;">
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
                                    <a href="{{ asset('storage/attendance/' . $p->foto_pulang) }}" target="_blank">
                                        <img src="{{ asset('storage/attendance/' . $p->foto_pulang) }}" class="rounded border" width="40" height="40" style="object-fit: cover;">
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="mb-1">
                                    @if($p->status_masuk === 'libur_shift')
                                        <span class="badge bg-info-light text-info fw-semibold">🌴 Libur Shift</span>
                                    @elseif($p->status_masuk === 'alpha')
                                        <span class="badge bg-danger-light text-danger fw-semibold">❌ Alpha</span>
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
                                    <div class="mt-2 d-flex justify-content-center gap-1">
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
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('presensi.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi ini?');" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Hapus Presensi">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

        @if($presensis->hasPages())
            <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-top-color: var(--border-color) !important;">
                <div class="small text-muted font-heading">
                    Menampilkan <strong>{{ $presensis->firstItem() }}</strong> - <strong>{{ $presensis->lastItem() }}</strong> dari <strong>{{ $presensis->total() }}</strong> data presensi
                </div>
                <div>
                    {{ $presensis->withQueryString()->links() }}
                </div>
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
                        <label for="penempatan_pkl_id" class="form-label small fw-semibold">Pilih Murid & DUDI</label>
                        <select name="penempatan_pkl_id" id="penempatan_pkl_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Murid --</option>
                            @foreach($activePlacements as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->murid->nama }} ({{ $item->murid->kelas->nama }}) - {{ $item->dudi->nama }}
                                </option>
                            @endforeach
                        </select>
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
</script>
@endif
@endsection
