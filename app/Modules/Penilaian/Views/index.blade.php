@extends('layouts.admin')

@section('title', 'Penilaian Siswa - PKLku')
@section('page_title', 'Evaluasi Nilai Kelulusan Murid')

@section('content')
<div class="container-fluid p-0">
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
                        <th class="text-center">Rata Guru</th>
                        <th class="text-center">Rata Industri</th>
                        <th class="text-center">Nilai Akhir</th>
                        <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($placements as $p)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $p->murid->nama }}</div>
                                <small class="text-muted">{{ $p->murid->nis }}</small>
                            </td>
                            <td>{{ $p->murid->kelas->nama ?? '-' }}</td>
                            <td>{{ $p->dudi->nama ?? '-' }}</td>
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
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action" data-bs-toggle="modal" data-bs-target="#gradeModal_{{ $p->id }}" title="{{ $p->penilaianPkl ? 'Edit Nilai' : 'Input Nilai' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                            </svg>
                                        </button>
                                        @if($p->penilaianPkl)
                                            <a href="{{ route('laporan.nilai_pdf', $p->id) }}" class="btn btn-sm btn-outline-danger btn-action" title="Unduh Rapor PDF">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </a>
                                            @if(auth()->user()->role === 'admin')
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-action" title="Hapus Nilai"
                                                    onclick="confirmDeleteNilai({{ $p->penilaianPkl->id }}, '{{ $p->murid->nama }}')">
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
                                                    <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Input Nilai Kelulusan PKL</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('penilaian.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="penempatan_pkl_id" value="{{ $p->id }}">

                                                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                                        <p class="small text-muted mb-4">Input skor (skala 0 - 100) dan keterangan untuk masing-masing kriteria penilaian untuk siswa <strong>{{ $p->murid->nama }}</strong>.</p>
                                                        
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-sm align-middle" style="font-size: 12px; color: var(--text-primary);">
                                                                <thead class="table-light">
                                                                    <tr class="font-heading small">
                                                                        <th style="width: 8%; text-align: center;">No.</th>
                                                                        <th style="width: 50%;">Tujuan Pembelajaran / Indikator</th>
                                                                        <th style="width: 17%; text-align: center;">Nilai</th>
                                                                        <th style="width: 25%;">Keterangan</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($tps as $tp)
                                                                        <!-- TP Header Row -->
                                                                        @php
                                                                            $existingTpDesc = $p->penilaianPkl && isset($p->penilaianPkl->keterangan_tp_json[$tp->id]) ? $p->penilaianPkl->keterangan_tp_json[$tp->id] : '';
                                                                            $hasDivider = $tp->indikators->contains(fn($ind) => $ind->nomor_urut === '3.7');
                                                                            $rowspan = 1 + count($tp->indikators) + ($hasDivider ? 1 : 0);
                                                                        @endphp
                                                                        <tr class="table-secondary fw-bold" style="background-color: rgba(15, 23, 42, 0.05);">
                                                                            <td style="text-align: center;">{{ $tp->nomor }}</td>
                                                                            <td>{{ $tp->nama }}</td>
                                                                            <td></td>
                                                                            <td rowspan="{{ $rowspan }}" style="vertical-align: top; background-color: var(--bg-card);">
                                                                                <textarea name="keterangan_tp[{{ $tp->id }}]" class="form-control form-control-sm h-100" style="min-height: 120px; resize: vertical;" placeholder="Tulis keterangan untuk Tujuan Pembelajaran {{ $tp->nomor }} di sini..." required>{{ $existingTpDesc }}</textarea>
                                                                            </td>
                                                                        </tr>
                                                                        
                                                                        <!-- TP Indicators -->
                                                                        @forelse($tp->indikators as $ind)
                                                                            @if($ind->nomor_urut == '3.7')
                                                                                <tr class="table-info fw-bold text-center small text-secondary">
                                                                                    <td colspan="3" style="font-size: 11px;">Point 3.7 kebawah, diisi oleh sekolah (Guru Pembimbing)</td>
                                                                                </tr>
                                                                            @endif

                                                                            @php
                                                                                $isGuru = $ind->tipe === 'guru';
                                                                                $fieldName = $isGuru ? "nilai_guru[{$ind->id}]" : "nilai_industri[{$ind->id}]";
                                                                                
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
                                                                                            <span class="badge bg-success-soft text-success">Guru Pembimbing (Sekolah)</span>
                                                                                        @else
                                                                                            <span class="badge bg-warning-soft text-warning">Pembimbing Industri (DUDI)</span>
                                                                                        @endif
                                                                                    </div>
                                                                                </td>
                                                                                <td>
                                                                                    <input type="number" name="{{ $fieldName }}" class="form-control form-control-sm text-center" min="0" max="100" value="{{ $existingVal }}" required>
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
                                                            <textarea name="catatan" id="catatan_{{ $p->id }}" class="form-control form-control-sm" rows="3" placeholder="Wajib diisi..." required>{{ $p->penilaianPkl ? $p->penilaianPkl->catatan : '' }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-sm btn-primary">Simpan Penilaian</button>
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
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada murid bimbingan aktif saat ini.</td>
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

@push('scripts')
<script>
function confirmDeleteNilai(id, nama) {
    if (confirm('Hapus data nilai yang sudah diinput untuk siswa "' + nama + '"?\n\nTindakan ini tidak dapat dibatalkan.')) {
        const form = document.getElementById('deleteNilaiForm');
        form.action = '{{ url("/penilaian") }}/' + id;
        form.submit();
    }
}
</script>
<form id="deleteNilaiForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endpush
