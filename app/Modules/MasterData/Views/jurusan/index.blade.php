@extends('layouts.admin')

@section('title', 'Data Jurusan - PKLku')
@section('page_title', 'Manajemen Jurusan')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <!-- Add Jurusan Card -->
        <div class="col-md-4 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Tambah Jurusan</h5>
                
                <form action="{{ route('jurusan.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="kode" class="form-label small fw-semibold">Kode Jurusan</label>
                        <input type="text" name="kode" id="kode" class="form-control form-control-sm" placeholder="RPL, TKJ, MM, dll." required>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label small fw-semibold">Nama Lengkap Jurusan</label>
                        <input type="text" name="nama" id="nama" class="form-control form-control-sm" placeholder="Rekayasa Perangkat Lunak" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100 font-heading">Simpan Jurusan</button>
                </form>
            </div>
        </div>

        <!-- List Jurusan Card -->
        <div class="col-md-8 mb-4">
            <div class="card-premium p-0 overflow-hidden">
                <div class="p-3 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                    <h6 class="fw-bold m-0 text-dark">Daftar Jurusan Sekolah</h6>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px;">
                        <thead>
                            <tr class="text-muted">
                                <th class="ps-4" style="width: 80px;">No</th>
                                <th style="width: 150px;">Kode</th>
                                <th>Nama Jurusan</th>
                                <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jurusans as $index => $j)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $jurusans->firstItem() + $index }}</td>
                                    <td class="fw-bold text-primary">{{ $j->kode }}</td>
                                    <td>{{ $j->nama }}</td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <!-- Edit button with Icon -->
                                            <button class="btn btn-sm btn-outline-warning p-1" data-bs-toggle="modal" data-bs-target="#editModal_{{ $j->id }}" title="Edit Jurusan">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            
                                            <!-- Delete Form with Icon -->
                                            <form action="{{ route('jurusan.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jurusan ini?');" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Hapus Jurusan">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Edit Modal -->
                                        <div class="modal fade text-start" id="editModal_{{ $j->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                                    <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                                        <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Edit Jurusan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('jurusan.update', $j->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Kode Jurusan</label>
                                                                <input type="text" name="kode" class="form-control form-control-sm" value="{{ $j->kode }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Nama Jurusan</label>
                                                                <input type="text" name="nama" class="form-control form-control-sm" value="{{ $j->nama }}" required>
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
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada data jurusan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($jurusans->hasPages())
                <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
                    {{ $jurusans->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
