@extends('layouts.admin')

@section('title', 'Konfigurasi Master - PKLku')
@section('page_title', 'Konfigurasi Master Akademik')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <!-- Tahun Ajaran Column -->
        <div class="col-md-6 mb-4">
            <div class="card-premium">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">Tahun Ajaran</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#collapseTahunAjaran">
                        + Tambah
                    </button>
                </div>

                <!-- Collapse Form -->
                <div class="collapse mb-3" id="collapseTahunAjaran">
                    <div class="p-3 border rounded bg-light" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                        <form action="{{ route('tahun-ajaran.store') }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Tahun</label>
                                <input type="text" name="tahun" class="form-control form-control-sm" placeholder="2025/2026" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Semester</label>
                                <select name="semester" class="form-select form-select-sm" required>
                                    <option value="ganjil">Ganjil</option>
                                    <option value="genap">Genap</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary w-100 mt-2">Simpan Tahun Ajaran</button>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-sm align-middle text-dark dark-text-light mb-0" style="font-size: 13px;">
                        <thead>
                            <tr class="text-muted">
                                <th>Periode</th>
                                <th>Semester</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tahunAjarans as $ta)
                                <tr>
                                    <td class="fw-semibold">{{ $ta->tahun }}</td>
                                    <td class="text-capitalize">{{ $ta->semester }}</td>
                                    <td class="text-center">
                                        @if($ta->is_aktif)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Non-Aktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            @if(!$ta->is_aktif)
                                                <form action="{{ route('tahun-ajaran.update', $ta->id) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-outline-success p-1" title="Aktifkan Periode">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                                <form action="{{ route('tahun-ajaran.destroy', $ta->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tahun ajaran ini?');" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Hapus Periode">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Jurusan & Kelas Column -->
        <div class="col-md-6 mb-4">
            <!-- Jurusan Card -->
            <div class="card-premium mb-4">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Jurusan / Kompetensi Keahlian</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle text-dark dark-text-light mb-0" style="font-size: 13px;">
                        <thead>
                            <tr class="text-muted">
                                <th>Kode</th>
                                <th>Nama Lengkap Keahlian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jurusans as $j)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $j->kode }}</td>
                                    <td>{{ $j->nama }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kelas Card -->
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Daftar Kelas</h5>
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-sm align-middle text-dark dark-text-light mb-0" style="font-size: 13px;">
                        <thead>
                            <tr class="text-muted">
                                <th>Nama Kelas</th>
                                <th>Jurusan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kelas as $k)
                                <tr>
                                    <td class="fw-semibold">{{ $k->nama }}</td>
                                    <td>{{ $k->jurusan->nama }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
