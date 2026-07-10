@extends('layouts.admin')

@section('title', 'Revisi Pengajuan Izin & Sakit - PKLku')
@section('page_title', 'Revisi Pengajuan Izin & Sakit')

@section('content')
<div class="container-fluid p-0">
    <div class="col-md-8 mx-auto">
        <div class="card-premium">
            <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Form Revisi Pengajuan</h5>

            @if($permission->catatan_guru)
                <div class="alert alert-warning mb-4" role="alert">
                    <h6 class="fw-bold m-0 mb-1 font-heading">Catatan Penolakan Guru:</h6>
                    <p class="small m-0">{{ $permission->catatan_guru }}</p>
                </div>
            @endif

            <form action="{{ route('izin.update', $permission->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="tipe" class="form-label small fw-semibold">Kategori Pengajuan</label>
                    <select name="tipe" id="tipe" class="form-select" required>
                        <option value="izin" {{ old('tipe', $permission->tipe) === 'izin' ? 'selected' : '' }}>Izin Resmi</option>
                        <option value="sakit" {{ old('tipe', $permission->tipe) === 'sakit' ? 'selected' : '' }}>Sakit (Butuh Surat Dokter)</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_mulai" class="form-label small fw-semibold">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', $permission->tanggal_mulai) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_selesai" class="form-label small fw-semibold">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', $permission->tanggal_selesai) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="alasan" class="form-label small fw-semibold">Alasan / Penjelasan</label>
                    <textarea name="alasan" id="alasan" class="form-control @error('alasan') is-invalid @enderror" rows="4" placeholder="Tulis alasan berhalangan hadir secara lengkap..." required>{{ old('alasan', $permission->alasan) }}</textarea>
                    @error('alasan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if($permission->surat_pendukung)
                    @php
                        $isPdf = Str::endsWith(strtolower($permission->surat_pendukung), '.pdf');
                    @endphp
                    <div class="mb-3">
                        <label class="form-label small fw-semibold d-block">Surat Pendukung Saat Ini</label>
                        @if($isPdf)
                            <a href="{{ asset('storage/izin/' . $permission->surat_pendukung) }}" target="_blank" class="btn btn-sm btn-outline-info font-heading">Buka PDF Saat Ini</a>
                        @else
                            <img src="{{ asset('storage/izin/' . $permission->surat_pendukung) }}" class="rounded border mb-2" width="120" height="90" style="object-fit: cover;">
                        @endif
                    </div>
                @endif

                <div class="mb-4">
                    <label for="surat" class="form-label small fw-semibold">Ganti Surat Pendukung (Foto/PDF) {{ !$permission->surat_pendukung ? '(Wajib)' : '(Opsional)' }}</label>
                    <input type="file" name="surat" id="surat" class="form-control @error('surat') is-invalid @enderror" accept="image/*,application/pdf" {{ !$permission->surat_pendukung ? 'required' : '' }}>
                    <small class="text-muted" style="font-size: 10px;">Format: JPG, JPEG, PNG, atau PDF (Maks. 2MB)</small>
                    @error('surat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('izin.index') }}" class="btn btn-secondary px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">Kirim Revisi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
