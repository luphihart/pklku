@extends('layouts.admin')

@section('title', 'Data Kelas - PKLku')
@section('page_title', 'Manajemen Kelas')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <!-- Add Kelas Card -->
        <div class="col-md-4 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Tambah Kelas</h5>
                
                <form action="{{ route('kelas.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="nama" class="form-label small fw-semibold">Nama Kelas</label>
                        <input type="text" name="nama" id="nama" class="form-control form-control-sm" placeholder="XII RPL 1" required>
                    </div>

                    <div class="mb-3">
                        <label for="jurusan_id" class="form-label small fw-semibold">Jurusan Terikat</label>
                        <select name="jurusan_id" id="jurusan_id" class="form-select form-select-sm" required>
                            <option value="">Pilih Jurusan</option>
                            @foreach($jurusans as $jur)
                                <option value="{{ $jur->id }}">{{ $jur->nama }} ({{ $jur->kode }})</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100 font-heading">Simpan Kelas</button>
                </form>
            </div>
        </div>

        <!-- List Kelas Card -->
        <div class="col-md-8 mb-4">
            <div class="card-premium p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-bottom-color: var(--border-color) !important;">
                    <h6 class="fw-bold m-0 text-dark">Daftar Kelas Sekolah</h6>
                    <button type="submit" form="bulkDeleteForm" id="btnDeleteSelected" class="btn btn-xs btn-danger font-heading fw-bold" style="display: none; font-size: 11px; padding: 4px 8px;" onclick="return confirm('Apakah Anda yakin ingin menghapus kelas yang terpilih?');">
                        <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: inline-block; vertical-align: middle;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus Terpilih
                    </button>
                </div>

                <form action="{{ route('kelas.destroy_bulk') }}" method="POST" id="bulkDeleteForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px;">
                            <thead>
                                <tr class="text-muted">
                                    <th class="ps-4" style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                                    <th style="width: 80px;">No</th>
                                    <th>Nama Kelas</th>
                                    <th>Jurusan</th>
                                    <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kelases as $index => $k)
                                    <tr>
                                        <td class="ps-4"><input type="checkbox" name="ids[]" value="{{ $k->id }}" class="row-checkbox"></td>
                                        <td class="fw-semibold">{{ $kelases->firstItem() + $index }}</td>
                                        <td class="fw-bold text-dark">{{ $k->name ?? $k->nama }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $k->jurusan->nama }} ({{ $k->jurusan->kode }})</span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <!-- Edit button with Icon -->
                                                <button type="button" class="btn btn-sm btn-outline-warning p-1" data-bs-toggle="modal" data-bs-target="#editModal_{{ $k->id }}" title="Edit Kelas">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                
                                                <!-- Delete Form with Icon -->
                                                <button type="button" class="btn btn-sm btn-outline-danger p-1" title="Hapus Kelas" onclick="if(confirm('Apakah Anda yakin ingin menghapus kelas ini?')) { document.getElementById('deleteForm_{{ $k->id }}').submit(); }">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data kelas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>

                <!-- Hidden Delete Forms for Single Delete -->
                @foreach($kelases as $k)
                    <form action="{{ route('kelas.destroy', $k->id) }}" method="POST" id="deleteForm_{{ $k->id }}" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach

                <!-- Edit Modals -->
                @foreach($kelases as $k)
                    <div class="modal fade text-start" id="editModal_{{ $k->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                    <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Edit Kelas</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('kelas.update', $k->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Nama Kelas</label>
                                            <input type="text" name="nama" class="form-control form-control-sm" value="{{ $k->name ?? $k->nama }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Jurusan Terikat</label>
                                            <select name="jurusan_id" class="form-select form-select-sm" required>
                                                @foreach($jurusans as $jur)
                                                    <option value="{{ $jur->id }}" {{ $k->jurusan_id == $jur->id ? 'selected' : '' }}>
                                                        {{ $jur->nama }} ({{ $jur->kode }})
                                                    </option>
                                                @endforeach
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
                @endforeach

                @if($kelases->hasPages())
                <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
                    {{ $kelases->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const btnDeleteSelected = document.getElementById('btnDeleteSelected');

        function toggleDeleteButton() {
            const anyChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
            btnDeleteSelected.style.display = anyChecked ? 'inline-block' : 'none';
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                rowCheckboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                toggleDeleteButton();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
                if (selectAll) selectAll.checked = allChecked;
                toggleDeleteButton();
            });
        });
    });
</script>
@endsection
