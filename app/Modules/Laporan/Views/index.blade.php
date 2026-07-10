@extends('layouts.admin')

@section('title', 'Pusat Laporan - PKLku')
@section('page_title', 'Pusat Unduhan Laporan')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <!-- Excel Export Column -->
        <div class="col-md-5 mb-4">
            <div class="card-premium" x-data="{ type: 'harian' }">
                <h5 class="fw-bold font-heading mb-3 text-dark">Ekspor Rekap Presensi</h5>
                <p class="small text-secondary mb-3">Unduh data kehadiran siswa dalam format Excel Spreadsheet.</p>
                
                <form action="{{ route('laporan.presensi_excel') }}" method="GET">
                    <div class="mb-3">
                        <label for="filter_type" class="form-label small fw-semibold">Tipe Rekapitulasi</label>
                        <select name="filter_type" id="filter_type" class="form-select form-select-sm" x-model="type">
                            <option value="harian">Harian</option>
                            <option value="mingguan">Mingguan</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="kustom">Jangkauan Kustom</option>
                        </select>
                    </div>

                    <!-- Harian -->
                    <div class="mb-3" x-show="type === 'harian'">
                        <label for="tanggal" class="form-label small fw-semibold">Pilih Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>

                    <!-- Mingguan -->
                    <div class="mb-3" x-show="type === 'mingguan'" style="display: none;">
                        <label for="minggu" class="form-label small fw-semibold">Pilih Minggu</label>
                        <input type="week" name="minggu" id="minggu" class="form-control form-control-sm" value="{{ date('Y-\WW') }}">
                    </div>

                    <!-- Bulanan -->
                    <div class="row g-2 mb-3" x-show="type === 'bulanan'" style="display: none;">
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
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label for="tanggal_selesai" class="form-label small fw-semibold">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control form-control-sm">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary w-100 mt-2">Ekspor ke Excel</button>
                </form>
            </div>
        </div>

        <!-- PDF Grades Sheet Column -->
        <div class="col-md-7 mb-4">
            <div class="card-premium p-0 overflow-hidden">
                <div class="p-3 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                    <h6 class="fw-bold m-0 text-dark dark-text-light">Unduh Lembar Nilai PDF Kop Sekolah</h6>
                </div>

                <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px;">
                        <thead>
                            <tr class="text-muted">
                                <th class="ps-4">Siswa (NIS)</th>
                                <th>DUDI PKL</th>
                                <th class="text-center">Nilai Akhir</th>
                                <th class="text-center pe-4" style="width: 100px;">Unduh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($placements as $p)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold">{{ $p->murid->nama }}</div>
                                        <small class="text-muted">{{ $p->murid->kelas->nama }}</small>
                                    </td>
                                    <td>{{ $p->dudi->nama }}</td>
                                    <td class="text-center fw-bold text-primary">{{ number_format($p->penilaianPkl->nilai_akhir, 2) }}</td>
                                    <td class="text-center pe-4">
                                        <a href="{{ route('laporan.nilai_pdf', $p->id) }}" class="btn btn-sm btn-outline-danger p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada murid yang selesai dinilai.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
