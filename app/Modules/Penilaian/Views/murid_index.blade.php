@extends('layouts.admin')

@section('title', 'Nilai PKL Saya - PKLku')
@section('page_title', 'Laporan Nilai Kelulusan PKL')

@section('content')
<div class="container-fluid p-0">
    @if(!$placement)
        <div class="card-premium text-center py-5">
            <span class="text-muted d-block">Pemberitahuan: Anda belum ditempatkan di DUDI manapun.</span>
            <small class="text-muted">Rapor nilai PKL hanya akan muncul ketika Anda telah selesai/sedang melaksanakan PKL.</small>
        </div>
    @elseif(!$evaluation)
        <div class="card-premium text-center py-5">
            <span class="text-muted d-block">Nilai PKL Anda belum dimasukkan.</span>
            <small class="text-muted">Guru Pembimbing atau Instansi DUDI saat ini sedang memproses penilaian akhir Anda.</small>
        </div>
    @else
        <div class="row">
            <!-- Summary Card -->
            <div class="col-md-4 mb-4">
                <div class="card-premium text-center py-4 d-flex flex-column align-items-center justify-content-center">
                    <span class="text-uppercase small fw-bold text-muted mb-2 font-heading" style="letter-spacing: 1px;">Nilai Akhir PKL</span>
                    <h1 class="display-3 fw-bold text-primary font-heading mb-1">{{ number_format($evaluation->nilai_akhir, 2) }}</h1>

                    <a href="{{ route('laporan.nilai_pdf', $placement->id) }}" class="btn btn-primary btn-sm font-heading mt-2 d-inline-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Rapor (PDF)
                    </a>

                    <p class="text-secondary small mt-3 px-3">
                        Nilai gabungan berdasarkan persentase bobot penilaian Guru Pembimbing Sekolah dan Pembimbing Lapangan DUDI.
                    </p>
                </div>
            </div>

            <!-- Detail Breakdown Card -->
            <div class="col-md-8 mb-4">
                <div class="card-premium">
                    <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Rincian Komponen Nilai</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" style="font-size: 13px; color: var(--text-primary);">
                            <thead class="table-light">
                                <tr class="font-heading small">
                                    <th style="width: 8%; text-align: center;">No.</th>
                                    <th style="width: 50%;">Tujuan Pembelajaran / Indikator</th>
                                    <th style="width: 15%; text-align: center;">Nilai</th>
                                    <th style="width: 27%;">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tps as $tp)
                                    <!-- TP Header Row -->
                                    @php
                                        $tpComment = $evaluation && isset($evaluation->keterangan_tp_json[$tp->id]) ? $evaluation->keterangan_tp_json[$tp->id] : '-';
                                        $hasDivider = $tp->indikators->contains(fn($ind) => $ind->nomor_urut === '3.7');
                                        $rowspan = 1 + count($tp->indikators) + ($hasDivider ? 1 : 0);
                                    @endphp
                                    <tr class="table-secondary fw-bold text-dark" style="background-color: rgba(15, 23, 42, 0.05);">
                                        <td style="text-align: center;">{{ $tp->nomor }}</td>
                                        <td>{{ $tp->nama }}</td>
                                        <td></td>
                                        <td rowspan="{{ $rowspan }}" style="vertical-align: top; background-color: #fff;" class="text-secondary small">{{ $tpComment }}</td>
                                    </tr>
                                    
                                    <!-- TP Indicators -->
                                    @php
                                        $pushedDivider = false;
                                    @endphp
                                    @foreach($tp->indikators as $ind)
                                        @if($ind->nomor_urut == '3.7' && !$pushedDivider)
                                            <tr class="table-info fw-bold text-center small text-secondary">
                                                <td colspan="3" style="font-size: 11px;">Point 3.7 kebawah, diisi oleh sekolah (Guru Pembimbing)</td>
                                            </tr>
                                            @php $pushedDivider = true; @endphp
                                        @endif
                                        
                                        @php
                                            $isGuru = $ind->tipe === 'guru';
                                            $score = '-';
                                            
                                            if ($evaluation) {
                                                if ($isGuru && isset($evaluation->nilai_guru_json[$ind->id])) {
                                                    $item = $evaluation->nilai_guru_json[$ind->id];
                                                    $score = is_array($item) ? ($item['nilai'] ?? '-') : $item;
                                                } elseif (!$isGuru && isset($evaluation->nilai_industri_json[$ind->id])) {
                                                    $item = $evaluation->nilai_industri_json[$ind->id];
                                                    $score = is_array($item) ? ($item['nilai'] ?? '-') : $item;
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td style="text-align: center;" class="text-secondary fw-semibold">{{ $ind->nomor_urut }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $ind->nama }}</div>
                                                @if($ind->deskripsi)
                                                    <div class="text-muted small" style="font-size: 11px;">{{ $ind->deskripsi }}</div>
                                                @endif
                                            </td>
                                            <td style="text-align: center;" class="fw-bold text-primary">{{ $score }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($evaluation->catatan)
                        <div class="mt-4 p-3 border rounded bg-light" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                            <strong class="small fw-semibold font-heading d-block mb-1 text-dark dark-text-light">Catatan Selama PKL (Keseluruhan):</strong>
                            <p class="text-secondary small m-0">{{ $evaluation->catatan }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
