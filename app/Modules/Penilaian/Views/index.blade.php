@extends('layouts.admin')

@section('title', 'Penilaian Siswa - PKLku')
@section('page_title', 'Evaluasi Nilai Kelulusan Murid')

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
    @if(auth()->user()->role === 'admin')
        <!-- Admin Assessment Switch -->
        <div class="card-premium mb-3 p-3 d-flex flex-row align-items-center justify-content-between flex-wrap gap-2" style="background-color: var(--bg-card); border-left: 4px solid {{ $isMasaPenilaianOpen ? 'var(--success)' : 'var(--danger)' }} !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="p-2 rounded-circle {{ $isMasaPenilaianOpen ? 'bg-success-light text-success' : 'bg-danger-light text-danger' }} d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    @if($isMasaPenilaianOpen)
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    @endif
                </div>
                <div>
                    <span class="text-muted text-uppercase fw-bold d-block" style="font-size: 11px; letter-spacing: 0.5px;">Status Masa Pengisian Nilai PKL</span>
                    @if($isMasaPenilaianOpen)
                        <span class="fw-bold text-success font-heading" style="font-size: 15px;">Masa Penilaian Sedang DIBUKA (Aktif)</span>
                        <small class="text-muted d-block" style="font-size: 11px;">Siswa dapat menginput nilai DUDI dan Guru Pembimbing dapat mengesahkan nilai.</small>
                    @else
                        <span class="fw-bold text-danger font-heading" style="font-size: 15px;">Masa Penilaian Sedang DITUTUP (Terkunci)</span>
                        <small class="text-muted d-block" style="font-size: 11px;">Form pengisian nilai murid dinonaktifkan sementara waktu.</small>
                    @endif
                </div>
            </div>
            <div>
                <form action="{{ route('penilaian.toggle_status') }}" method="POST" class="d-inline">
                    @csrf
                    @if($isMasaPenilaianOpen)
                        <button type="submit" class="btn btn-sm btn-outline-danger font-heading fw-semibold d-flex align-items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Kunci / Tutup Penilaian
                        </button>
                    @else
                        <button type="submit" class="btn btn-sm btn-success font-heading fw-semibold d-flex align-items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                            Buka Masa Penilaian
                        </button>
                    @endif
                </form>
            </div>
        </div>
    @endif

    <div class="card-premium p-0 overflow-hidden">
        <div class="p-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2" style="border-bottom-color: var(--border-color) !important;">
            <h6 class="fw-bold m-0 text-dark dark-text-light">
                {{ auth()->user()->role === 'guru' ? 'Daftar Nilai PKL Siswa Bimbingan' : 'Daftar Nilai PKL Semua Murid' }}
            </h6>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('penilaian.template') }}" class="btn btn-sm btn-outline-success font-heading d-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Template Excel
                </a>
                <button type="button" class="btn btn-sm btn-success font-heading d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import Nilai Excel
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px;">
                <thead class="table-light">
                    <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                        <th class="ps-4">Siswa (NIS)</th>
                        <th>Kelas</th>
                        <th>Mitra DUDI</th>
                        <th class="text-center">Status DUDI</th>
                        <th class="text-center">Rata Guru (R1)</th>
                        <th class="text-center">Rata DUDI (R2)</th>
                        <th class="text-center">Nilai Akhir</th>
                        <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($placements as $p)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $p->murid?->nama ?? 'Siswa Terhapus' }}</div>
                                <small class="text-muted">{{ $p->murid?->nis ?? '-' }}</small>
                            </td>
                            <td>{{ $p->murid?->kelas?->nama ?? '-' }}</td>
                            <td>{{ $p->dudi?->nama ?? 'DUDI Terhapus' }}</td>
                            <td class="text-center">
                                @if($p->penilaianPkl && $p->penilaianPkl->status_nilai_industri === 'diverifikasi')
                                    <span class="badge bg-success-light text-success">Disahkan</span>
                                @elseif($p->penilaianPkl && $p->penilaianPkl->status_nilai_industri === 'diajukan')
                                    <span class="badge bg-info-light text-info" title="Murid telah input nilai DUDI, siap diverifikasi">Diajukan Siswa</span>
                                @elseif($p->penilaianPkl && $p->penilaianPkl->rata_nilai_guru > 0)
                                    <span class="badge bg-warning-light text-warning">Menunggu DUDI</span>
                                @else
                                    <span class="badge bg-secondary-light text-secondary">Belum Diisi</span>
                                @endif
                            </td>
                            <td class="text-center text-success fw-semibold">
                                {{ $p->penilaianPkl ? number_format($p->penilaianPkl->rata_nilai_guru, 2) : '-' }}
                            </td>
                            <td class="text-center text-warning fw-semibold">
                                {{ $p->penilaianPkl ? number_format($p->penilaianPkl->rata_nilai_industri, 2) : '-' }}
                            </td>
                            <td class="text-center text-primary fw-bold" style="font-size: 14px;">
                                {{ $p->penilaianPkl ? number_format($p->penilaianPkl->nilai_akhir, 2) : '-' }}
                            </td>

                            <td class="text-center pe-4">
                                @if(auth()->user()->role === 'guru' || auth()->user()->role === 'admin')
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action" data-bs-toggle="modal" data-bs-target="#gradeModal_{{ $p->id }}" title="{{ $p->penilaianPkl ? 'Edit / Sahkan Nilai' : 'Input Nilai' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 00-2 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                            </svg>
                                        </button>
                                        @if($p->penilaianPkl && $p->penilaianPkl->nilai_akhir > 0)
                                            <a href="{{ route('laporan.nilai_pdf', $p->id) }}" class="btn btn-sm btn-outline-danger btn-action" title="Unduh Rapor PDF" aria-label="Unduh Rapor PDF {{ $p->murid?->nama }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </a>
                                            @if(auth()->user()->role === 'admin')
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-action" title="Hapus Nilai" aria-label="Hapus Nilai {{ $p->murid?->nama }}"
                                                    onclick="confirmDeleteNilai({{ $p->penilaianPkl->id }}, '{{ addslashes($p->murid?->nama ?? 'Siswa') }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        @endif
                                    </div>

                                    <!-- Grade Input Modal -->
                                    <div class="modal fade text-start" id="gradeModal_{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                                <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                                    <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Input & Pengesahan Nilai PKL</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('penilaian.store') }}" method="POST" id="gradeForm_{{ $p->id }}">
                                                    @csrf
                                                    <input type="hidden" name="penempatan_pkl_id" value="{{ $p->id }}">

                                                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                                        <div class="d-flex flex-wrap justify-content-between align-items-center p-3 rounded mb-3" style="background: rgba(14, 165, 233, 0.06); border: 1px solid rgba(14, 165, 233, 0.2);">
                                                            <div>
                                                                <h6 class="fw-bold m-0 text-dark">{{ $p->murid?->nama }}</h6>
                                                                <small class="text-muted">NIS: {{ $p->murid?->nis }} &bull; Mitra: {{ $p->dudi?->nama }}</small>
                                                            </div>
                                                            <div class="d-flex gap-3 align-items-center mt-2 mt-md-0">
                                                                <div class="text-center">
                                                                    <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase; font-weight: 600;">Skor Akhir Estimasi</span>
                                                                    <span class="fw-bold font-heading text-primary estimated-final-score-{{ $p->id }}" style="font-size: 16px;">
                                                                        {{ $p->penilaianPkl ? number_format($p->penilaianPkl->nilai_akhir, 2) : '0.00' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Evidence preview from student -->
                                                        @if($p->penilaianPkl && $p->penilaianPkl->bukti_nilai_industri)
                                                            @php
                                                                $isPdfFile = Str::endsWith(strtolower($p->penilaianPkl->bukti_nilai_industri), '.pdf');
                                                            @endphp
                                                            <div class="alert alert-info border-0 p-3 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3" style="background-color: rgba(79, 70, 229, 0.08); border-left: 4px solid var(--accent-primary) !important;">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    @if($isPdfFile)
                                                                        <span class="badge bg-danger-light text-danger p-2 font-heading">PDF</span>
                                                                    @else
                                                                        <img src="{{ asset('storage/penilaian/' . $p->penilaianPkl->bukti_nilai_industri) }}" class="rounded border" width="44" height="44" style="object-fit: cover;">
                                                                    @endif
                                                                    <div>
                                                                        <strong class="text-dark small d-block">Lampiran Bukti Lembar Nilai Fisik DUDI (Diunggah Siswa):</strong>
                                                                        <span class="text-muted small">Silakan periksa lembar fisik asli untuk memvalidasi angka nilai DUDI.</span>
                                                                    </div>
                                                                </div>
                                                                <a href="{{ asset('storage/penilaian/' . $p->penilaianPkl->bukti_nilai_industri) }}" target="_blank" class="btn btn-sm btn-primary font-heading py-1 px-3">
                                                                    Buka Bukti Fisik
                                                                </a>
                                                            </div>
                                                        @else
                                                            <div class="alert alert-warning border-0 p-2 mb-3 small" style="background-color: rgba(245, 158, 11, 0.1); color: #b45309;">
                                                                ℹ️ <strong>Perhatian:</strong> Siswa belum mengunggah foto lembar bukti fisik nilai dari DUDI. Anda tetap dapat memasukkan nilai sekolah dan melengkapi nilai industri secara manual jika diperlukan.
                                                            </div>
                                                        @endif
                                                        
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-sm align-middle" style="font-size: 12px; color: var(--text-primary);">
                                                                <thead class="table-light">
                                                                    <tr class="font-heading small">
                                                                        <th style="width: 8%; text-align: center;">No.</th>
                                                                        <th style="width: 50%;">Tujuan Pembelajaran / Indikator</th>
                                                                        <th style="width: 17%; text-align: center;">Nilai</th>
                                                                        <th style="width: 25%;">Keterangan TP</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($tps as $tp)
                                                                        @php
                                                                            $existingTpDesc = $p->penilaianPkl && isset($p->penilaianPkl->keterangan_tp_json[$tp->id]) ? $p->penilaianPkl->keterangan_tp_json[$tp->id] : '';
                                                                            $rowspan = 1 + count($tp->indikators);
                                                                        @endphp
                                                                        <tr class="table-secondary fw-bold" style="background-color: rgba(15, 23, 42, 0.05);">
                                                                            <td style="text-align: center;">{{ $tp->nomor }}</td>
                                                                            <td>{{ $tp->nama }}</td>
                                                                            <td></td>
                                                                            <td rowspan="{{ $rowspan }}" style="vertical-align: top; background-color: var(--bg-card);">
                                                                                <textarea name="keterangan_tp[{{ $tp->id }}]" class="form-control form-control-sm h-100" style="min-height: 120px; resize: vertical;" placeholder="Tulis keterangan capaian untuk Tujuan Pembelajaran {{ $tp->nomor }} di sini..." required>{{ $existingTpDesc }}</textarea>
                                                                            </td>
                                                                        </tr>
                                                                        
                                                                        @forelse($tp->indikators as $ind)

                                                                            @php
                                                                                $isGuru = $ind->tipe === 'guru';
                                                                                $fieldName = $isGuru ? "nilai_guru[{$ind->id}]" : "koreksi_nilai_industri[{$ind->id}]";
                                                                                
                                                                                // Get existing value
                                                                                $existingVal = '';
                                                                                if ($p->penilaianPkl) {
                                                                                    if ($isGuru && isset($p->penilaianPkl->nilai_guru_json[$ind->id])) {
                                                                                        $item = $p->penilaianPkl->nilai_guru_json[$ind->id];
                                                                                        $existingVal = is_array($item) ? ($item['nilai'] ?? '') : $item;
                                                                                    } elseif (!$isGuru && isset($p->penilaianPkl->nilai_industri_json[$ind->id])) {
                                                                                        $item = $p->penilaianPkl->nilai_industri_json[$ind->id];
                                                                                        $existingVal = is_array($item) ? ($item['nilai'] ?? '') : $item;
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
                                                                                    <div class="mt-1" style="font-size: 10px;">
                                                                                        @if($isGuru)
                                                                                            <span class="badge bg-success-light text-success">Nilai Sekolah (Diisi Guru)</span>
                                                                                        @else
                                                                                            <span class="badge bg-warning-light text-warning">Nilai DUDI (Diinput Siswa / Diverifikasi)</span>
                                                                                        @endif
                                                                                    </div>
                                                                                </td>
                                                                                <td>
                                                                                    <input type="number" step="0.01" min="0" max="100" name="{{ $fieldName }}" class="form-control form-control-sm text-center grade-input-{{ $p->id }}" value="{{ $existingVal }}" oninput="calculateLiveScore({{ $p->id }})" {{ $isGuru ? 'required' : '' }} placeholder="0-100">
                                                                                </td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr>
                                                                                <td colspan="3" class="text-center text-muted small py-2">Belum ada indikator untuk tujuan pembelajaran ini.</td>
                                                                            </tr>
                                                                        @endforelse
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        
                                                        <div class="mb-2 mt-3">
                                                            <label for="catatan_{{ $p->id }}" class="form-label small fw-semibold">Catatan Selama PKL (Keseluruhan)</label>
                                                            <textarea name="catatan" id="catatan_{{ $p->id }}" class="form-control form-control-sm" rows="3" placeholder="Tulis catatan perkembangan dan evaluasi PKL siswa..." required>{{ $p->penilaianPkl ? $p->penilaianPkl->catatan : '' }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-sm btn-primary" data-loading-text="Menyimpan...">Simpan & Sahkan Penilaian</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="empty-state py-4">
                                    <div class="empty-state-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                    </div>
                                    <h6 class="empty-state-title">Belum Ada Data Penilaian</h6>
                                    <p class="empty-state-text">Data penilaian akan tersedia setelah murid ditempatkan pada mitra industri.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($placements->hasPages())
        <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
            {{ $placements->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Import Nilai dari Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('penilaian.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Silakan unduh template Excel terlebih dahulu menggunakan tombol <strong>Download Template Excel</strong>, isi nilai siswa pada kolom yang tersedia, lalu unggah kembali filenya di bawah ini.
                    </p>
                    <div class="mb-3">
                        <label for="file_excel" class="form-label small fw-semibold">Pilih File Excel (.xlsx, .xls)</label>
                        <input type="file" name="file_excel" id="file_excel" class="form-control form-control-sm" accept=".xlsx, .xls" required>
                    </div>
                </div>
                <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Mulai Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDeleteNilai(id, nama) {
    window.confirmAction({
        title: 'Hapus Nilai Siswa?',
        text: 'Data nilai untuk ' + nama + ' akan dihapus dan perlu diinput ulang.',
        icon: 'warning',
        confirmButtonColor: '#e11d48',
        confirmButtonText: 'Ya, Hapus'
    }).then(r => {
        if(r.isConfirmed) {
            const form = document.getElementById('deleteNilaiForm');
            form.action = '{{ url("/penilaian") }}/' + id;
            form.submit();
        }
    });
}

function calculateLiveScore(placementId) {
    const inputs = document.querySelectorAll('.grade-input-' + placementId);
    let sum = 0;
    let count = 0;
    inputs.forEach(inp => {
        const val = parseFloat(inp.value);
        if (!isNaN(val) && val >= 0) {
            sum += val;
            count++;
        }
    });
    const avg = count > 0 ? (sum / count).toFixed(2) : '0.00';
    const scoreDisplay = document.querySelector('.estimated-final-score-' + placementId);
    if (scoreDisplay) {
        scoreDisplay.textContent = avg;
    }
}
</script>
<form id="deleteNilaiForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection
