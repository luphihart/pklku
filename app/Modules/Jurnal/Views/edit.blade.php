@extends('layouts.admin')

@section('title', 'Edit Jurnal - PKLku')
@section('page_title', 'Edit Jurnal Kegiatan')

@section('content')
<div class="container-fluid p-0">
    <div class="col-md-8 mx-auto">
        <div class="card-premium">
            <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Form Edit Jurnal Harian</h5>

            <form action="{{ route('jurnal.update', $journal->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="tanggal" class="form-label small fw-semibold">Tanggal Kegiatan</label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', $journal->tanggal ? \Carbon\Carbon::parse($journal->tanggal)->format('Y-m-d') : '') }}" required>
                    @error('tanggal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi_aktivitas" class="form-label small fw-semibold mb-1">Rincian Aktivitas Harian</label>

                    <!-- Panduan Penulisan 5W + 1H (Teks Deskripsi Biasa) -->
                    <div class="p-2.5 px-3 mb-2 rounded border" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important; font-size: 12px; line-height: 1.55;">
                        <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                            <span>💡</span>
                            <span>Panduan Penulisan (5W + 1H):</span>
                        </div>
                        <div class="text-secondary">
                            Tuliskan secara jelas <strong>apa</strong> yang dikerjakan (What), <strong>tujuan</strong> kegiatan (Why), <strong>lokasi / alat</strong> yang digunakan (Where), <strong>waktu</strong> pelaksanaan (When), <strong>pembimbing / rekan</strong> yang terlibat (Who), serta <strong>langkah & hasil</strong> pekerjaan (How).
                        </div>
                    </div>

                    <textarea name="deskripsi_aktivitas" id="deskripsi_aktivitas" class="form-control @error('deskripsi_aktivitas') is-invalid @enderror" rows="5" placeholder="Tuliskan aktivitas, tugas yang dikerjakan, atau materi yg dipelajari hari ini sesuai panduan 5W + 1H..." required>{{ old('deskripsi_aktivitas', $journal->deskripsi_aktivitas) }}</textarea>
                    @error('deskripsi_aktivitas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if($journal->foto_kegiatan)
                    @php
                        $isPdf = Str::endsWith(strtolower($journal->foto_kegiatan), '.pdf');
                    @endphp
                    <div class="mb-3">
                        <label class="form-label small fw-semibold d-block">Bukti Kegiatan Saat Ini</label>
                        @if($isPdf)
                            <a href="{{ asset('storage/jurnal/' . $journal->foto_kegiatan) }}" target="_blank" class="btn btn-sm btn-outline-info font-heading">Buka PDF Saat Ini</a>
                        @else
                            <img src="{{ asset('storage/jurnal/' . $journal->foto_kegiatan) }}" class="rounded border mb-2" width="120" height="90" style="object-fit: cover;">
                        @endif
                    </div>
                @endif

                <div class="mb-4">
                    <label for="foto" class="form-label small fw-semibold">Ganti Bukti Kegiatan (Foto/PDF) {{ !$journal->foto_kegiatan ? '(Wajib)' : '(Opsional)' }}</label>
                    <input type="file" name="foto" id="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*, application/pdf" {{ !$journal->foto_kegiatan ? 'required' : '' }}>
                    <small class="text-muted" style="font-size: 10px;">Format: JPG, JPEG, PNG, atau PDF (Maks. 2MB)</small>
                    @error('foto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('jurnal.index') }}" class="btn btn-secondary px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
