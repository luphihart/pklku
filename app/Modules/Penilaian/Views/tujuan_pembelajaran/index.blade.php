@extends('layouts.admin')

@section('title', 'Tujuan Pembelajaran - PKLku')
@section('page_title', 'Manajemen Tujuan Pembelajaran')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-3">
        <a href="{{ route('indikator.index') }}" class="btn btn-sm btn-outline-secondary font-heading d-inline-flex align-items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Indikator Penilaian
        </a>
    </div>

    <div class="row">
        <!-- Add TP Column -->
        <div class="col-md-4 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Tambah Tujuan Pembelajaran</h5>
                
                <form action="{{ route('tujuan-pembelajaran.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="nomor" class="form-label small fw-semibold">Nomor TP (Opsional)</label>
                        <input type="text" name="nomor" id="nomor" class="form-control form-control-sm" placeholder="Contoh: 1, 2, 3, 4">
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label small fw-semibold">Nama Tujuan Pembelajaran</label>
                        <textarea name="nama" id="nama" class="form-control form-control-sm" rows="3" placeholder="Contoh: Menerapkan soft skills yang dibutuhkan dalam dunia kerja..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100 font-heading">Simpan Tujuan Pembelajaran</button>
                </form>
            </div>
        </div>

        <!-- List TP Column -->
        <div class="col-md-8 mb-4">
            <div class="card-premium p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-bottom-color: var(--border-color) !important;">
                    <h6 class="fw-bold m-0 text-dark">Daftar Tujuan Pembelajaran</h6>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px;">
                        <thead>
                            <tr class="text-muted">
                                <th class="ps-4" style="width: 80px;">No. TP</th>
                                <th>Nama Tujuan Pembelajaran</th>
                                <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tps as $tp)
                                <tr>
                                    <td class="ps-4 fw-bold text-secondary">{{ $tp->nomor ?? '-' }}</td>
                                    <td class="fw-bold text-dark">{{ $tp->nama }}</td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <!-- Edit Button -->
                                            <button class="btn btn-sm btn-outline-warning p-1" data-bs-toggle="modal" data-bs-target="#editModal_{{ $tp->id }}" title="Edit TP">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>

                                            <!-- Delete Button -->
                                            <form action="{{ route('tujuan-pembelajaran.destroy', $tp->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus TP ini? Semua indikator di dalamnya akan terhapus.');" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Hapus TP">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Edit Modal -->
                                        <div class="modal fade text-start" id="editModal_{{ $tp->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                                    <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                                        <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Edit Tujuan Pembelajaran</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('tujuan-pembelajaran.update', $tp->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Nomor TP (Opsional)</label>
                                                                <input type="text" name="nomor" class="form-control form-control-sm" value="{{ $tp->nomor }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Nama Tujuan Pembelajaran</label>
                                                                <textarea name="nama" class="form-control form-control-sm" rows="3" required>{{ $tp->nama }}</textarea>
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
                                    <td colspan="3" class="text-center py-4 text-muted">Belum ada data tujuan pembelajaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
