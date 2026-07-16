@extends('layouts.admin')

@section('title', 'Izin & Sakit - PKLku')
@section('page_title', 'Pengajuan Izin & Sakit')

@section('content')
<div class="container-fluid p-0">
    @if(!$placement)
        <div class="card-premium text-center py-5">
            <span class="text-muted d-block">Pemberitahuan: Anda belum ditempatkan di DUDI manapun.</span>
            <small class="text-muted">Akses pengajuan izin sakit hanya aktif ketika penempatan Anda telah diselesaikan oleh Admin.</small>
        </div>
    @else
        <div class="row">
            <!-- Apply form -->
            <div class="col-md-5 mb-4">
                <div class="card-premium">
                    <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Form Pengajuan</h5>
                    
                    <form action="{{ route('izin.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="penempatan_pkl_id" value="{{ $placement->id }}">

                        <div class="mb-3">
                            <label for="tipe" class="form-label small fw-semibold">Kategori Pengajuan</label>
                            <select name="tipe" id="tipe" class="form-select form-select-sm" required>
                                <option value="izin">Izin Resmi</option>
                                <option value="sakit">Sakit (Butuh Surat Dokter)</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="tanggal_mulai" class="form-label small fw-semibold">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="tanggal_selesai" class="form-label small fw-semibold">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control form-control-sm" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="alasan" class="form-label small fw-semibold">Alasan / Penjelasan</label>
                            <textarea name="alasan" id="alasan" class="form-control form-control-sm" rows="3" placeholder="Tulis alasan berhalangan hadir secara lengkap..." required></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="surat" class="form-label small fw-semibold">Surat Pendukung (Wajib, Foto)</label>
                            <input type="file" name="surat" id="surat" class="form-control form-control-sm" accept="image/*" required>
                            <small class="text-muted" style="font-size: 10px;">Format: JPG, JPEG, PNG (Maks. 2MB)</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 font-heading">Kirim Pengajuan</button>
                    </form>
                </div>
            </div>

            <!-- History panel -->
            <div class="col-md-7 mb-4">
                <div class="card-premium">
                    <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Riwayat Pengajuan</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px;">
                             <thead>
                                 <tr class="text-muted">
                                     <th>Periode Tanggal</th>
                                     <th>Tipe</th>
                                     <th>Alasan</th>
                                     <th class="text-center">Lampiran</th>
                                     <th class="text-center">Status</th>
                                     <th class="text-center" style="width: 70px;">Aksi</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 @forelse($history as $h)
                                     <tr>
                                         <td>
                                             <span class="fw-semibold">{{ \Carbon\Carbon::parse($h->tanggal_mulai)->format('d/m/y') }}</span>
                                             <small class="text-muted d-block">s/d {{ \Carbon\Carbon::parse($h->tanggal_selesai)->format('d/m/y') }}</small>
                                         </td>
                                         <td class="text-capitalize">{{ $h->tipe }}</td>
                                         <td>
                                             <div>{{ Str::limit($h->alasan, 40) }}</div>
                                             @if($h->catatan_guru)
                                                 <small class="text-danger d-block mt-1"><strong>Tanggapan Guru:</strong> {{ $h->catatan_guru }}</small>
                                             @endif
                                         </td>
                                         <td class="text-center">
                                             @if($h->surat_pendukung)
                                                 <a href="{{ asset('storage/izin/' . $h->surat_pendukung) }}" target="_blank" class="btn btn-sm btn-outline-secondary p-1" title="Lihat Lampiran">
                                                     <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                     </svg>
                                                 </a>
                                             @else
                                                 <span class="text-muted small">-</span>
                                             @endif
                                         </td>
                                         <td class="text-center">
                                             <span class="badge {{ $h->status_approval === 'disetujui' ? 'bg-success' : ($h->status_approval === 'ditolak' ? 'bg-danger' : 'bg-warning') }}">
                                                 {{ $h->status_approval }}
                                             </span>
                                         </td>
                                         <td class="text-center">
                                             @if($h->status_approval === 'ditolak')
                                                 <a href="{{ route('izin.edit', $h->id) }}" class="btn btn-sm btn-outline-warning p-1" title="Revisi Pengajuan">
                                                     <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                         <td colspan="6" class="text-center py-4 text-muted">Belum ada pengajuan cuti/izin.</td>
                                     </tr>
                                 @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
