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
        <form action="{{ route('presensi.index') }}" method="GET" class="row g-3">
            <div class="col-md-8">
                <label class="form-label small fw-semibold">Pilih Tanggal Absensi</label>
                <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ request('tanggal', now()->toDateString()) }}">
            </div>
            <div class="col-md-4 d-grid align-items-end">
                <button type="submit" class="btn btn-sm btn-primary">Filter Tanggal</button>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="card-premium p-0 overflow-hidden">
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
                                <div class="fw-semibold">{{ $p->penempatanPkl->murid->nama }}</div>
                                <small class="text-muted">{{ $p->penempatanPkl->murid->kelas->nama }}</small>
                            </td>
                            <td>{{ $p->penempatanPkl->dudi->nama }}</td>
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
                                    <span class="badge {{ $p->status_masuk === 'tepat_waktu' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $p->status_masuk === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat' }}
                                    </span>
                                </div>
                                @if($p->status_pulang === 'pulang_cepat')
                                    <div class="mb-1">
                                        <span class="badge bg-warning text-dark">Pulang Cepat</span>
                                    </div>
                                @elseif($p->status_pulang === 'tepat_waktu')
                                    <div class="mb-1">
                                        <span class="badge bg-success">Pulang Tepat Waktu</span>
                                    </div>
                                @endif
                                @if(auth()->user()->role === 'admin')
                                    <div class="mt-2 d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-warning p-1" title="Koreksi Presensi" data-bs-toggle="modal" data-bs-target="#modalEditManual" onclick="editPresensi({{ json_encode([
                                            'id' => $p->id,
                                            'tanggal' => \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d F Y'),
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
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Hapus Presensi">
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
                            <td colspan="7" class="text-center py-4 text-muted">Tidak ada catatan presensi untuk tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($presensis->hasPages())
        <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
            {{ $presensis->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

@if(auth()->user()->role === 'admin')
<!-- Modal Tambah Manual -->
<div class="modal fade" id="modalTambahManual" tabindex="-1" aria-labelledby="modalTambahManualLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalTambahManualLabel">Tambah Presensi Manual</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('presensi.store_manual') }}" method="POST">
                @csrf
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label for="penempatan_pkl_id" class="form-label small fw-semibold">Pilih Murid</label>
                        <select name="penempatan_pkl_id" id="penempatan_pkl_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Murid --</option>
                            @foreach($activePlacements as $ap)
                                <option value="{{ $ap->id }}">{{ $ap->murid->nama }} (Kelas: {{ $ap->murid->kelas->nama }} - DUDI: {{ $ap->dudi->nama }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal" class="form-label small fw-semibold">Tanggal Kehadiran</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="{{ request('tanggal', now()->toDateString()) }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="jam_masuk" class="form-label small fw-semibold">Jam Masuk (Opsional)</label>
                            <input type="time" name="jam_masuk" id="jam_masuk" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label for="status_masuk" class="form-label small fw-semibold">Status Masuk (Opsional)</label>
                            <select name="status_masuk" id="status_masuk" class="form-select form-select-sm">
                                <option value="">-- Tanpa Status Masuk --</option>
                                <option value="tepat_waktu">Tepat Waktu</option>
                                <option value="terlambat">Terlambat</option>
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
                        * Isikan salah satu (Jam Masuk saja / Jam Pulang saja) atau isi keduanya.
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
        <div class="modal-content">
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
                                <option value="">-- Tanpa Status Masuk --</option>
                                <option value="tepat_waktu">Tepat Waktu</option>
                                <option value="terlambat">Terlambat</option>
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
        document.getElementById('formEditManual').action = `/presensi/${p.id}/manual`;
        document.getElementById('edit_nama_murid').value = p.penempatan_pkl.murid.nama;
        document.getElementById('edit_tanggal').value = p.tanggal;
        document.getElementById('edit_jam_masuk').value = p.jam_masuk ? p.jam_masuk.substring(0, 5) : '';
        document.getElementById('edit_status_masuk').value = p.status_masuk || '';
        document.getElementById('edit_jam_pulang').value = p.jam_pulang ? p.jam_pulang.substring(0, 5) : '';
        document.getElementById('edit_status_pulang').value = p.status_pulang || '';
    }
</script>
@endif
@endsection
