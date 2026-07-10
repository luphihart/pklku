@extends('layouts.admin')

@section('title', 'Pusat Laporan - PKLku')
@section('page_title', 'Pusat Unduhan Laporan')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <!-- Excel Export Column -->
        <div class="col-md-6 mx-auto mb-4">
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
    </div>
</div>
@endsection
