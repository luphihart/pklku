@extends('layouts.admin')

@section('title', 'Data Murid - PKLku')
@section('page_title', 'Manajemen Data Murid')

@section('content')
<div class="container-fluid p-0">
    <!-- Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">Daftar Seluruh Murid</h5>
        <div class="d-flex gap-2 mt-2 mt-sm-0 flex-wrap">
            <!-- Export Excel Button -->
            <a href="{{ route('murid.export', request()->query()) }}" class="btn btn-sm btn-outline-success d-flex align-items-center font-heading">
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
                Tambah Murid
            </button>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card-premium mb-4">
        <form action="{{ route('murid.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4 col-lg-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-0" placeholder="Cari nama atau NIS..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3 col-lg-3">
                <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-3">
                <select name="sort_by_order" class="form-select form-select-sm" onchange="
                    const val = this.value.split(':');
                    document.getElementById('murid_sort_by').value = val[0];
                    document.getElementById('murid_order').value = val[1];
                    this.form.submit();
                ">
                    <option value="nama:asc" {{ (request('sort_by', 'nama') == 'nama' && request('order', 'asc') == 'asc') ? 'selected' : '' }}>Nama (A ➔ Z)</option>
                    <option value="nama:desc" {{ (request('sort_by') == 'nama' && request('order') == 'desc') ? 'selected' : '' }}>Nama (Z ➔ A)</option>
                    <option value="nis:asc" {{ (request('sort_by') == 'nis' && request('order', 'asc') == 'asc') ? 'selected' : '' }}>NIS (Terkecil ➔ Terbesar)</option>
                    <option value="nis:desc" {{ (request('sort_by') == 'nis' && request('order') == 'desc') ? 'selected' : '' }}>NIS (Terbesar ➔ Terkecil)</option>
                </select>
                <input type="hidden" name="sort_by" id="murid_sort_by" value="{{ request('sort_by', 'nama') }}">
                <input type="hidden" name="order" id="murid_order" value="{{ request('order', 'asc') }}">
            </div>
            <div class="col-md-2 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary font-heading d-flex align-items-center justify-content-center gap-1 px-3 flex-fill">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filter</span>
                </button>
                @if(request()->filled('search') || request()->filled('kelas_id') || (request()->filled('sort_by') && request('sort_by') !== 'nama') || (request()->filled('order') && request('order') !== 'asc'))
                    <a href="{{ route('murid.index') }}" class="btn btn-sm btn-outline-secondary font-heading d-flex align-items-center justify-content-center gap-1 px-2.5" title="Reset Filter">
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
            <h6 class="fw-bold m-0 text-dark">Data Murid Aktif</h6>
            <div class="d-flex gap-2">
                <button type="button" id="btnResetSelected" class="btn btn-xs btn-info text-white font-heading fw-bold btn-bulk-action" style="display: none;" onclick="
                    const count = document.querySelectorAll('.row-checkbox:checked').length;
                    window.confirmAction({
                        title: 'Reset Password ' + count + ' Murid?',
                        text: 'Password untuk seluruh murid terpilih akan direset ke default (siswa123).',
                        confirmButtonText: 'Ya, Reset'
                    }).then(r => {
                        if(r.isConfirmed) {
                            const form = document.getElementById('bulkDeleteForm');
                            form.action = '{{ route('murid.reset_password_bulk') }}';
                            form.submit();
                        }
                    });
                ">
                    <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: inline-block; vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-2-2a2 2 0 11-2-2m2 2a2 2 0 002 2m0 0a2 2 0 002-2v3a2 2 0 01-2 2h-1a2 2 0 01-2-2v-5a2 2 0 00-2-2H9m0 0l-2 2m2-2l-2-2M7 9v1H6v1H5v1H4v1H3v1h1"/>
                    </svg>
                    Reset Password Terpilih
                </button>
                <button type="button" id="btnDeleteSelected" class="btn btn-xs btn-danger font-heading fw-bold btn-bulk-action" style="display: none;" onclick="
                    const count = document.querySelectorAll('.row-checkbox:checked').length;
                    window.confirmAction({
                        title: 'Hapus ' + count + ' Murid Terpilih?',
                        text: 'Data murid dan akun login terkait akan dihapus secara permanen.',
                        icon: 'warning',
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Ya, Hapus'
                    }).then(r => {
                        if(r.isConfirmed) {
                            const form = document.getElementById('bulkDeleteForm');
                            form.action = '{{ route('murid.destroy_bulk') }}';
                            form.submit();
                        }
                    });
                ">
                    <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: inline-block; vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus Terpilih
                </button>
            </div>
        </div>

        <form action="{{ route('murid.destroy_bulk') }}" method="POST" id="bulkDeleteForm">
            @csrf
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="color: var(--text-primary);">
                    <thead class="table-light" style="background-color: var(--bg-canvas);">
                        <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                            <th class="ps-4" style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                            <th>
                                <a href="{{ route('murid.index', array_merge(request()->query(), ['sort_by' => 'nis', 'order' => (request('sort_by') === 'nis' && request('order', 'asc') === 'asc') ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark dark-text-light d-inline-flex align-items-center gap-1" title="Klik untuk mengurutkan NIS">
                                    NIS
                                    @if(request('sort_by') === 'nis')
                                        @if(request('order', 'asc') === 'desc')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        @endif
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted opacity-50"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('murid.index', array_merge(request()->query(), ['sort_by' => 'nama', 'order' => (request('sort_by', 'nama') === 'nama' && request('order', 'asc') === 'asc') ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark dark-text-light d-inline-flex align-items-center gap-1" title="Klik untuk mengurutkan Nama Lengkap">
                                    Nama Lengkap
                                    @if(request('sort_by', 'nama') === 'nama')
                                        @if(request('order', 'asc') === 'desc')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        @endif
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted opacity-50"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    @endif
                                </a>
                            </th>
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
                                <td><span class="badge bg-primary-light text-primary fw-semibold">{{ $murid->kelas?->nama ?? '-' }}</span></td>
                                <td>{{ $murid->user?->email ?? '-' }}</td>
                                <td>{{ $murid->user?->phone ?? '-' }}</td>
                                <td class="text-center pe-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-action" data-bs-toggle="modal" data-bs-target="#editModal_{{ $murid->id }}" title="Edit Murid" aria-label="Edit Murid {{ $murid->nama }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info btn-action" title="Reset Password" aria-label="Reset Password Murid {{ $murid->nama }}" onclick="window.confirmAction({ title: 'Reset Password?', text: 'Password untuk {{ addslashes($murid->nama) }} akan direset menjadi default (siswa123).', confirmButtonText: 'Ya, Reset' }).then(r => { if(r.isConfirmed) document.getElementById('resetForm_{{ $murid->id }}').submit(); });">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-2-2a2 2 0 11-2-2m2 2a2 2 0 002 2m0 0a2 2 0 002-2v3a2 2 0 01-2 2h-1a2 2 0 01-2-2v-5a2 2 0 00-2-2H9m0 0l-2 2m2-2l-2-2M7 9v1H6v1H5v1H4v1H3v1h1"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" title="Hapus Murid" aria-label="Hapus Murid {{ $murid->nama }}" onclick="window.confirmDelete('deleteForm_{{ $murid->id }}', 'murid {{ addslashes($murid->nama) }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="empty-state py-4">
                                        <div class="empty-state-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </div>
                                        <h6 class="empty-state-title">Tidak Ada Data Murid</h6>
                                        <p class="empty-state-text">Gunakan tombol Tambah Murid atau Impor Excel di atas untuk memasukkan data siswa baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        @if(method_exists($murids, 'hasPages') && $murids->hasPages())
            <div class="border-top" style="border-top-color: var(--border-color) !important;">
                {{ $murids->withQueryString()->links() }}
            </div>
        @endif

        <!-- Hidden Delete Forms for Single Delete -->
        @foreach($murids as $murid)
            <form action="{{ route('murid.destroy', $murid->id) }}" method="POST" id="deleteForm_{{ $murid->id }}" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <!-- Hidden Reset Password Forms -->
        @foreach($murids as $murid)
            <form action="{{ route('murid.reset_password', $murid->id) }}" method="POST" id="resetForm_{{ $murid->id }}" style="display: none;">
                @csrf
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
        const btnResetSelected = document.getElementById('btnResetSelected');

        function toggleActionButtons() {
            const anyChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
            btnDeleteSelected.style.display = anyChecked ? 'inline-block' : 'none';
            if (btnResetSelected) {
                btnResetSelected.style.display = anyChecked ? 'inline-block' : 'none';
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                rowCheckboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                toggleActionButtons();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
                if (selectAll) selectAll.checked = allChecked;
                toggleActionButtons();
            });
        });
    });
</script>
@endsection
