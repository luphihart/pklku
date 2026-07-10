@extends('layouts.admin')

@section('title', 'Jurnal Kegiatan - PKLku')
@section('page_title', 'Jurnal Aktivitas Harian')

@section('content')
<div class="container-fluid p-0">
    @if(!$placement)
        <div class="card-premium text-center py-5">
            <span class="text-muted d-block">Pemberitahuan: Anda belum ditempatkan di DUDI manapun.</span>
            <small class="text-muted">Akses pengisian jurnal hanya aktif ketika penempatan Anda telah diselesaikan oleh Admin.</small>
        </div>
    @else
        <div class="row">
            <!-- Journal submission form -->
            <div class="col-md-5 mb-4">
                <div class="card-premium">
                    <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Tulis Jurnal Baru</h5>
                    
                    <form action="{{ route('jurnal.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="penempatan_pkl_id" value="{{ $placement->id }}">

                        <div class="mb-3">
                            <label for="tanggal" class="form-label small fw-semibold">Tanggal Kegiatan</label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi_aktivitas" class="form-label small fw-semibold">Rincian Aktivitas Harian</label>
                            <textarea name="deskripsi_aktivitas" id="deskripsi_aktivitas" class="form-control form-control-sm" rows="5" placeholder="Jelaskan secara detail pekerjaan, teknologi, atau modul yang dipelajari hari ini..." required></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="foto" class="form-label small fw-semibold">Bukti Kegiatan (Wajib, Foto/PDF)</label>
                            <input type="file" name="foto" id="foto" class="form-control form-control-sm" accept="image/*, application/pdf" required>
                            <small class="text-muted" style="font-size: 10px;">Format: JPG, JPEG, PNG, atau PDF (Maks. 2MB)</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 font-heading">Kirim Jurnal Harian</button>
                    </form>
                </div>
            </div>

            <!-- History panel -->
            <div class="col-md-7 mb-4">
                <div class="card-premium">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                        <h5 class="fw-bold font-heading m-0 text-dark">Riwayat Jurnal Harian</h5>
                        <a href="{{ route('laporan.murid_jurnal_pdf') }}" class="btn btn-xs btn-outline-primary d-flex align-items-center" style="font-size: 11px; padding: 4px 8px;">
                            <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Unduh PDF
                        </a>
                    </div>
                    
                    <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px;">
                            <thead>
                                <tr class="text-muted">
                                    <th style="width: 100px;">Tanggal</th>
                                    <th>Aktivitas</th>
                                    <th class="text-center">Bukti</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 70px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($journals as $j)
                                    <tr>
                                        <td class="fw-semibold">{{ \Carbon\Carbon::parse($j->tanggal)->format('d/m/y') }}</td>
                                        <td>
                                            <div>{{ Str::limit($j->deskripsi_aktivitas, 60) }}</div>
                                            @if($j->catatan_verifikasi)
                                                <small class="text-danger d-block mt-1"><strong>Catatan Guru:</strong> {{ $j->catatan_verifikasi }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($j->foto_kegiatan)
                                                @php
                                                    $isPdf = Str::endsWith(strtolower($j->foto_kegiatan), '.pdf');
                                                @endphp
                                                <a href="{{ asset('storage/jurnal/' . $j->foto_kegiatan) }}" target="_blank">
                                                    @if($isPdf)
                                                        <span class="btn btn-xs btn-outline-info p-1 font-heading" style="font-size: 11px;">PDF</span>
                                                    @else
                                                        <img src="{{ asset('storage/jurnal/' . $j->foto_kegiatan) }}" class="rounded border" width="36" height="36" style="object-fit: cover;">
                                                    @endif
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $j->status_verifikasi === 'disetujui' ? 'bg-success' : ($j->status_verifikasi === 'ditolak' ? 'bg-danger' : ($j->status_verifikasi === 'revisi' ? 'bg-warning text-dark' : 'bg-secondary')) }}">
                                                {{ $j->status_verifikasi }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if(in_array($j->status_verifikasi, ['pending', 'revisi']))
                                                <a href="{{ route('jurnal.edit', $j->id) }}" class="btn btn-sm btn-outline-warning p-1" title="Edit Jurnal">
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
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada jurnal yang ditulis.</td>
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
