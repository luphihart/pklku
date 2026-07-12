@extends('layouts.admin')

@section('title', 'Dashboard - PKLku')
@section('page_title', 'Dashboard Utama')

@section('content')
<div class="container-fluid p-0">
    <!-- Birthday Greeting (If applicable) -->
    @if(auth()->user()->tanggal_lahir && auth()->user()->tanggal_lahir->isBirthday())
    <div class="card-premium mb-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #f43f5e 0%, #d946ef 100%); border: none;">
        <div class="position-absolute" style="right: -20px; top: -20px; opacity: 0.15; transform: rotate(15deg);">
            <svg xmlns="http://www.w3.org/2000/svg" width="150" height="150" fill="currentColor" viewBox="0 0 16 16">
                <path d="M3 2.5a2.5 2.5 0 0 1 5 0 2.5 2.5 0 0 1 5 0v.006c0 .07 0 .27-.038.494H15a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5V7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h2.038A2.968 2.968 0 0 1 3 2.506zm1.068.5H7v-.5a1.5 1.5 0 1 0-3 0c0 .085.002.274.045.43a.522.522 0 0 0 .023.07zM9 3h2.932a.56.56 0 0 0 .023-.07c.043-.156.045-.345.045-.43a1.5 1.5 0 0 0-3 0v.5zM1 4v2h14V4H1zm1.5 3v7.5a.5.5 0 0 0 .5.5h4V7h-4.5zm5.5 8h4a.5.5 0 0 0 .5-.5V7H7v8z"/>
            </svg>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="p-3 bg-white rounded-circle text-danger" style="box-shadow: 0 4px 15px rgba(244, 63, 94, 0.4); font-size: 24px; line-height: 1;">
                🎉
            </div>
            <div>
                <h4 class="fw-bold font-heading m-0">Selamat Ulang Tahun, {{ auth()->user()->name }}! 🎂</h4>
                <p class="m-0 mt-1" style="font-size: 13px; opacity: 0.9;">Semoga panjang umur, sehat selalu, dilancarkan segala urusannya, serta sukses dalam belajar dan berkarya!</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Welcome Header -->
    <div class="card-premium mb-4" style="border-left: 4px solid var(--accent-primary) !important;">
        <h4 class="fw-bold font-heading m-0 text-dark dark-text-light">Selamat Datang Kembali, {{ auth()->user()->name }}!</h4>
        <p class="text-muted m-0 mt-1" style="font-size: 14px;">
            @php
                $dayName = \Carbon\Carbon::now()->translatedFormat('l, d F Y');
                
                $muridQuotes = [
                    "Tetap semangat belajar dan jalani kegiatan PKL hari ini dengan penuh tanggung jawab serta kedisiplinan!",
                    "Setiap tantangan di tempat PKL adalah kesempatan emas untuk tumbuh menjadi profesional hebat. Tetap semangat!",
                    "Jaga selalu nama baik sekolah, tunjukkan integritas tinggi, dan serap ilmu sebanyak-banyaknya hari ini!",
                    "Kesuksesan masa depan dibangun dari kedisiplinan dan kerja keras hari ini. Selamat menjalani aktivitas PKL!",
                    "Fokus, tekun, dan jangan ragu untuk bertanya. Jadikan hari ini langkah maju menuju cita-citamu!",
                    "PKL adalah jembatan emas menuju dunia kerja nyata. Lakukan yang terbaik dan nikmati proses belajarmu hari ini!",
                    "Karakter unggul dibentuk dari kebiasaan baik setiap hari. Tetap disiplin, sopan, dan berikan performa terbaikmu!"
                ];
                
                $staffQuotes = [
                    "Semoga hari Anda menyenangkan dalam membimbing dan memfasilitasi masa depan generasi emas bangsa!",
                    "Dedikasi Anda adalah pelita bagi kesuksesan siswa. Selamat bertugas membimbing para calon pemimpin bangsa!",
                    "Terima kasih atas segala komitmen dan ketulusan dalam mengawal masa depan generasi penentu bangsa!",
                    "Semoga setiap langkah bimbingan Anda hari ini membawa keberkahan dan kemajuan bagi anak-anak didik kita!",
                    "Selamat beraktivitas! Semoga kelancaran dan kemudahan menyertai setiap tugas administratif dan bimbingan Anda hari ini.",
                    "Pekerjaan mulia Anda adalah fondasi kesuksesan masa depan mereka. Tetap semangat menginspirasi!",
                    "Mari kita terus bersinergi membangun ekosistem PKL yang berkualitas demi masa depan cerah siswa-siswi kita."
                ];
                
                $dayOfMonth = (int) \Carbon\Carbon::now()->day;
                if (auth()->user()->role === 'murid') {
                    $quote = $muridQuotes[$dayOfMonth % count($muridQuotes)];
                } else {
                    $quote = $staffQuotes[$dayOfMonth % count($staffQuotes)];
                }
            @endphp
            Hari ini, {{ $dayName }}. {{ $quote }}
        </p>
    </div>

    <!-- Statistics Grid (For Admin and Guru) -->
    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'guru')
        @if(auth()->user()->role === 'guru')
            <div class="row mb-4">
                <!-- Count 1: Murid Bimbingan -->
                <div class="col-6 col-md-4 mb-3">
                    <div class="card-premium d-flex align-items-center justify-content-between h-100">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold font-heading" style="font-size: 11px;">Murid Bimbingan</span>
                            <h3 class="fw-bold m-0 mt-1 text-dark dark-text-light">{{ $counts['murid'] ?? 0 }}</h3>
                        </div>
                        <div class="p-3 rounded bg-light d-none d-sm-block" style="color: var(--accent-primary); background-color: rgba(79, 70, 229, 0.1) !important;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Count 2: Mitra DUDI Plotted -->
                <div class="col-6 col-md-4 mb-3">
                    <div class="card-premium d-flex align-items-center justify-content-between h-100">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold font-heading" style="font-size: 11px;">Mitra DUDI</span>
                            <h3 class="fw-bold m-0 mt-1 text-dark dark-text-light">{{ $counts['dudi'] ?? 0 }}</h3>
                        </div>
                        <div class="p-3 rounded bg-light d-none d-sm-block" style="color: var(--warning); background-color: rgba(245, 158, 11, 0.1) !important;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Count 3: Penempatan Aktif -->
                <div class="col-12 col-md-4 mb-3">
                    <div class="card-premium d-flex align-items-center justify-content-between h-100">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold font-heading" style="font-size: 11px;">Penempatan Aktif</span>
                            <h3 class="fw-bold m-0 mt-1 text-dark dark-text-light">{{ $counts['penempatan_aktif'] ?? 0 }}</h3>
                        </div>
                        <div class="p-3 rounded bg-light d-none d-sm-block" style="color: var(--danger); background-color: rgba(225, 29, 72, 0.1) !important;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row mb-4">
                <!-- Count 1 -->
                <div class="col-6 col-md-3 mb-3">
                    <div class="card-premium d-flex align-items-center justify-content-between h-100">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold font-heading" style="font-size: 11px;">Total Murid</span>
                            <h3 class="fw-bold m-0 mt-1 text-dark dark-text-light">{{ $counts['murid'] ?? 0 }}</h3>
                        </div>
                        <div class="p-3 rounded bg-light d-none d-sm-block" style="color: var(--accent-primary); background-color: rgba(79, 70, 229, 0.1) !important;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Count 2 -->
                <div class="col-6 col-md-3 mb-3">
                    <div class="card-premium d-flex align-items-center justify-content-between h-100">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold font-heading" style="font-size: 11px;">Total Guru</span>
                            <h3 class="fw-bold m-0 mt-1 text-dark dark-text-light">{{ $counts['guru'] ?? 0 }}</h3>
                        </div>
                        <div class="p-3 rounded bg-light d-none d-sm-block" style="color: var(--success); background-color: rgba(16, 185, 129, 0.1) !important;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m-6 4h6"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Count 3 -->
                <div class="col-6 col-md-3 mb-3">
                    <div class="card-premium d-flex align-items-center justify-content-between h-100">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold font-heading" style="font-size: 11px;">Mitra DUDI</span>
                            <h3 class="fw-bold m-0 mt-1 text-dark dark-text-light">{{ $counts['dudi'] ?? 0 }}</h3>
                        </div>
                        <div class="p-3 rounded bg-light d-none d-sm-block" style="color: var(--warning); background-color: rgba(245, 158, 11, 0.1) !important;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Count 4 -->
                <div class="col-6 col-md-3 mb-3">
                    <div class="card-premium d-flex align-items-center justify-content-between h-100">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold font-heading" style="font-size: 11px;">Penempatan Aktif</span>
                            <h3 class="fw-bold m-0 mt-1 text-dark dark-text-light">{{ $counts['penempatan_aktif'] ?? 0 }}</h3>
                        </div>
                        <div class="p-3 rounded bg-light d-none d-sm-block" style="color: var(--danger); background-color: rgba(225, 29, 72, 0.1) !important;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Attendance Stats Today (Admin/Guru) -->
    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'guru')
    <div class="row">
        <!-- Kehadiran Hari Ini -->
        <div class="col-md-8 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Kehadiran Hari Ini</h5>
                <div class="row mt-4">
                    <div class="col-4 d-flex flex-column align-items-center text-center">
                        <span class="text-muted small d-flex align-items-center justify-content-center" style="min-height: 40px; line-height: 1.2;">Sudah Hadir</span>
                        <h2 class="fw-bold text-success mt-2 mb-0 font-heading">{{ $attendance['hadir'] ?? 0 }}</h2>
                    </div>
                    <div class="col-4 d-flex flex-column align-items-center text-center">
                        <span class="text-muted small d-flex align-items-center justify-content-center" style="min-height: 40px; line-height: 1.2;">Terlambat</span>
                        <h2 class="fw-bold text-warning mt-2 mb-0 font-heading">{{ $attendance['terlambat'] ?? 0 }}</h2>
                    </div>
                    <div class="col-4 d-flex flex-column align-items-center text-center">
                        <span class="text-muted small d-flex align-items-center justify-content-center" style="min-height: 40px; line-height: 1.2;">Belum Absen</span>
                        <h2 class="fw-bold text-danger mt-2 mb-0 font-heading">{{ $attendance['belum_hadir'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Action / Menu Links -->
        <div class="col-md-4 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Aksi Cepat</h5>
                <div class="list-group list-group-flush">
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('murid.index') }}" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center py-2" style="background-color: transparent; color: var(--text-primary);">
                        <span class="p-1 rounded bg-light me-2 d-flex align-items-center" style="background-color: rgba(79, 70, 229, 0.1) !important; color: var(--accent-primary);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </span>
                        Tambah Data Murid
                    </a>
                    <a href="{{ route('penempatan.index') }}" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center py-2" style="background-color: transparent; color: var(--text-primary);">
                        <span class="p-1 rounded bg-light me-2 d-flex align-items-center" style="background-color: rgba(16, 185, 129, 0.1) !important; color: var(--success);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </span>
                        Lakukan Plotting PKL
                    </a>
                    @else
                    <a href="{{ route('jurnal.index') }}" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center py-2" style="background-color: transparent; color: var(--text-primary);">
                        <span class="p-1 rounded bg-light me-2 d-flex align-items-center" style="background-color: rgba(245, 158, 11, 0.1) !important; color: var(--warning);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        Verifikasi Jurnal Bimbingan
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Student Dashboard (Murid) -->
    @if(auth()->user()->role === 'murid')
    <div class="row">
        <!-- Murid PKL Info -->
        <div class="col-md-6 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Status Penempatan PKL</h5>
                @php
                    $murid = auth()->user()->murid;
                    $penempatan = $murid ? $murid->penempatanAktif : null;
                @endphp

                @if($penempatan)
                    <div class="d-flex flex-column gap-3 mt-3">
                        <!-- Tempat DUDI -->
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background-color: var(--bg-canvas); border: 1px solid var(--border-color);">
                            <div class="p-2 rounded bg-primary-light text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-muted d-block small mb-1 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Tempat DUDI</span>
                                <span class="font-heading fw-bold text-dark" style="font-size: 14px; line-height: 1.4;">{{ $penempatan->dudi->nama }}</span>
                            </div>
                        </div>

                        <!-- Guru Pembimbing -->
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background-color: var(--bg-canvas); border: 1px solid var(--border-color);">
                            <div class="p-2 rounded bg-success-light text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m-6 4h6"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-muted d-block small mb-1 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Guru Pembimbing</span>
                                <span class="fw-semibold text-dark font-heading" style="font-size: 14px; line-height: 1.4;">{{ $penempatan->guru->nama }}</span>
                            </div>
                        </div>

                        <!-- Pembimbing Industri -->
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background-color: var(--bg-canvas); border: 1px solid var(--border-color);">
                            <div class="p-2 rounded bg-warning-light text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-muted d-block small mb-1 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Pembimbing Industri</span>
                                <span class="fw-semibold text-dark font-heading" style="font-size: 14px; line-height: 1.4;">
                                    {{ $penempatan->pembimbingIndustri ? $penempatan->pembimbingIndustri->nama : ($penempatan->dudi->pic_nama ? $penempatan->dudi->pic_nama . ' (' . $penempatan->dudi->pic_phone . ')' : 'Belum di-assign') }}
                                </span>
                            </div>
                        </div>

                        <!-- Tanggal Pelaksanaan -->
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background-color: var(--bg-canvas); border: 1px solid var(--border-color);">
                            <div class="p-2 rounded bg-indigo-light text-indigo d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-muted d-block small mb-1 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Tanggal Pelaksanaan</span>
                                <span class="fw-semibold text-dark font-heading" style="font-size: 14px; line-height: 1.4;">
                                    {{ \Carbon\Carbon::parse($penempatan->tanggal_mulai)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($penempatan->tanggal_selesai)->translatedFormat('d F Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <span class="text-muted d-block mb-2">Anda belum ditempatkan di DUDI manapun.</span>
                        <small class="text-muted">Hubungi Admin Hubungan Industri untuk informasi plotting.</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Presensi Harian Cepat -->
        <div class="col-md-6 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Presensi Hari Ini</h5>
                @if($penempatan)
                    <p class="text-muted mb-4" style="font-size: 13px;">Lakukan presensi langsung dari area kerja DUDI.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('presensi.index') }}" class="btn btn-primary py-3 fw-bold font-heading">
                            Buka Menu Presensi Harian
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <span class="text-muted">Fitur presensi tidak aktif (Belum ditempatkan).</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'guru')
    <!-- Active Location Map (Peta Lokasi Aktif) -->
    <div class="row mt-2">
        <!-- Leaflet Map Column -->
        <div class="col-lg-8 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark">Peta Lokasi PKL Aktif</h5>
                <div id="monitoringMap" style="height: 400px; border-radius: 0.5rem; border: 1px solid var(--border-color); z-index: 1;"></div>
            </div>
        </div>

        <!-- DUDI List Column -->
        <div class="col-lg-4 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark">
                    {{ auth()->user()->role === 'guru' ? 'Daftar DUDI Bimbingan Anda' : 'Daftar Mitra DUDI Aktif' }}
                </h5>
                
                <div class="pe-2" style="max-height: 400px; overflow-y: auto;">
                    @forelse($dudiList as $dudiId => $dudiItem)
                        <div class="p-3 mb-3 border rounded" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important;">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="fw-bold text-primary font-heading" style="font-size: 13.5px;">{{ $dudiItem['dudi']->nama }}</span>
                                <span class="badge bg-primary-light text-primary" style="font-size: 10px; font-weight: 700;">{{ count($dudiItem['placements']) }} Siswa</span>
                            </div>
                            <small class="text-muted d-block mt-1 mb-2" style="font-size: 11px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="align-middle me-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>{{ $dudiItem['dudi']->alamat }}
                            </small>
                            
                            @if(auth()->user()->role === 'guru')
                                <!-- Show details (like list of students) for Guru -->
                                <div class="mt-2 pt-2 border-top" style="border-top-color: var(--border-color) !important;">
                                    <span class="text-muted d-block mb-1 fw-semibold" style="font-size: 10px; text-uppercase;">Daftar Siswa Bimbingan:</span>
                                    <ul class="ps-3 mb-0" style="font-size: 11px; color: var(--text-primary);">
                                        @foreach($dudiItem['placements'] as $placement)
                                            <li class="mb-1">
                                                <strong>{{ $placement->murid->nama }}</strong> ({{ $placement->murid->kelas->nama }})
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted small">
                            Belum ada bimbingan aktif di DUDI saat ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Announcements Feed (Visible to all users) -->
    <div class="row mt-2">
        <div class="col-12 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-3 text-dark">Informasi & Pengumuman Terbaru</h5>
                <div class="row">
                    @forelse($announcements as $announce)
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded h-100" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold m-0 font-heading text-primary" style="font-size: 14px;">{{ $announce->judul }}</h6>
                                    <small class="text-muted" style="font-size: 10px;">{{ $announce->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="text-secondary small m-0" style="line-height: 1.5;">
                                    {!! nl2br(e(Str::limit($announce->isi, 200))) !!}
                                    @if(strlen($announce->isi) > 200)
                                        <a href="javascript:void(0);" class="text-primary fw-semibold ms-1" data-bs-toggle="modal" data-bs-target="#readDashboardAnnounceModal_{{ $announce->id }}">Baca Selengkapnya</a>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Read Dashboard Modal -->
                        <div class="modal fade text-start" id="readDashboardAnnounceModal_{{ $announce->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                    <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                        <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">{{ $announce->judul }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="text-muted small mb-3">
                                            Dipublikasikan pada: {{ $announce->created_at->translatedFormat('l, d F Y') }}
                                        </div>
                                        <div style="white-space: pre-line; font-size: 13px; line-height: 1.6; color: var(--text-primary);">
                                            {!! e($announce->isi) !!}
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4 text-muted small">
                            Tidak ada pengumuman terbaru saat ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if(auth()->user()->role === 'admin' || auth()->user()->role === 'guru')
@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Fix Leaflet broken default marker icons
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        });

        // Define Custom DivIcons for premium visual styling
        const dudiIcon = L.divIcon({
            className: 'custom-dudi-marker',
            html: `<div style="background-color: #4f46e5; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(15,23,42,0.35); display: flex; align-items: center; justify-content: center; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                    </svg>
                   </div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        const studentIcon = L.divIcon({
            className: 'custom-student-marker',
            html: `<div style="background-color: #10b981; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(16,185,129,0.35); display: flex; align-items: center; justify-content: center; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                   </div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        const placements = @json($placements);
        const todayPresensi = @json($todayPresensi);
        const dudiGroups = {};

        placements.forEach(p => {
            if (p.dudi) {
                if (!dudiGroups[p.dudi_id]) {
                    dudiGroups[p.dudi_id] = {
                        name: p.dudi.nama,
                        alamat: p.dudi.alamat,
                        lat: parseFloat(p.dudi.latitude),
                        lng: parseFloat(p.dudi.longitude),
                        students: []
                    };
                }
                dudiGroups[p.dudi_id].students.push(p);
            }
        });

        // Initialize map centered at first DUDI or average center
        let mapCenter = [-6.200000, 106.816666]; // Default Jakarta
        const dudiKeys = Object.keys(dudiGroups);
        
        if (dudiKeys.length > 0) {
            mapCenter = [dudiGroups[dudiKeys[0]].lat, dudiGroups[dudiKeys[0]].lng];
        }

        const map = L.map('monitoringMap').setView(mapCenter, 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const markers = [];
        const isGuru = @json(auth()->user()->role === 'guru');

        // Draw DUDI markers (shown for both Admin and Guru)
        Object.values(dudiGroups).forEach(dudi => {
            let tooltipContent = `<div class="p-1">` +
                                 `<strong style="font-size: 12.5px; color: var(--accent-primary);">${dudi.name}</strong>` +
                                 `<div class="text-muted mt-1" style="font-size: 11px; max-width: 200px; white-space: normal; line-height: 1.3;">` +
                                 `<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1" style="display:inline-block; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>` +
                                 `${dudi.alamat}</div>` +
                                 `<div class="mt-1" style="font-size: 11px; font-weight: 600; color: var(--accent-primary);">` +
                                 `Siswa Terplotting: ${dudi.students.length} Murid</div>` +
                                 `</div>`;

            const marker = L.marker([dudi.lat, dudi.lng], { icon: dudiIcon }).addTo(map)
                .bindTooltip(tooltipContent, { direction: 'top', permanent: false });
            
            markers.push(marker);
        });

        // Draw student presensi markers (only for Guru)
        if (isGuru && todayPresensi && todayPresensi.length > 0) {
            todayPresensi.forEach(presensi => {
                const placement = placements.find(pl => pl.id === presensi.penempatan_pkl_id);
                if (placement) {
                    const studentName = placement.murid.nama;
                    const kelasName = placement.murid.kelas.nama;
                    
                    // 1. Check-in Marker
                    if (presensi.lat_masuk && presensi.lng_masuk) {
                        const checkinLat = parseFloat(presensi.lat_masuk);
                        const checkinLng = parseFloat(presensi.lng_masuk);
                        
                        let checkinTooltip = `<div class="p-1">` +
                                             `<strong>${studentName}</strong> <small class="text-muted">(${kelasName})</small><br>` +
                                             `<span class="badge bg-success-soft mt-1 mb-1">Presensi Masuk</span><br>` +
                                             `<small class="text-muted">Jam: <strong>${presensi.jam_masuk || '-'}</strong> | Status: <strong>${presensi.status_masuk ? presensi.status_masuk.replace('_', ' ') : '-'}</strong></small>` +
                                             `</div>`;
                        
                        const checkinMarker = L.marker([checkinLat, checkinLng], { icon: studentIcon }).addTo(map)
                            .bindTooltip(checkinTooltip, { direction: 'top', permanent: false });
                        
                        markers.push(checkinMarker);
                    }
                    
                    // 2. Check-out Marker
                    if (presensi.lat_pulang && presensi.lng_pulang) {
                        const checkoutLat = parseFloat(presensi.lat_pulang);
                        const checkoutLng = parseFloat(presensi.lng_pulang);
                        
                        let checkoutTooltip = `<div class="p-1">` +
                                              `<strong>${studentName}</strong> <small class="text-muted">(${kelasName})</small><br>` +
                                              `<span class="badge bg-danger-soft mt-1 mb-1">Presensi Pulang</span><br>` +
                                              `<small class="text-muted">Jam: <strong>${presensi.jam_pulang || '-'}</strong> | Status: <strong>${presensi.status_pulang ? presensi.status_pulang.replace('_', ' ') : '-'}</strong></small>` +
                                              `</div>`;
                        
                        const checkoutMarker = L.marker([checkoutLat, checkoutLng], { icon: studentIcon }).addTo(map)
                            .bindTooltip(checkoutTooltip, { direction: 'top', permanent: false });
                        
                        markers.push(checkoutMarker);
                    }
                }
            });
        }

        // Adjust bounds to fit all markers
        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.15));
        }
    });
</script>
@endsection
@endif
