@extends('layouts.admin')

@section('title', 'Verifikasi Jurnal - PKLku')
@section('page_title', 'Verifikasi Jurnal Bimbingan')

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
    <!-- Filter Status Quick Pills & Bar -->
    <div class="card-premium mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('jurnal.index') }}" class="btn btn-xs font-heading {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius: 9999px; padding: 4px 12px;">
                    Semua
                </a>
                <a href="{{ route('jurnal.index', ['status' => 'pending']) }}" class="btn btn-xs font-heading {{ request('status') === 'pending' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius: 9999px; padding: 4px 12px;">
                    Menunggu Verifikasi
                </a>
                <a href="{{ route('jurnal.index', ['status' => 'disetujui']) }}" class="btn btn-xs font-heading {{ request('status') === 'disetujui' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius: 9999px; padding: 4px 12px;">
                    Disetujui
                </a>
                <a href="{{ route('jurnal.index', ['status' => 'revisi']) }}" class="btn btn-xs font-heading {{ request('status') === 'revisi' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius: 9999px; padding: 4px 12px;">
                    Butuh Revisi
                </a>
                <a href="{{ route('jurnal.index', ['status' => 'ditolak']) }}" class="btn btn-xs font-heading {{ request('status') === 'ditolak' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius: 9999px; padding: 4px 12px;">
                    Ditolak
                </a>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card-premium p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px;">
                <thead class="table-light">
                    <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                        <th class="ps-4" style="width: 100px;">Tanggal</th>
                        <th>Siswa (Kelas)</th>
                        <th>DUDI Tempat PKL</th>
                        <th>Isi Laporan Aktivitas</th>
                        <th class="text-center">Foto Bukti</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4" style="width: 110px;">Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $j)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ \Carbon\Carbon::parse($j->tanggal)->format('d/m/Y') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $j->penempatanPkl?->murid?->nama ?? 'Siswa Terhapus' }}</div>
                                <small class="text-muted">{{ $j->penempatanPkl?->murid?->kelas?->nama ?? '-' }}</small>
                            </td>
                            <td>{{ $j->penempatanPkl?->dudi?->nama ?? 'DUDI Terhapus' }}</td>
                            <td>
                                <div>{{ Str::limit($j->deskripsi_aktivitas, 100) }}</div>
                                @if($j->catatan_verifikasi)
                                    <small class="text-danger d-block mt-1"><strong>Komentar:</strong> {{ $j->catatan_verifikasi }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($j->foto_kegiatan)
                                    @php
                                        $isPdf = Str::endsWith(strtolower($j->foto_kegiatan), '.pdf');
                                    @endphp
                                    <a href="{{ asset('storage/jurnal/' . $j->foto_kegiatan) }}" target="_blank" aria-label="Lihat lampiran bukti jurnal">
                                        @if($isPdf)
                                            <span class="badge bg-danger-light text-danger p-1 font-heading" style="font-size: 11px;">PDF</span>
                                        @else
                                            <img src="{{ asset('storage/jurnal/' . $j->foto_kegiatan) }}" class="rounded border" width="36" height="36" style="object-fit: cover;" alt="Bukti Foto">
                                        @endif
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($j->status_verifikasi === 'disetujui')
                                    <span class="status-badge bg-success-light text-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Disetujui
                                    </span>
                                @elseif($j->status_verifikasi === 'revisi')
                                    <span class="status-badge bg-warning-light text-warning">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Revisi
                                    </span>
                                @elseif($j->status_verifikasi === 'ditolak')
                                    <span class="status-badge bg-danger-light text-danger">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Ditolak
                                    </span>
                                @else
                                    <span class="status-badge bg-secondary-light text-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center align-items-center">
                                    @if($j->status_verifikasi === 'pending' && (auth()->user()->role === 'guru' || auth()->user()->role === 'admin'))
                                        <button class="btn btn-sm btn-outline-primary btn-action" data-bs-toggle="modal" data-bs-target="#reviewModal_{{ $j->id }}" title="Tinjau Jurnal" aria-label="Tinjau Jurnal {{ $j->penempatanPkl?->murid?->nama }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>

                                        <!-- Modal Verifikasi Jurnal -->
                                        <div class="modal fade text-start" id="reviewModal_{{ $j->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-md">
                                                <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                                    <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                                        <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Verifikasi Jurnal Kegiatan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('jurnal.verify', $j->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body" x-data="{ decision: 'disetujui' }">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold text-muted">Aktivitas Siswa</label>
                                                                <div class="p-2 border rounded bg-light small" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                                                                    {{ $j->deskripsi_aktivitas }}
                                                                </div>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="statusSelect_{{ $j->id }}" class="form-label small fw-semibold">Pilih Keputusan</label>
                                                                <select name="status" id="statusSelect_{{ $j->id }}" class="form-select form-select-sm" x-model="decision" required>
                                                                    <option value="disetujui">Setujui Jurnal</option>
                                                                    <option value="revisi">Minta Revisi Jurnal</option>
                                                                    <option value="ditolak">Tolak Jurnal</option>
                                                                </select>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="catatan_{{ $j->id }}" class="form-label small fw-semibold">Catatan / Komentar Guru (Wajib untuk revisi/tolak)</label>
                                                                <textarea name="catatan_verifikasi" id="catatan_{{ $j->id }}" class="form-control form-control-sm" rows="3" placeholder="Masukkan instruksi revisi atau alasan penolakan..." :required="decision === 'revisi' || decision === 'ditolak'"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-sm btn-primary" data-loading-text="Menyimpan...">Simpan Keputusan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(auth()->user()->role === 'guru' || auth()->user()->role === 'admin')
                                        <form action="{{ route('jurnal.destroy', $j->id) }}" method="POST" id="deleteJurnalForm_{{ $j->id }}" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-action" title="Hapus Jurnal" aria-label="Hapus Jurnal {{ $j->penempatanPkl?->murid?->nama }}" onclick="window.confirmDelete('deleteJurnalForm_{{ $j->id }}', 'jurnal kegiatan {{ addslashes($j->penempatanPkl?->murid?->nama ?? 'siswa') }}')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="empty-state py-4">
                                    <div class="empty-state-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h6 class="empty-state-title">Tidak Ada Jurnal</h6>
                                    <p class="empty-state-text">Tidak ditemukan data jurnal siswa untuk kriteria filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($journals->hasPages())
        <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
            {{ $journals->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
