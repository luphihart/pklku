@extends('layouts.admin')

@section('title', 'Indikator Penilaian - PKLku')
@section('page_title', 'Manajemen Indikator Penilaian')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <!-- Add Indicator Column -->
        <div class="col-md-4 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Tambah Indikator</h5>
                
                <form action="{{ route('indikator.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="tujuan_pembelajaran_id" class="form-label small fw-semibold">Tujuan Pembelajaran</label>
                        <select name="tujuan_pembelajaran_id" id="tujuan_pembelajaran_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Tujuan Pembelajaran --</option>
                            @foreach($tps as $tp)
                                <option value="{{ $tp->id }}">({{ $tp->nomor ?? '-' }}) {{ Str::limit($tp->nama, 80) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="nomor_urut" class="form-label small fw-semibold">Nomor Urut Indikator</label>
                        <input type="text" name="nomor_urut" id="nomor_urut" class="form-control form-control-sm" placeholder="Contoh: 1.1, 1.2, 3.7" required>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label small fw-semibold">Nama Indikator / Kriteria</label>
                        <input type="text" name="nama" id="nama" class="form-control form-control-sm" placeholder="Contoh: Kedisiplinan, Inisiatif, Kerja Sama" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label small fw-semibold">Deskripsi Indikator (Opsional)</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control form-control-sm" rows="3" placeholder="Jelaskan kriteria penilaian indikator ini..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="tipe" class="form-label small fw-semibold">Tipe Penilai</label>
                        <select name="tipe" id="tipe" class="form-select form-select-sm" required>
                            <option value="guru">Guru Sekolah (Internal)</option>
                            <option value="industri">Pembimbing DUDI (Eksternal)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100 font-heading">Simpan Indikator</button>
                </form>
            </div>
        </div>

        <!-- List Indicators Column -->
        <div class="col-md-8 mb-4">
            <div class="card-premium p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-bottom-color: var(--border-color) !important;">
                    <h6 class="fw-bold m-0 text-dark">Daftar Indikator Penilaian</h6>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="{{ route('tujuan-pembelajaran.index') }}" class="btn btn-sm btn-outline-secondary font-heading py-1" style="font-size: 11px;">
                            Kelola Tujuan Pembelajaran
                        </a>
                        <div class="d-flex gap-1 border-start ps-2" style="border-start-color: var(--border-color) !important;">
                            <a href="{{ route('indikator.index') }}" class="btn btn-xs {{ !$tipe ? 'btn-primary' : 'btn-outline-primary' }}" style="font-size: 11px;">Semua</a>
                            <a href="{{ route('indikator.index', ['tipe' => 'guru']) }}" class="btn btn-xs {{ $tipe === 'guru' ? 'btn-primary' : 'btn-outline-primary' }}" style="font-size: 11px;">Guru</a>
                            <a href="{{ route('indikator.index', ['tipe' => 'industri']) }}" class="btn btn-xs {{ $tipe === 'industri' ? 'btn-primary' : 'btn-outline-primary' }}" style="font-size: 11px;">DUDI</a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px;">
                        <thead>
                            <tr class="text-muted">
                                <th class="ps-4" style="width: 60px;">No</th>
                                <th style="width: 90px;">No. Urut</th>
                                <th>Nama Indikator</th>
                                <th>Tujuan Pembelajaran</th>
                                <th>Tipe Penilai</th>
                                <th class="text-center pe-4" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($indikators as $index => $ind)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $indikators->firstItem() + $index }}</td>
                                    <td class="fw-bold text-secondary">{{ $ind->nomor_urut ?? '-' }}</td>
                                    <td class="fw-bold text-dark" style="min-width: 150px;">{{ $ind->nama }}</td>
                                    <td class="small text-muted">{{ $ind->tujuanPembelajaran ? $ind->tujuanPembelajaran->nama : '-' }}</td>
                                    <td>
                                        @if($ind->tipe === 'guru')
                                            <span class="badge bg-success">Guru Sekolah</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pembimbing DUDI</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <!-- Edit Button -->
                                            <button class="btn btn-sm btn-outline-warning p-1" data-bs-toggle="modal" data-bs-target="#editModal_{{ $ind->id }}" title="Edit Indikator">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>

                                            <!-- Delete Button -->
                                            <form action="{{ route('indikator.destroy', $ind->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus indikator ini?');" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Hapus Indikator">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Edit Modal -->
                                        <div class="modal fade text-start" id="editModal_{{ $ind->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                                    <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                                        <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Edit Indikator Penilaian</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('indikator.update', $ind->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Tujuan Pembelajaran</label>
                                                                <select name="tujuan_pembelajaran_id" class="form-select form-select-sm" required>
                                                                    <option value="">-- Pilih Tujuan Pembelajaran --</option>
                                                                    @foreach($tps as $tp)
                                                                        <option value="{{ $tp->id }}" {{ $ind->tujuan_pembelajaran_id == $tp->id ? 'selected' : '' }}>({{ $tp->nomor ?? '-' }}) {{ Str::limit($tp->nama, 80) }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Nomor Urut Indikator</label>
                                                                <input type="text" name="nomor_urut" class="form-control form-control-sm" value="{{ $ind->nomor_urut }}" placeholder="Contoh: 1.1, 1.2" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Nama Indikator / Kriteria</label>
                                                                <input type="text" name="nama" class="form-control form-control-sm" value="{{ $ind->nama }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Deskripsi Indikator (Opsional)</label>
                                                                <textarea name="deskripsi" class="form-control form-control-sm" rows="3" placeholder="Jelaskan kriteria penilaian indikator ini...">{{ $ind->deskripsi }}</textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Tipe Penilai</label>
                                                                <select name="tipe" class="form-select form-select-sm" required>
                                                                    <option value="guru" {{ $ind->tipe === 'guru' ? 'selected' : '' }}>Guru Sekolah (Internal)</option>
                                                                    <option value="industri" {{ $ind->tipe === 'industri' ? 'selected' : '' }}>Pembimbing DUDI (Eksternal)</option>
                                                                </select>
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
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada data indikator penilaian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($indikators->hasPages())
                <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
                    {{ $indikators->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
