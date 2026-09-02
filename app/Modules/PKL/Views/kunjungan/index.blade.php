@extends('layouts.admin')

@section('title', 'Kunjungan Pembimbing - PKLku')
@section('page_title', 'Kunjungan Pembimbing')

@section('content')
<div class="container-fluid p-0">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <strong>Berhasil!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <div class="row">
        <!-- Logging Form Card -->
        @if(auth()->user()->role === 'guru')
        <div class="col-md-4 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark">Catat Kunjungan Baru</h5>
                
                <form action="{{ route('kunjungan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="penempatan_pkl_id" class="form-label small fw-semibold">Mitra DUDI Bimbingan</label>
                        <select name="penempatan_pkl_id" id="penempatan_pkl_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih DUDI --</option>
                            @foreach($dudiPlacements as $p)
                                <option value="{{ $p->id }}">{{ $p->dudi->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="jenis_kunjungan" class="form-label small fw-semibold">Jenis Kunjungan</label>
                        <select name="jenis_kunjungan" id="jenis_kunjungan" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Jenis Kunjungan --</option>
                            <option value="Penjajakan Kerja Sama">Penjajakan Kerja Sama</option>
                            <option value="Penyerahan Murid">Penyerahan Murid</option>
                            <option value="Monitoring Berkala">Monitoring Berkala</option>
                            <option value="Penarikan PKL">Penarikan PKL</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal" class="form-label small fw-semibold">Tanggal Kunjungan</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi_kunjungan" class="form-label small fw-semibold">Catatan Kunjungan</label>
                        <textarea name="deskripsi_kunjungan" id="deskripsi_kunjungan" class="form-control form-control-sm" rows="4" placeholder="Tulis catatan kunjungan pembimbing, agenda diskusi, atau kendala lapangan..." required></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="foto" class="form-label small fw-semibold">Foto Bukti Kunjungan (Wajib)</label>
                        <input type="file" name="foto" id="foto" class="form-control form-control-sm" accept="image/*" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 font-heading">Simpan Catatan Kunjungan</button>
                </form>
            </div>
        </div>
        @endif

        <!-- Visitations History Card -->
        <div class="{{ auth()->user()->role === 'guru' ? 'col-md-8' : 'col-md-12' }} mb-4">
            <div class="card-premium p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-bottom-color: var(--border-color) !important;">
                    <h6 class="fw-bold m-0 text-dark">Riwayat Kunjungan Pembimbing</h6>
                    <a href="{{ route('kunjungan.export_pdf') }}" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 font-heading fw-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export PDF
                    </a>
                </div>

                <!-- Desktop Table View (md and up) -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); min-width: 760px; font-size: 13px;">
                        <thead class="table-light">
                            <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                                <th class="ps-4" style="width: 110px;">Tanggal</th>
                                <th>Mitra DUDI / Jenis</th>
                                <th>Guru Pembimbing</th>
                                <th>Catatan Kunjungan</th>
                                <th class="text-center" style="width: 100px;">Foto Bukti</th>
                                <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kunjungans as $k)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="fw-semibold text-primary">{{ $k->penempatanPkl?->dudi?->nama ?? 'DUDI Terhapus' }}</div>
                                        <span class="badge bg-primary-light text-primary fw-semibold" style="font-size: 10.5px;">{{ $k->jenis_kunjungan ?? 'Monitoring Berkala' }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $k->penempatanPkl?->guru?->nama ?? 'Guru Terhapus' }}</div>
                                    </td>
                                    <td>{{ Str::limit($k->deskripsi_kunjungan, 120) }}</td>
                                    <td class="text-center">
                                        @if($k->foto_kunjungan)
                                            <a href="{{ asset('storage/kunjungan/' . $k->foto_kunjungan) }}" target="_blank" aria-label="Foto Bukti Kunjungan">
                                                <img src="{{ asset('storage/kunjungan/' . $k->foto_kunjungan) }}" class="rounded border" width="40" height="40" style="object-fit: cover;" alt="Bukti Kunjungan">
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-action btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal_{{ $k->id }}" title="Edit Kunjungan" aria-label="Edit Catatan Kunjungan {{ $k->penempatanPkl?->dudi?->nama }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <form action="{{ route('kunjungan.destroy', $k->id) }}" method="POST" id="deleteKunjunganForm_{{ $k->id }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-action btn-sm btn-outline-danger" title="Hapus Kunjungan" aria-label="Hapus Kunjungan {{ $k->penempatanPkl?->dudi?->nama }}" onclick="window.confirmDelete('deleteKunjunganForm_{{ $k->id }}', 'catatan kunjungan {{ addslashes($k->penempatanPkl?->dudi?->nama ?? '') }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
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
                                            <h6 class="empty-state-title">Belum Ada Catatan Kunjungan</h6>
                                            <p class="empty-state-text">Gunakan form di sebelah kiri untuk mencatat kunjungan pembimbing ke mitra DUDI.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List View (Visible on smartphone < md) -->
                <div class="d-md-none p-3">
                    @forelse($kunjungans as $k)
                        <div class="card p-3 mb-3 border rounded shadow-xs" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                            <!-- Top: Tanggal & Jenis -->
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                <div class="d-flex align-items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="fw-bold font-heading text-dark" style="font-size: 13px;">
                                        {{ \Carbon\Carbon::parse($k->tanggal)->translatedFormat('d M Y') }}
                                    </span>
                                </div>
                                <span class="badge bg-primary-light text-primary fw-semibold" style="font-size: 10.5px;">{{ $k->jenis_kunjungan ?? 'Monitoring' }}</span>
                            </div>

                            <!-- DUDI & Guru -->
                            <div class="mb-2">
                                <div class="fw-bold text-dark font-heading" style="font-size: 13.5px;">{{ $k->penempatanPkl?->dudi?->nama ?? 'DUDI Terhapus' }}</div>
                                <div class="text-muted small">Pembimbing: <span class="fw-semibold text-secondary">{{ $k->penempatanPkl?->guru?->nama ?? 'Guru Terhapus' }}</span></div>
                            </div>

                            <!-- Catatan -->
                            <div class="p-2.5 rounded bg-light border mb-2" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important; font-size: 12.5px; line-height: 1.5; color: var(--text-primary); white-space: pre-line; word-break: break-word;">
                                {{ $k->deskripsi_kunjungan }}
                            </div>

                            <!-- Footer: Bukti & Tombol Aksi -->
                            <div class="pt-2 border-top d-flex align-items-center justify-content-between gap-2" style="border-top-color: var(--border-color) !important;">
                                <div>
                                    @if($k->foto_kunjungan)
                                        <a href="{{ asset('storage/kunjungan/' . $k->foto_kunjungan) }}" target="_blank" class="badge bg-light text-dark border d-flex align-items-center gap-1.5 text-decoration-none py-1.5 px-2.5" style="border-color: var(--border-color) !important;">
                                            <img src="{{ asset('storage/kunjungan/' . $k->foto_kunjungan) }}" class="rounded" width="18" height="18" style="object-fit: cover;" alt="Thumbnail">
                                            <span class="fw-semibold" style="font-size: 11px;">Lihat Foto Bukti</span>
                                        </a>
                                    @else
                                        <span class="text-muted small" style="font-size: 11px;">Tanpa foto</span>
                                    @endif
                                </div>

                                <div class="d-flex gap-1.5 align-items-center ms-auto">
                                    <button type="button" class="btn btn-sm btn-outline-warning font-heading d-flex align-items-center gap-1 px-2.5 py-1" data-bs-toggle="modal" data-bs-target="#editModal_{{ $k->id }}" style="font-size: 12px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Edit</span>
                                    </button>
                                    <form action="{{ route('kunjungan.destroy', $k->id) }}" method="POST" id="deleteKunjunganFormMob_{{ $k->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger px-2.5 py-1" title="Hapus Kunjungan" onclick="window.confirmDelete('deleteKunjunganFormMob_{{ $k->id }}', 'catatan kunjungan {{ addslashes($k->penempatanPkl?->dudi?->nama ?? '') }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-4 text-center">
                            <h6 class="empty-state-title">Belum Ada Catatan Kunjungan</h6>
                            <p class="empty-state-text">Gunakan form di atas untuk mencatat kunjungan pembimbing ke mitra DUDI.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Edit Modals -->
                @foreach($kunjungans as $k)
                    <div class="modal fade text-start" id="editModal_{{ $k->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                    <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Edit Catatan Kunjungan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('kunjungan.update', $k->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body text-start">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Mitra DUDI Bimbingan</label>
                                            <select name="penempatan_pkl_id" class="form-select form-select-sm" required>
                                                @foreach($dudiPlacements as $p)
                                                    <option value="{{ $p->id }}" {{ $p->id == $k->penempatan_pkl_id ? 'selected' : '' }}>
                                                        {{ $p->dudi->nama }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Tanggal Kunjungan</label>
                                            <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $k->tanggal }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Jenis Kunjungan</label>
                                            <select name="jenis_kunjungan" class="form-select form-select-sm" required>
                                                <option value="Penjajakan Kerja Sama" {{ $k->jenis_kunjungan === 'Penjajakan Kerja Sama' ? 'selected' : '' }}>Penjajakan Kerja Sama</option>
                                                <option value="Penyerahan Murid" {{ $k->jenis_kunjungan === 'Penyerahan Murid' ? 'selected' : '' }}>Penyerahan Murid</option>
                                                <option value="Monitoring Berkala" {{ $k->jenis_kunjungan === 'Monitoring Berkala' ? 'selected' : '' }}>Monitoring Berkala</option>
                                                <option value="Penarikan PKL" {{ $k->jenis_kunjungan === 'Penarikan PKL' ? 'selected' : '' }}>Penarikan PKL</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Catatan Kunjungan</label>
                                            <textarea name="deskripsi_kunjungan" class="form-control form-control-sm" rows="4" required>{{ $k->deskripsi_kunjungan }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Foto Bukti Kunjungan (Pilih baru jika ingin mengubah)</label>
                                            <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                                            @if($k->foto_kunjungan)
                                                 <div class="mt-2">
                                                    <small class="text-muted d-block mb-1">Foto saat ini:</small>
                                                    <img src="{{ asset('storage/kunjungan/' . $k->foto_kunjungan) }}" class="rounded border" width="80" height="80" style="object-fit: cover;" alt="Foto Kunjungan">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-sm btn-primary" data-loading-text="Menyimpan...">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($kunjungans->hasPages())
                <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
                    {{ $kunjungans->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
