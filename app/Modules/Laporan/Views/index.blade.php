@extends('layouts.admin')

@section('title', 'Pusat Laporan - PKLku')
@section('page_title', 'Pusat Unduhan Laporan')

@section('content')
<div class="container-fluid p-0">

    <!-- Header Banner -->
    <div class="card-premium mb-4 p-4" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.08) 0%, rgba(99, 102, 241, 0.05) 100%); border-left: 4px solid var(--primary-color);">
        <div class="d-flex align-items-center gap-3">
            <div class="p-3 rounded-3 bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h5 class="fw-bold font-heading m-0 text-dark">Pusat Laporan & Rekapitulasi PKL</h5>
                <p class="text-secondary small m-0 mt-1">Unduh berkas rekapitulasi kehadiran, buku jurnal kegiatan, dan lembar rapor penilaian siswa dalam format <strong>Excel (.xlsx)</strong> maupun dokumen resmi <strong>PDF (.pdf)</strong>.</p>
            </div>
        </div>
    </div>

    <!-- Export Forms Row -->
    <div class="row g-4 mb-4">

        <!-- 1. Ekspor Rekap Presensi -->
        <div class="col-lg-6">
            <div class="card-premium h-100" x-data="{ 
                type: 'bulanan',
                submitExport(format) {
                    const form = this.$refs.presensiForm;
                    if (format === 'pdf') {
                        form.action = '{{ route('laporan.presensi_pdf') }}';
                    } else {
                        form.action = '{{ route('laporan.presensi_excel') }}';
                    }
                    form.submit();
                }
            }">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded bg-primary-light text-primary d-inline-flex">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <h6 class="fw-bold font-heading m-0 text-dark" style="font-size: 15px;">Rekapitulasi Presensi</h6>
                    </div>
                    <span class="badge bg-light text-muted border small" style="font-size: 11px;">PDF & Excel</span>
                </div>
                <p class="small text-secondary mb-3">Cetak rekap log kehadiran seluruh siswa aktif sesuai rentang waktu yang dipilih.</p>
                
                <form x-ref="presensiForm" method="GET">
                    <div class="mb-3">
                        <label for="presensi_filter_type" class="form-label small fw-semibold">Tipe Rekapitulasi</label>
                        <select name="filter_type" id="presensi_filter_type" class="form-select form-select-sm" x-model="type">
                            <option value="harian">Harian (Per Tanggal)</option>
                            <option value="mingguan">Mingguan</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="kustom">Jangkauan Kustom (Rentang Tanggal)</option>
                        </select>
                    </div>

                    <!-- Harian -->
                    <div class="mb-3" x-show="type === 'harian'" style="display: none;">
                        <label for="tanggal" class="form-label small fw-semibold">Pilih Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>

                    <!-- Mingguan -->
                    <div class="mb-3" x-show="type === 'mingguan'" style="display: none;">
                        <label for="minggu" class="form-label small fw-semibold">Pilih Minggu</label>
                        <input type="week" name="minggu" id="minggu" class="form-control form-control-sm" value="{{ date('Y-\WW') }}">
                    </div>

                    <!-- Bulanan -->
                    <div class="row g-2 mb-3" x-show="type === 'bulanan'">
                        <div class="col-7">
                            <label for="bulan" class="form-label small fw-semibold">Pilih Bulan</label>
                            <select name="bulan" id="bulan" class="form-select form-select-sm">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ sprintf('%02d', $m) }}" {{ date('m') == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-5">
                            <label for="tahun" class="form-label small fw-semibold">Pilih Tahun</label>
                            <select name="tahun" id="tahun" class="form-select form-select-sm">
                                @for($y = date('Y') - 3; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Kustom -->
                    <div class="row g-2 mb-3" x-show="type === 'kustom'" style="display: none;">
                        <div class="col-6">
                            <label for="tanggal_mulai" class="form-label small fw-semibold">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control form-control-sm" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-6">
                            <label for="tanggal_selesai" class="form-label small fw-semibold">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row g-2 mt-4 pt-2 border-top">
                        <div class="col-6">
                            <button type="button" @click="submitExport('pdf')" class="btn btn-sm btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span>Unduh PDF</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" @click="submitExport('excel')" class="btn btn-sm btn-success w-100 d-flex align-items-center justify-content-center gap-1 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Unduh Excel</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Ekspor Rekap Jurnal Kegiatan -->
        <div class="col-lg-6">
            <div class="card-premium h-100" x-data="{ 
                mode: 'periode', 
                filterType: 'bulanan'
            }">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded bg-info-light text-info d-inline-flex">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </span>
                        <h6 class="fw-bold font-heading m-0 text-dark" style="font-size: 15px;">Rekapitulasi Jurnal Kegiatan</h6>
                    </div>
                    <span class="badge bg-light text-muted border small" style="font-size: 11px;">Dokumen PDF</span>
                </div>
                <p class="small text-secondary mb-3">Unduh buku rekap aktivitas harian PKL siswa yang telah diverifikasi.</p>

                <form action="{{ route('laporan.jurnal_pdf') }}" method="GET">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Lingkup Unduhan</label>
                        <div class="d-flex gap-3 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope_mode" id="scope_periode" value="periode" x-model="mode">
                                <label class="form-check-label small" for="scope_periode">Semua Siswa per Periode</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope_mode" id="scope_siswa" value="siswa" x-model="mode">
                                <label class="form-check-label small" for="scope_siswa">Per Siswa Spesifik</label>
                            </div>
                        </div>
                    </div>

                    <!-- Mode Periode -->
                    <div x-show="mode === 'periode'">
                        <div class="mb-3">
                            <label for="jurnal_filter_type" class="form-label small fw-semibold">Tipe Periode</label>
                            <select name="filter_type" id="jurnal_filter_type" class="form-select form-select-sm" x-model="filterType">
                                <option value="bulanan">Bulanan</option>
                                <option value="harian">Harian (Per Tanggal)</option>
                                <option value="kustom">Jangkauan Kustom</option>
                            </select>
                        </div>

                        <!-- Harian -->
                        <div class="mb-3" x-show="filterType === 'harian'" style="display: none;">
                            <label for="jurnal_tanggal" class="form-label small fw-semibold">Pilih Tanggal</label>
                            <input type="date" name="tanggal" id="jurnal_tanggal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Bulanan -->
                        <div class="row g-2 mb-3" x-show="filterType === 'bulanan'">
                            <div class="col-7">
                                <label for="jurnal_bulan" class="form-label small fw-semibold">Pilih Bulan</label>
                                <select name="bulan" id="jurnal_bulan" class="form-select form-select-sm">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ sprintf('%02d', $m) }}" {{ date('m') == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-5">
                                <label for="jurnal_tahun" class="form-label small fw-semibold">Pilih Tahun</label>
                                <select name="tahun" id="jurnal_tahun" class="form-select form-select-sm">
                                    @for($y = date('Y') - 3; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Kustom -->
                        <div class="row g-2 mb-3" x-show="filterType === 'kustom'" style="display: none;">
                            <div class="col-6">
                                <label for="jurnal_tanggal_mulai" class="form-label small fw-semibold">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" id="jurnal_tanggal_mulai" class="form-control form-control-sm" value="{{ date('Y-m-01') }}">
                            </div>
                            <div class="col-6">
                                <label for="jurnal_tanggal_selesai" class="form-label small fw-semibold">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" id="jurnal_tanggal_selesai" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Mode Per Siswa -->
                    <div x-show="mode === 'siswa'" style="display: none;">
                        <div class="mb-3">
                            <label for="placement_id" class="form-label small fw-semibold">Pilih Siswa PKL</label>
                            <select name="placement_id" id="placement_id" class="form-select form-select-sm">
                                <option value="">-- Pilih Siswa Penempatan --</option>
                                @foreach($allPlacements as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->murid?->nama ?? 'Siswa' }} ({{ $item->murid?->kelas?->nama ?? '-' }}) — {{ $item->dudi?->nama ?? 'DUDI' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 pt-2 border-top">
                        <button type="submit" class="btn btn-sm btn-primary w-100 d-flex align-items-center justify-content-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span>Unduh PDF Rekap Jurnal</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Student Document Quick Download Table -->
    <div class="card-premium">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
            <div>
                <h6 class="fw-bold font-heading m-0 text-dark" style="font-size: 16px;">Daftar Dokumen Siswa PKL</h6>
                <span class="text-secondary small">Unduh bundel dokumen resmi per individu siswa (Presensi, Buku Jurnal, & Rapor Nilai).</span>
            </div>
            <form action="{{ route('laporan.index') }}" method="GET" class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari siswa / DUDI..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
                @if(request('search'))
                    <a href="{{ route('laporan.index') }}" class="btn btn-sm btn-outline-secondary font-heading d-flex align-items-center justify-content-center px-2.5" title="Reset Pencarian">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 4%;" class="text-center">No</th>
                        <th style="width: 25%;">Nama Siswa & NIS</th>
                        <th style="width: 14%;">Kelas / Jurusan</th>
                        <th style="width: 20%;">Mitra DUDI</th>
                        <th style="width: 15%;">Guru Pembimbing</th>
                        <th style="width: 22%;" class="text-center">Aksi Cepat Unduh Dokumen (PDF)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($placements as $index => $p)
                        <tr>
                            <td class="text-center text-muted">{{ $placements->firstItem() + $index }}</td>
                            <td>
                                <strong class="text-dark font-heading d-block">{{ $p->murid?->nama ?? 'Siswa Terhapus' }}</strong>
                                <small class="text-muted">NIS: {{ $p->murid?->nis ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="d-block">{{ $p->murid?->kelas?->nama ?? '-' }}</span>
                                <small class="text-muted">{{ $p->murid?->kelas?->jurusan?->singkatan ?? '' }}</small>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $p->dudi?->nama ?? '-' }}</span>
                            </td>
                            <td>
                                <small class="text-secondary">{{ $p->guru?->nama ?? 'Belum ditentukan' }}</small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <!-- Unduh PDF Presensi -->
                                    <a href="{{ route('laporan.murid_presensi_pdf', ['placement_id' => $p->id]) }}" class="btn btn-outline-primary" title="Unduh Rekap Presensi Siswa PDF" data-bs-toggle="tooltip">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>Presensi
                                    </a>

                                    <!-- Unduh PDF Jurnal -->
                                    <a href="{{ route('laporan.murid_jurnal_pdf', ['placement_id' => $p->id]) }}" class="btn btn-outline-info" title="Unduh Buku Jurnal Kegiatan Siswa PDF" data-bs-toggle="tooltip">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>Jurnal
                                    </a>

                                    <!-- Unduh PDF Rapor Nilai -->
                                    @if($p->penilaianPkl)
                                        <a href="{{ route('laporan.nilai_pdf', $p->id) }}" class="btn btn-outline-success" title="Unduh Lembar Rapor Nilai Siswa PDF" data-bs-toggle="tooltip">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                            </svg>Rapor
                                        </a>
                                    @else
                                        <span class="btn btn-outline-secondary disabled" title="Belum Dinilai" style="opacity: 0.5;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>Rapor
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Tidak ada data penempatan siswa yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($placements->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <small class="text-muted">Menampilkan {{ $placements->firstItem() }} - {{ $placements->lastItem() }} dari {{ $placements->total() }} data</small>
                {{ $placements->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>
@endsection
