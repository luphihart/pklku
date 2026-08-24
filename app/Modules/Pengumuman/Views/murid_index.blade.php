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
            <div class="card-premium empty-state text-center py-5">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h6 class="empty-state-title">Belum Ada Pengumuman</h6>
                <p class="empty-state-text">Semua informasi atau pengumuman dari sekolah akan diposting di sini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
