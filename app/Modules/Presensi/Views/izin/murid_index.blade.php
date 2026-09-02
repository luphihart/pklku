@extends('layouts.admin')

@section('title', 'Izin & Sakit - PKLku')
@section('page_title', 'Pengajuan Izin & Sakit')

@section('content')
<div class="container-fluid p-0">
    @if(!$placement)
        <div class="card-premium empty-state text-center py-5">
            <div class="empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h6 class="empty-state-title">Penempatan Belum Aktif</h6>
            <p class="empty-state-text">Akses pengajuan izin atau sakit hanya aktif ketika penempatan Anda telah diselesaikan oleh Admin.</p>
        </div>
    @else
        <div class="row">
            <!-- Apply form -->
            <div class="col-md-5 mb-4" x-data="{
                previewUrl: null,
                fileName: '',
                handleFile(e) {
                    const file = e.target.files[0];
                    if (!file) {
                        this.previewUrl = null;
                        this.fileName = '';
                        return;
                    }
                    this.fileName = file.name;
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (ev) => { this.previewUrl = ev.target.result; };
                        reader.readAsDataURL(file);
                    }
                }
            }">
                <div class="card-premium">
                    <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Form Pengajuan Izin / Sakit</h5>
                    
                    <form action="{{ route('izin.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="penempatan_pkl_id" value="{{ $placement->id }}">

                        <div class="mb-3">
                            <label for="tipe" class="form-label font-heading fw-semibold text-secondary" style="font-size: 13px;">Kategori Pengajuan</label>
                            <select name="tipe" id="tipe" class="form-select form-select-sm" required>
                                <option value="izin">Izin Resmi</option>
                                <option value="sakit">Sakit (Wajib Surat Dokter)</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="tanggal_mulai" class="form-label font-heading fw-semibold text-secondary" style="font-size: 13px;">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="tanggal_selesai" class="form-label font-heading fw-semibold text-secondary" style="font-size: 13px;">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control form-control-sm" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="alasan" class="form-label font-heading fw-semibold text-secondary" style="font-size: 13px;">Alasan / Keterangan</label>
                            <textarea name="alasan" id="alasan" class="form-control form-control-sm" rows="3" placeholder="Tulis alasan berhalangan hadir secara rinci dan jelas..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="surat" class="form-label font-heading fw-semibold text-secondary" style="font-size: 13px;">Surat Pendukung (Wajib, Foto)</label>
                            <input type="file" name="surat" id="surat" class="form-control form-control-sm" accept="image/*" @change="handleFile($event)" required>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Format: JPG, JPEG, PNG (Maks. 2MB)</small>
                            
                            <!-- Live File Preview -->
                            <div class="mt-2" x-show="previewUrl" style="display: none;">
                                <div class="p-2 border rounded d-flex align-items-center gap-2" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                    <img :src="previewUrl" alt="Pratinjau Surat" class="rounded border" width="48" height="48" style="object-fit: cover;">
                                    <div class="text-truncate" style="font-size: 12px;">
                                        <span class="fw-semibold text-dark d-block text-truncate" x-text="fileName"></span>
                                        <span class="text-success" style="font-size: 11px;">Foto siap diunggah</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 font-heading fw-semibold py-2 mt-2" data-loading-text="Mengirim Pengajuan...">Kirim Pengajuan</button>
                    </form>
                </div>
            </div>

            <!-- History panel -->
            <div class="col-md-7 mb-4">
                <div class="card-premium">
                    <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Riwayat Pengajuan</h5>
                    
                    <!-- Desktop Table View (md and up) -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px; min-width: 520px;">
                             <thead>
                                 <tr class="text-muted">
                                     <th style="width: 100px;">Periode</th>
                                     <th style="width: 80px;">Tipe</th>
                                     <th>Alasan</th>
                                     <th class="text-center" style="width: 70px;">Lampiran</th>
                                     <th class="text-center" style="width: 100px;">Status</th>
                                     <th class="text-center" style="width: 60px;">Aksi</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 @forelse($history as $h)
                                     <tr>
                                         <td>
                                             <span class="fw-semibold">{{ \Carbon\Carbon::parse($h->tanggal_mulai)->format('d/m/y') }}</span>
                                             <small class="text-muted d-block">s/d {{ \Carbon\Carbon::parse($h->tanggal_selesai)->format('d/m/y') }}</small>
                                         </td>
                                         <td>
                                             <span class="badge {{ $h->tipe === 'sakit' ? 'bg-danger-light text-danger' : 'bg-info-light text-info' }} text-capitalize">
                                                 {{ $h->tipe }}
                                             </span>
                                         </td>
                                         <td>
                                             <div style="line-height: 1.4; word-break: break-word; white-space: normal;">{{ $h->alasan }}</div>
                                             @if($h->catatan_guru)
                                                 <small class="text-danger d-block mt-1"><strong>Tanggapan Guru:</strong> {{ $h->catatan_guru }}</small>
                                             @endif
                                         </td>
                                         <td class="text-center">
                                             @if($h->surat_pendukung)
                                                 <a href="{{ asset('storage/izin/' . $h->surat_pendukung) }}" target="_blank" class="btn btn-sm btn-outline-secondary btn-action" title="Lihat Lampiran" aria-label="Lihat Lampiran Surat">
                                                     <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                     </svg>
                                                 </a>
                                             @else
                                                 <span class="text-muted small">-</span>
                                             @endif
                                         </td>
                                         <td class="text-center">
                                             @if($h->status_approval === 'disetujui')
                                                 <span class="status-badge bg-success-light text-success">
                                                     <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                     Disetujui
                                                 </span>
                                             @elseif($h->status_approval === 'ditolak')
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
                                         <td class="text-center">
                                             @if($h->status_approval === 'ditolak')
                                                 <a href="{{ route('izin.edit', $h->id) }}" class="btn btn-sm btn-outline-warning btn-action" title="Revisi Pengajuan" aria-label="Revisi Pengajuan Izin">
                                                     <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                     </svg>
                                                 </a>
                                             @else
                                                 <span class="text-muted small">-</span>
                                             @endif
                                         </td>
                                     </tr>
                                 @empty
                                     <tr>
                                         <td colspan="6" class="text-center py-4">
                                             <div class="empty-state py-4">
                                                 <div class="empty-state-icon">
                                                     <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                     </svg>
                                                 </div>
                                                 <h6 class="empty-state-title">Belum Ada Pengajuan Izin</h6>
                                                 <p class="empty-state-text">Gunakan form di sebelah kiri jika Anda berhalangan hadir pada kegiatan PKL.</p>
                                             </div>
                                         </td>
                                     </tr>
                                 @endforelse
                             </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card List View (Visible on smartphone < md) -->
                    <div class="d-md-none">
                        @forelse($history as $h)
                            <div class="card p-3 mb-3 border rounded shadow-xs" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                                <!-- Header: Kategori & Status -->
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                    <span class="badge {{ $h->tipe === 'sakit' ? 'bg-danger-light text-danger' : 'bg-primary-light text-primary' }} text-capitalize px-2 py-1" style="font-size: 11px;">
                                        {{ $h->tipe }}
                                    </span>
                                    <div>
                                        @if($h->status_approval === 'disetujui')
                                            <span class="status-badge bg-success-light text-success" style="font-size: 10.5px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Disetujui
                                            </span>
                                        @elseif($h->status_approval === 'ditolak')
                                            <span class="status-badge bg-danger-light text-danger" style="font-size: 10.5px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Ditolak
                                            </span>
                                        @else
                                            <span class="status-badge bg-warning-light text-warning" style="font-size: 10.5px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Pending
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Periode Tanggal -->
                                <div class="text-secondary fw-semibold mb-2" style="font-size: 12px;">
                                    Periode: {{ \Carbon\Carbon::parse($h->tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($h->tanggal_selesai)->format('d/m/Y') }}
                                </div>

                                <!-- Alasan -->
                                <div class="p-2.5 rounded bg-light border mb-2" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important; font-size: 12.5px; line-height: 1.5; color: var(--text-primary); white-space: pre-line; word-break: break-word;">
                                    {{ $h->alasan }}
                                </div>

                                @if($h->catatan_guru)
                                    <div class="p-2 rounded mb-2 border" style="background-color: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.2) !important; font-size: 12px; line-height: 1.4;">
                                        <strong class="text-danger">Tanggapan Guru:</strong>
                                        <div class="text-dark mt-0.5" style="white-space: pre-line;">{{ $h->catatan_guru }}</div>
                                    </div>
                                @endif

                                <!-- Footer Lampiran & Aksi -->
                                <div class="pt-2 border-top d-flex align-items-center justify-content-between gap-2" style="border-top-color: var(--border-color) !important;">
                                    <div>
                                        @if($h->surat_pendukung)
                                            <a href="{{ asset('storage/izin/' . $h->surat_pendukung) }}" target="_blank" class="badge bg-light text-dark border d-flex align-items-center gap-1.5 text-decoration-none py-1.5 px-2.5" style="border-color: var(--border-color) !important;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <span class="fw-semibold" style="font-size: 11px;">Buka Lampiran</span>
                                            </a>
                                        @else
                                            <span class="text-muted small" style="font-size: 11px;">Tanpa lampiran</span>
                                        @endif
                                    </div>

                                    @if($h->status_approval === 'ditolak')
                                        <a href="{{ route('izin.edit', $h->id) }}" class="btn btn-sm btn-outline-warning font-heading d-flex align-items-center gap-1 px-3 py-1" style="font-size: 12px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            <span>Revisi</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="empty-state py-4 text-center">
                                <h6 class="empty-state-title">Belum Ada Pengajuan Izin</h6>
                                <p class="empty-state-text">Gunakan form di atas jika Anda berhalangan hadir pada kegiatan PKL.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
