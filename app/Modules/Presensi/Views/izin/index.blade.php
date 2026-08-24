@extends('layouts.admin')

@section('title', 'Verifikasi Izin - PKLku')
@section('page_title', 'Verifikasi Izin & Sakit Murid')

@section('content')
<div class="container-fluid p-0">
    <div class="card-premium p-0 overflow-hidden">
        <div class="p-3 border-bottom" style="border-bottom-color: var(--border-color) !important;">
            <h6 class="fw-bold m-0 text-dark dark-text-light">Daftar Pengajuan Cuti / Izin Murid</h6>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px;">
                <thead class="table-light">
                    <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                        <th class="ps-4">Siswa (Kelas)</th>
                        <th>Mitra DUDI</th>
                        <th>Kategori</th>
                        <th>Tanggal Cuti</th>
                        <th>Alasan Pengajuan</th>
                        <th class="text-center">Lampiran</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $p)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $p->penempatanPkl?->murid?->nama ?? 'Siswa Terhapus' }}</div>
                                <small class="text-muted">{{ $p->penempatanPkl?->murid?->kelas?->nama ?? '-' }}</small>
                            </td>
                            <td>{{ $p->penempatanPkl?->dudi?->nama ?? 'DUDI Terhapus' }}</td>
                            <td class="text-capitalize">{{ $p->tipe }}</td>
                            <td>
                                <div>{{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/y') }}</div>
                                <small class="text-muted">s/d {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/y') }}</small>
                            </td>
                            <td>
                                <div>{{ Str::limit($p->alasan, 50) }}</div>
                                @if($p->catatan_guru)
                                    <small class="text-danger d-block mt-1"><strong>Tanggapan Guru:</strong> {{ $p->catatan_guru }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($p->surat_pendukung)
                                    <a href="{{ asset('storage/izin/' . $p->surat_pendukung) }}" target="_blank" class="btn btn-sm btn-outline-secondary btn-action" title="Lihat Lampiran" aria-label="Lihat Lampiran Surat">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($p->status_approval === 'disetujui')
                                    <span class="status-badge bg-success-light text-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Disetujui
                                    </span>
                                @elseif($p->status_approval === 'ditolak')
                                    <span class="status-badge bg-danger-light text-danger">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Ditolak
                                    </span>
                                @else
                                    <span class="status-badge bg-warning-light text-warning">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center align-items-center">
                                    @if($p->status_approval === 'pending' && (auth()->user()->role === 'guru' || auth()->user()->role === 'admin'))
                                        <button class="btn btn-sm btn-outline-primary btn-action" data-bs-toggle="modal" data-bs-target="#verifyModal_{{ $p->id }}" title="Tinjau & Verifikasi" aria-label="Tinjau Izin {{ $p->penempatanPkl?->murid?->nama }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                        
                                        <!-- Modal Verifikasi -->
                                        <div class="modal fade text-start" id="verifyModal_{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                                    <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                                        <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Verifikasi Izin Murid</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('izin.review', $p->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p class="small text-muted mb-3">Tinjau izin untuk <strong>{{ $p->penempatanPkl?->murid?->nama }}</strong>.</p>
                                                            
                                                            <div class="mb-3">
                                                                <label for="statusSelect_{{ $p->id }}" class="form-label small fw-semibold">Pilih Keputusan</label>
                                                                <select name="status" id="statusSelect_{{ $p->id }}" class="form-select form-select-sm" required>
                                                                    <option value="disetujui">Setujui Pengajuan</option>
                                                                    <option value="ditolak">Tolak Pengajuan</option>
                                                                </select>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="catatan_{{ $p->id }}" class="form-label small fw-semibold">Catatan Tanggapan (Opsional)</label>
                                                                <textarea name="catatan_guru" id="catatan_{{ $p->id }}" class="form-control form-control-sm" rows="3" placeholder="Tulis catatan persetujuan atau alasan penolakan..."></textarea>
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

                                    @if(auth()->user()->role === 'admin')
                                        <form action="{{ route('izin.destroy', $p->id) }}" method="POST" id="deleteIzinForm_{{ $p->id }}" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-action" title="Hapus Pengajuan" aria-label="Hapus Pengajuan Izin {{ $p->penempatanPkl?->murid?->nama }}" onclick="window.confirmDelete('deleteIzinForm_{{ $p->id }}', 'pengajuan izin {{ addslashes($p->penempatanPkl?->murid?->nama ?? 'siswa') }}')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    @if($p->status_approval !== 'pending' && auth()->user()->role !== 'admin')
                                        <span class="text-muted small">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="empty-state py-4">
                                    <div class="empty-state-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h6 class="empty-state-title">Tidak Ada Pengajuan Izin</h6>
                                    <p class="empty-state-text">Saat ini belum ada data pengajuan cuti/izin murid yang perlu ditinjau.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($permissions->hasPages())
        <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
            {{ $permissions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
