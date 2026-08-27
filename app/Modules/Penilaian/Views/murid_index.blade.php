@extends('layouts.admin')

@section('title', 'Nilai PKL Saya - PKLku')
@section('page_title', 'Laporan Nilai Kelulusan PKL')

@section('content')
<div class="container-fluid p-0" x-data="{
    filePreview: null,
    fileName: '',
    isPdf: false,
    handleFileSelect(e) {
        const file = e.target.files[0];
        if (!file) {
            this.filePreview = null;
            this.fileName = '';
            return;
        }
        this.fileName = file.name;
        if (file.type.startsWith('image/')) {
            this.isPdf = false;
            const reader = new FileReader();
            reader.onload = (ev) => { this.filePreview = ev.target.result; };
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            this.isPdf = true;
            this.filePreview = 'pdf';
        }
    }
}">
    @if(!$placement)
        <div class="card-premium text-center py-5">
            <span class="text-muted d-block">Pemberitahuan: Anda belum ditempatkan di DUDI manapun.</span>
            <small class="text-muted">Rapor nilai PKL hanya akan muncul ketika Anda telah selesai/sedang melaksanakan PKL.</small>
        </div>
    @elseif(!$isMasaPenilaianOpen && (!$evaluation || !$evaluation->nilai_akhir))
        <!-- Masa Penilaian Ditutup Banner -->
        <div class="card-premium text-center py-5" style="border-left: 4px solid var(--secondary) !important;">
            <div class="d-inline-flex p-3 rounded-circle bg-secondary-light text-secondary mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h5 class="fw-bold font-heading text-dark m-0">Masa Pengisian Penilaian PKL Sedang Ditutup</h5>
            <p class="text-secondary small mt-2 mb-0" style="max-width: 500px; margin: auto;">
                Masa pengisian dan pengesahan nilai PKL saat ini belum dibuka atau telah ditutup oleh Admin Sekolah. Silakan tunggu informasi jadwal penilaian dari koordinator PKL.
            </p>
        </div>
    @elseif($evaluation && $evaluation->nilai_akhir > 0 && $evaluation->status_nilai_industri === 'diverifikasi')
        <!-- Final Evaluation Completed & Verified -->
        <div class="row">
            <!-- Summary Card -->
            <div class="col-md-4 mb-4">
                <div class="card-premium text-center py-4 d-flex flex-column align-items-center justify-content-center">
                    <span class="text-uppercase small fw-bold text-muted mb-2 font-heading" style="letter-spacing: 1px;">Nilai Akhir PKL</span>
                    <h1 class="display-3 fw-bold text-primary font-heading mb-1">{{ number_format($evaluation->nilai_akhir, 2) }}</h1>
                    <span class="badge bg-success-light text-success font-heading mb-3 px-3 py-1" style="font-size: 13px;">Predikat: {{ $evaluation->predikat }} (LULUS)</span>

                    <a href="{{ route('laporan.nilai_pdf', $placement->id) }}" class="btn btn-primary btn-sm font-heading mt-1 d-inline-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Rapor (PDF)
                    </a>

                    <p class="text-secondary small mt-3 px-3">
                        Nilai gabungan berdasarkan persentase bobot penilaian Guru Pembimbing Sekolah (R1: {{ number_format($evaluation->rata_nilai_guru, 2) }}) dan Pembimbing Lapangan DUDI (R2: {{ number_format($evaluation->rata_nilai_industri, 2) }}).
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
                                    @php
                                        $tpComment = $evaluation && isset($evaluation->keterangan_tp_json[$tp->id]) ? $evaluation->keterangan_tp_json[$tp->id] : '-';
                                        $rowspan = 1 + count($tp->indikators);
                                    @endphp
                                    <tr class="table-secondary fw-bold text-dark" style="background-color: rgba(15, 23, 42, 0.05);">
                                        <td style="text-align: center;">{{ $tp->nomor }}</td>
                                        <td>{{ $tp->nama }}</td>
                                        <td></td>
                                        <td rowspan="{{ $rowspan }}" style="vertical-align: top; background-color: #fff;" class="text-secondary small">{{ $tpComment }}</td>
                                    </tr>
                                    
                                    @foreach($tp->indikators as $ind)
                                        
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
    @else
        <!-- Student Input Form for DUDI Marks & Evidence -->
        <div class="row">
            <div class="col-lg-8 mx-auto mb-4">
                @if($evaluation && $evaluation->status_nilai_industri === 'diajukan')
                    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-3 mb-4" role="alert" style="background-color: rgba(79, 70, 229, 0.1); color: var(--accent-primary); border-left: 4px solid var(--accent-primary) !important;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <strong>Nilai DUDI Telah Dikirim</strong>
                            <div class="small">Nilai DUDI dan bukti lembar fisik berhasil dikirim. Guru Pembimbing Sekolah saat ini sedang memeriksa dan memverifikasi nilai Anda sebelum diterbitkan rapor kelulusan. Anda masih dapat memperbarui data jika terdapat kesalahan.</div>
                        </div>
                    </div>
                @endif

                <div class="card-premium">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                        <div>
                            <h5 class="fw-bold font-heading m-0 text-dark">Input Nilai Pembimbing DUDI / Industri</h5>
                            <small class="text-muted">Masukkan nilai sesuai lembar fisik/sertifikat yang diberikan oleh instruktur DUDI Anda.</small>
                        </div>
                        <span class="badge bg-primary-light text-primary font-heading px-3 py-1">Mitra: {{ $placement->dudi?->nama }}</span>
                    </div>

                    <form action="{{ route('penilaian.store_murid') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- List Indikator DUDI -->
                        <div class="mb-4">
                            <h6 class="fw-bold font-heading text-dark mb-3">1. Isian Nilai Indikator Industri (Skala 0 - 100)</h6>
                            <div class="table-responsive border rounded mb-3">
                                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 10%; text-align: center;">No.</th>
                                            <th style="width: 65%;">Indikator Penilaian DUDI</th>
                                            <th style="width: 25%; text-align: center;">Nilai Angka (0-100)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $indCounter = 1; @endphp
                                        @foreach($tps as $tp)
                                            @php
                                                $dudiIndikators = $tp->indikators->filter(fn($i) => $i->tipe === 'industri');
                                            @endphp
                                            @if($dudiIndikators->count() > 0)
                                                <tr class="table-secondary fw-bold">
                                                    <td colspan="3" class="ps-3 text-secondary" style="font-size: 12px;">TP: {{ $tp->nama }}</td>
                                                </tr>
                                                @foreach($dudiIndikators as $ind)
                                                    @php
                                                        $currentVal = '';
                                                        if ($evaluation && isset($evaluation->nilai_industri_json[$ind->id])) {
                                                            $item = $evaluation->nilai_industri_json[$ind->id];
                                                            $currentVal = is_array($item) ? ($item['nilai'] ?? '') : $item;
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center fw-semibold text-muted">{{ $ind->nomor_urut ?: $indCounter++ }}</td>
                                                        <td>
                                                            <div class="fw-bold text-dark">{{ $ind->nama }}</div>
                                                            @if($ind->deskripsi)
                                                                <small class="text-muted" style="font-size: 11px;">{{ $ind->deskripsi }}</small>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="number" step="0.01" min="0" max="100" name="nilai_industri[{{ $ind->id }}]" class="form-control form-control-sm text-center fw-bold text-primary mx-auto" style="max-width: 120px;" value="{{ old('nilai_industri.' . $ind->id, $currentVal) }}" placeholder="0 - 100" required>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Upload Bukti Fisik Lembar Nilai -->
                        <div class="mb-4">
                            <h6 class="fw-bold font-heading text-dark mb-1">2. Upload Bukti Lembar Nilai Fisik DUDI</h6>
                            <small class="text-muted d-block mb-2">Unggah foto atau scan PDF lembar penilaian fisik yang telah ditandatangani / dicap oleh pihak DUDI.</small>
                            
                            @if($evaluation && $evaluation->bukti_nilai_industri)
                                @php
                                    $isPdfProof = Str::endsWith(strtolower($evaluation->bukti_nilai_industri), '.pdf');
                                @endphp
                                <div class="mb-3 p-3 border rounded d-flex align-items-center justify-content-between" style="background-color: var(--bg-canvas);">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($isPdfProof)
                                            <span class="badge bg-danger-light text-danger p-2 font-heading">PDF</span>
                                        @else
                                            <img src="{{ asset('storage/penilaian/' . $evaluation->bukti_nilai_industri) }}" class="rounded border" width="50" height="50" style="object-fit: cover;">
                                        @endif
                                        <div>
                                            <span class="fw-bold d-block text-dark small">Berkas Bukti Terunggah:</span>
                                            <a href="{{ asset('storage/penilaian/' . $evaluation->bukti_nilai_industri) }}" target="_blank" class="small text-primary text-decoration-none">Lihat Lampiran Saat Ini</a>
                                        </div>
                                    </div>
                                    <span class="badge bg-success-light text-success">Sudah Ada Berkas</span>
                                </div>
                            @endif

                            <input type="file" name="foto_bukti" id="foto_bukti" class="form-control form-control-sm" accept="image/*, application/pdf" @change="handleFileSelect($event)" {{ $evaluation && $evaluation->bukti_nilai_industri ? '' : 'required' }}>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Format: JPG, JPEG, PNG, atau PDF (Maks. 2MB). {{ $evaluation && $evaluation->bukti_nilai_industri ? 'Pilih file baru jika ingin mengganti bukti lama.' : '' }}</small>
                            
                            <!-- Live File Preview -->
                            <div class="mt-2" x-show="filePreview" style="display: none;">
                                <div class="p-2 border rounded d-flex align-items-center gap-2" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                    <template x-if="!isPdf && filePreview">
                                        <img :src="filePreview" alt="Pratinjau Bukti" class="rounded border" width="48" height="48" style="object-fit: cover;">
                                    </template>
                                    <template x-if="isPdf">
                                        <div class="p-2 bg-danger-light text-danger rounded font-heading fw-bold" style="font-size: 12px;">PDF</div>
                                    </template>
                                    <div class="text-truncate" style="font-size: 12px;">
                                        <span class="fw-semibold text-dark d-block text-truncate" x-text="fileName"></span>
                                        <span class="text-success" style="font-size: 11px;">Siap diunggah</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 font-heading fw-semibold py-2" data-loading-text="Menyimpan Nilai...">
                            {{ $evaluation && $evaluation->status_nilai_industri === 'diajukan' ? 'Perbarui Nilai DUDI & Berkas' : 'Kirim Nilai DUDI & Berkas Bukti' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
