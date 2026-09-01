@extends('layouts.admin')

@section('title', 'Data Mitra DUDI - PKLku')
@section('page_title', 'Manajemen Data Mitra DUDI')

@section('content')
<div class="container-fluid p-0">
    <!-- Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">Daftar Mitra Dunia Usaha / Industri</h5>
        <div class="d-flex gap-2 mt-2 mt-sm-0 flex-wrap">
            <!-- Export Excel Button -->
            <a href="{{ route('dudi.export', request()->query()) }}" class="btn btn-sm btn-outline-success d-flex align-items-center font-heading">
                <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Ekspor Excel
            </a>
            <!-- Trigger Import Modal -->
            <button class="btn btn-sm btn-outline-primary d-flex align-items-center font-heading" data-bs-toggle="modal" data-bs-target="#importModal">
                <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Impor Excel
            </button>
            <!-- Trigger Add Modal -->
            <button class="btn btn-sm btn-primary d-flex align-items-center font-heading" data-bs-toggle="modal" data-bs-target="#addModal">
                <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah DUDI
            </button>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card-premium mb-4">
        <form action="{{ route('dudi.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-6 col-lg-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-0" placeholder="Cari nama DUDI, alamat, atau PIC..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="asc" {{ request('sort', 'asc') == 'asc' ? 'selected' : '' }}>Urutan Nama: A ➔ Z (Abjad)</option>
                    <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Urutan Nama: Z ➔ A (Terbalik)</option>
                </select>
            </div>
            <div class="col-md-2 col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary font-heading d-flex align-items-center justify-content-center gap-1 px-3 flex-fill">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filter</span>
                </button>
                @if(request()->filled('search') || (request()->filled('sort') && request('sort') !== 'asc'))
                    <a href="{{ route('dudi.index') }}" class="btn btn-sm btn-outline-secondary font-heading d-flex align-items-center justify-content-center gap-1 px-2.5" title="Reset Filter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Reset</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card-premium p-0 overflow-hidden">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-bottom-color: var(--border-color) !important;">
            <h6 class="fw-bold m-0 text-dark">Data Mitra DUDI</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="color: var(--text-primary);">
                <thead class="table-light" style="background-color: var(--bg-canvas);">
                    <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                        <th class="ps-4">
                            <a href="{{ route('dudi.index', array_merge(request()->query(), ['sort' => request('sort') === 'desc' ? 'asc' : 'desc'])) }}" class="text-decoration-none text-dark dark-text-light d-inline-flex align-items-center gap-1" title="Klik untuk mengurutkan nama DUDI">
                                Nama DUDI
                                @if(request('sort') === 'desc')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </a>
                        </th>
                        <th>Alamat</th>
                        <th>Koordinat (Lat, Lng)</th>
                        <th>Radius Geofence</th>
                        <th>Pembimbing Industri (Nama/No. HP)</th>
                        <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody style="font-size: 13px;">
                    @forelse($dudis as $dudi)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $dudi->nama }}</td>
                            <td>{{ Str::limit($dudi->alamat, 40) }}</td>
                            <td><code style="font-size: 11px;">{{ $dudi->latitude }}, {{ $dudi->longitude }}</code></td>
                            <td>
                                <span class="badge bg-primary-light text-primary fw-semibold d-block mb-1" style="max-width: 90px;">{{ $dudi->radius_meter }} Meter</span>
                                <small class="text-muted d-block" style="font-size: 10px;" title="Hari Kerja: {{ $dudi->hari_kerja ?? 'Senin,Selasa,Rabu,Kamis,Jumat' }}">
                                    <strong>Kerja:</strong> {{ Str::limit($dudi->hari_kerja ?? 'Senin,Selasa,Rabu,Kamis,Jumat', 15) }}
                                </small>
                            </td>
                            <td>
                                <div>{{ $dudi->pic_nama }}</div>
                                <small class="text-muted">{{ $dudi->pic_phone }}</small>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-outline-warning btn-action" data-bs-toggle="modal" data-bs-target="#editModal_{{ $dudi->id }}" title="Edit DUDI" aria-label="Edit DUDI {{ $dudi->nama }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('dudi.destroy', $dudi->id) }}" method="POST" id="deleteDudiForm_{{ $dudi->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" title="Hapus DUDI" aria-label="Hapus DUDI {{ $dudi->nama }}" onclick="window.confirmDelete('deleteDudiForm_{{ $dudi->id }}', 'mitra DUDI {{ addslashes($dudi->nama) }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                <!-- Edit Modal -->
                                <div class="modal fade text-start" id="editModal_{{ $dudi->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                            <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                                <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Edit Mitra DUDI</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('dudi.update', $dudi->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Nama Perusahaan / Instansi</label>
                                                        <input type="text" name="nama" class="form-control form-control-sm" value="{{ $dudi->nama }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Alamat Lengkap Kantor</label>
                                                        <textarea name="alamat" class="form-control form-control-sm" rows="2" required>{{ $dudi->alamat }}</textarea>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label small fw-semibold">Latitude</label>
                                                            <input type="text" name="latitude" class="form-control form-control-sm" value="{{ $dudi->latitude }}" required>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label small fw-semibold">Longitude</label>
                                                            <input type="text" name="longitude" class="form-control form-control-sm" value="{{ $dudi->longitude }}" required>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label small fw-semibold">Radius Geofence (Meter)</label>
                                                            <input type="number" name="radius_meter" class="form-control form-control-sm" value="{{ $dudi->radius_meter }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label small fw-semibold">Nama Pembimbing Industri</label>
                                                            <input type="text" name="pic_nama" class="form-control form-control-sm" value="{{ $dudi->pic_nama }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label small fw-semibold">No. HP / WA Pembimbing Industri</label>
                                                            <input type="text" name="pic_phone" class="form-control form-control-sm" value="{{ $dudi->pic_phone }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold d-block">Hari Kerja Efektif</label>
                                                        @php
                                                            $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                                            $selectedDays = explode(',', $dudi->hari_kerja ?? 'Senin,Selasa,Rabu,Kamis,Jumat');
                                                        @endphp
                                                        <div class="d-flex flex-wrap gap-3 mt-1">
                                                            @foreach($days as $day)
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" name="hari_kerja[]" value="{{ $day }}" id="hari_edit_{{ $dudi->id }}_{{ $day }}" {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                                                                    <label class="form-check-label small" for="hari_edit_{{ $dudi->id }}_{{ $day }}">{{ $day }}</label>
                                                                </div>
                                                            @endforeach
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
                                    <h6 class="empty-state-title">Tidak Ada Data Mitra DUDI</h6>
                                    <p class="empty-state-text">Gunakan tombol Tambah Mitra DUDI atau Impor Excel di atas untuk mendaftarkan mitra industri baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dudis->hasPages())
        <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-top-color: var(--border-color) !important;">
            <div class="small text-muted font-heading">
                Menampilkan <strong>{{ $dudis->firstItem() }}</strong> - <strong>{{ $dudis->lastItem() }}</strong> dari <strong>{{ $dudis->total() }}</strong> mitra DUDI
            </div>
            <div>
                {{ $dudis->withQueryString()->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal: Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                <h5 class="modal-title font-heading fw-bold" id="importModalLabel">Impor Massal Mitra DUDI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('import.store', 'dudi') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-3" style="background-color: rgba(79, 70, 229, 0.1); color: var(--accent-primary); font-size: 13px;">
                        Silakan unduh template excel terlebih dahulu, isi data sesuai kolom, lalu unggah kembali ke sini.
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('import.template', 'dudi') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center">
                            <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M8 12l4 4m0 0l4-4m-4 4V4"/>
                            </svg>
                            Unduh Template Excel
                        </a>
                    </div>
                    <div class="mb-3">
                        <label for="excelFile" class="form-label small fw-semibold">Pilih File Excel (.xlsx / .xls)</label>
                        <input type="file" name="file" id="excelFile" class="form-control" accept=".xlsx, .xls" required>
                    </div>
                </div>
                <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Mulai Impor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Dudi -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                <h5 class="modal-title font-heading fw-bold" id="addModalLabel">Tambah Mitra DUDI Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('dudi.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama" class="form-label small fw-semibold">Nama Perusahaan / Instansi</label>
                        <input type="text" name="nama" id="nama" class="form-control form-control-sm" placeholder="PT. Sukses Kreatif Solusindo" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alamat" class="form-label small fw-semibold">Alamat Lengkap Kantor</label>
                        <textarea name="alamat" id="alamat" class="form-control form-control-sm" rows="2" placeholder="Jl. Sudirman Kav. 21, Jakarta Selatan" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="latitude" class="form-label small fw-semibold">Latitude Koordinat</label>
                            <input type="text" name="latitude" id="latitude" class="form-control form-control-sm" placeholder="-6.223056" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="longitude" class="form-label small fw-semibold">Longitude Koordinat</label>
                            <input type="text" name="longitude" id="longitude" class="form-control form-control-sm" placeholder="106.809722" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="radius_meter" class="form-label small fw-semibold">Radius Geofence (Meter)</label>
                            <input type="number" name="radius_meter" id="radius_meter" class="form-control form-control-sm" value="50" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pic_nama" class="form-label small fw-semibold">Nama Pembimbing Industri</label>
                            <input type="text" name="pic_nama" id="pic_nama" class="form-control form-control-sm" placeholder="Eko Prasetyo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pic_phone" class="form-label small fw-semibold">No. HP / WA Pembimbing Industri</label>
                            <input type="text" name="pic_phone" id="pic_phone" class="form-control form-control-sm" placeholder="081299998888" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold d-block">Hari Kerja Efektif</label>
                        @php
                            $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                        @endphp
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            @foreach($days as $day)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="hari_kerja[]" value="{{ $day }}" id="hari_add_{{ $day }}" {{ in_array($day, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="hari_add_{{ $day }}">{{ $day }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Mitra</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
