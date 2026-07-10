@extends('layouts.admin')

@section('title', 'Data Murid - PKLku')
@section('page_title', 'Manajemen Data Murid')

@section('content')
<div class="container-fluid p-0">
    <!-- Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">Daftar Seluruh Murid</h5>
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
                Tambah Murid
            </button>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card-premium mb-4">
        <form action="{{ route('murid.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari berdasarkan nama atau NIS..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="kelas_id" class="form-select form-select-sm">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-sm btn-primary">Filter Data</button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card-premium p-0 overflow-hidden">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-bottom-color: var(--border-color) !important;">
            <h6 class="fw-bold m-0 text-dark">Data Murid Aktif</h6>
            <button type="submit" form="bulkDeleteForm" id="btnDeleteSelected" class="btn btn-xs btn-danger font-heading fw-bold" style="display: none; font-size: 11px; padding: 4px 8px;" onclick="return confirm('Apakah Anda yakin ingin menghapus murid yang terpilih? Akun login terkait juga akan ikut dihapus.');">
                <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: inline-block; vertical-align: middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Terpilih
            </button>
        </div>

        <form action="{{ route('murid.destroy_bulk') }}" method="POST" id="bulkDeleteForm">
            @csrf
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="color: var(--text-primary);">
                    <thead class="table-light" style="background-color: var(--bg-canvas);">
                        <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                            <th class="ps-4" style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                            <th>NIS</th>
                            <th>Nama Lengkap</th>
                            <th>Kelas</th>
                            <th>Email</th>
                            <th>No. Telp</th>
                            <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13px;">
                        @forelse($murids as $murid)
                            <tr>
                                <td class="ps-4"><input type="checkbox" name="ids[]" value="{{ $murid->id }}" class="row-checkbox"></td>
                                <td class="fw-semibold">{{ $murid->nis }}</td>
                                <td class="fw-bold text-dark">{{ $murid->nama }}</td>
                                <td><span class="badge bg-secondary">{{ $murid->kelas->nama }}</span></td>
                                <td>{{ $murid->user->email }}</td>
                                <td>{{ $murid->user->phone ?? '-' }}</td>
                                <td class="text-center pe-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-outline-warning p-1" data-bs-toggle="modal" data-bs-target="#editModal_{{ $murid->id }}" title="Edit Murid">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('murid.reset_password', $murid->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset password murid ini menjadi default (siswa123)?');" style="display: inline-block;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-info p-1" title="Reset Password (siswa123)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-2-2a2 2 0 11-2-2m2 2a2 2 0 002 2m0 0a2 2 0 002-2v3a2 2 0 01-2 2h-1a2 2 0 01-2-2v-5a2 2 0 00-2-2H9m0 0l-2 2m2-2l-2-2M7 9v1H6v1H5v1H4v1H3v1h1"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1" title="Hapus Murid" onclick="if(confirm('Apakah Anda yakin ingin menghapus murid ini? Akun login yang berhubungan juga akan dihapus.')) { document.getElementById('deleteForm_{{ $murid->id }}').submit(); }">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Tidak ada data murid ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Hidden Delete Forms for Single Delete -->
        @foreach($murids as $murid)
            <form action="{{ route('murid.destroy', $murid->id) }}" method="POST" id="deleteForm_{{ $murid->id }}" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <!-- Edit Modals -->
        @foreach($murids as $murid)
            <div class="modal fade text-start" id="editModal_{{ $murid->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                        <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                            <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Edit Data Murid</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('murid.update', $murid->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">NIS</label>
                                        <input type="text" name="nis" class="form-control form-control-sm" value="{{ $murid->nis }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Kelas</label>
                                        <select name="kelas_id" class="form-select form-select-sm" required>
                                            @foreach($kelas as $k)
                                                <option value="{{ $k->id }}" {{ $murid->kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control form-control-sm" value="{{ $murid->nama }}" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Email Sekolah</label>
                                        <input type="email" name="email" class="form-control form-control-sm" value="{{ $murid->user->email }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">No. Telp (WhatsApp)</label>
                                        <input type="text" name="phone" class="form-control form-control-sm" value="{{ $murid->user->phone }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" class="form-control form-control-sm" value="{{ $murid->user->tanggal_lahir ? $murid->user->tanggal_lahir->format('Y-m-d') : '' }}">
                                </div>
                                <div class="mb-3" x-data="{ showPass: false }">
                                    <label class="form-label small fw-semibold">Password Login Baru (Opsional)</label>
                                    <div class="input-group input-group-sm">
                                        <input :type="showPass ? 'text' : 'password'" name="password" class="form-control" placeholder="Biarkan kosong jika tidak diubah">
                                        <button class="btn btn-outline-secondary" type="button" @click="showPass = !showPass">
                                            <span x-text="showPass ? 'Sembunyikan' : 'Tampilkan'"></span>
                                        </button>
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
        @endforeach

        @if($murids->hasPages())
        <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
            {{ $murids->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal: Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                <h5 class="modal-title font-heading fw-bold" id="importModalLabel">Impor Massal Murid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('import.store', 'murid') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-3" style="background-color: rgba(79, 70, 229, 0.1); color: var(--accent-primary); font-size: 13px;">
                        Silakan unduh template excel terlebih dahulu, isi data sesuai kolom, lalu unggah kembali ke sini.
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('import.template', 'murid') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center">
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

<!-- Modal: Add Murid -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                <h5 class="modal-title font-heading fw-bold" id="addModalLabel">Tambah Data Murid Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('murid.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nis" class="form-label small fw-semibold">NIS</label>
                        <input type="text" name="nis" id="nis" class="form-control form-control-sm" placeholder="102911" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" name="nama" id="nama" class="form-control form-control-sm" placeholder="Ahmad Fauzi" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">Email Sekolah</label>
                        <input type="email" name="email" id="email" class="form-control form-control-sm" placeholder="ahmad@siswa.sch.id" required>
                    </div>
                    <div class="mb-3">
                        <label for="kelas_id" class="form-label small fw-semibold">Kelas</label>
                        <select name="kelas_id" id="kelas_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label small fw-semibold">No. Telp (WhatsApp)</label>
                        <input type="text" name="phone" id="phone" class="form-control form-control-sm" placeholder="08123456789">
                    </div>
                    <div class="mb-3">
                        <label for="add_tanggal_lahir" class="form-label small fw-semibold">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="add_tanggal_lahir" class="form-control form-control-sm">
                    </div>
                    <div class="mb-3" x-data="{ showPass: false }">
                        <label for="password" class="form-label small fw-semibold">Password Login (Opsional)</label>
                        <div class="input-group input-group-sm">
                            <input :type="showPass ? 'text' : 'password'" name="password" id="password" class="form-control" placeholder="Default: siswa123">
                            <button class="btn btn-outline-secondary" type="button" @click="showPass = !showPass">
                                <span x-text="showPass ? 'Sembunyikan' : 'Tampilkan'"></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Murid</button>
                </div>
            </form>
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
