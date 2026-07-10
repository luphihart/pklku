@extends('layouts.admin')

@section('title', 'Pengumuman - PKLku')
@section('page_title', 'Pengumuman Sekolah')

@section('content')
<div class="container-fluid p-0">
    <div class="col-md-9 mx-auto">
        <h5 class="fw-bold font-heading mb-4 text-dark dark-text-light">Papan Informasi & Pengumuman</h5>

        @forelse($announcements as $a)
            <div class="card-premium mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                    <h6 class="fw-bold m-0 font-heading text-primary" style="font-size: 15px;">{{ $a->judul }}</h6>
                    <small class="text-muted" style="font-size: 11px;">
                        {{ $a->created_at->translatedFormat('d F Y H:i') }} WIB
                    </small>
                </div>
                <p class="text-secondary m-0" style="font-size: 13px; line-height: 1.6;">
                    {!! nl2br(e($a->isi)) !!}
                </p>
            </div>
        @empty
            <div class="card-premium text-center py-5">
                <span class="text-muted d-block">Tidak ada pengumuman baru saat ini.</span>
                <small class="text-muted">Semua informasi atau pengumuman dari sekolah akan diposting di sini.</small>
            </div>
        @endif
    </div>
</div>
@endsection
