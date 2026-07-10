@extends('layouts.admin')

@section('title', 'Data Mitra DUDI - PKLku')
@section('page_title', 'Manajemen Data Mitra DUDI')

@section('content')
<div class="container-fluid p-0">
    <!-- Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">Daftar Mitra Dunia Usaha / Industri</h5>
        <div class="d-flex gap-2 mt-2 mt-sm-0">
            <!-- Trigger Import Modal -->
            <button class="btn btn-sm btn-outline-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#importModal">
                <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Impor Excel
            </button>
            <!-- Trigger Add Modal -->
            <button class="btn btn-sm btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addModal">
                <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah DUDI
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card-premium p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="color: var(--text-primary);">
                <thead class="table-light" style="background-color: var(--bg-canvas);">
                    <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                        <th class="ps-4">Nama Perusahaan</th>
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
                                <span class="badge bg-info d-block mb-1" style="max-width: 90px;">{{ $dudi->radius_meter }} Meter</span>
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
                                    <button class="btn btn-sm btn-outline-warning p-1" data-bs-toggle="modal" data-bs-target="#editModal_{{ $dudi->id }}" title="Edit DUDI">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('dudi.destroy', $dudi->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mitra DUDI ini? Semua hubungan penempatan aktif di dalamnya akan ikut dihapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Hapus DUDI">
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
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada data mitra DUDI ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dudis->hasPages())
        <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
            {{ $dudis->links() }}
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
                        <input type="text" name="nama" id="nama" class="form-control form-control-sm" placeholder="PT. Antigravity Global Technology" required>
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
