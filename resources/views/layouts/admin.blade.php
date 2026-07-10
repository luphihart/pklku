<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard - ' . ($globalSettings['nama_sekolah'] ?? 'PKLku'))</title>
    <!-- Modern SVG Favicon representing a digital briefcase & learning growth -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%234f46e5' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z'></path><path d='M12 11v6'></path><path d='M9 14h6'></path></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>
<body x-data="{ 
    sidebarOpen: window.innerWidth > 768,
    masterOpen: true,
    aktivitasOpen: true,
    tambahanOpen: true
}" class="g-sidenav-show">
    
    <!-- Sidebar Backdrop for Mobile Overlay -->
    <div class="sidebar-backdrop" x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity style="display: none;"></div>

    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" :class="{ 'collapsed': !sidebarOpen, 'show': sidebarOpen }">
            <div class="sidebar-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded-3 text-primary d-flex align-items-center justify-content-center" style="background-color: rgba(79, 70, 229, 0.08) !important;">
                        <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='var(--accent-primary)' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round' width="22" height="22"><path d='M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z'></path><path d='M12 11v6'></path><path d='M9 14h6'></path></svg>
                    </div>
                    <div>
                        <h5 class="m-0 font-heading fw-bold" style="color: var(--accent-primary); letter-spacing: -0.5px; line-height: 1.2;">PKLku</h5>
                        <small class="text-muted" style="font-size: 12.5px; font-weight: 500; display: block; margin-top: 1px;">{{ $globalSettings['nama_sekolah'] ?? 'SMK N 1' }}</small>
                    </div>
                </div>
                <button class="btn btn-sm d-md-none border-0 text-secondary" @click="sidebarOpen = false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <ul class="sidebar-menu">
                <li class="{{ Route::is('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                </li>

                <!-- Master Data Collapsible Section -->
                <li class="sidebar-header-text d-flex justify-content-between align-items-center px-4 pt-3 pb-1 text-uppercase font-heading" style="font-size: 10px; font-weight: 700; color: var(--text-secondary); cursor: pointer; user-select: none;" @click="masterOpen = !masterOpen">
                    <span>Master Data</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" :style="masterOpen ? 'transform: rotate(0deg); transition: transform 0.2s;' : 'transform: rotate(-90deg); transition: transform 0.2s;'" style="margin-right: 1.5rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                    </svg>
                </li>
                
                @if(auth()->user()->role === 'admin')
                <li x-show="masterOpen" class="{{ Request::is('master/tahun-ajaran*') ? 'active' : '' }}">
                    <a href="{{ route('tahun-ajaran.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Tahun Ajaran
                    </a>
                </li>
                <li x-show="masterOpen" class="{{ Request::is('master/jurusan*') ? 'active' : '' }}">
                    <a href="{{ route('jurusan.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Jurusan
                    </a>
                </li>
                <li x-show="masterOpen" class="{{ Request::is('master/kelas*') ? 'active' : '' }}">
                    <a href="{{ route('kelas.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Kelas
                    </a>
                </li>
                <li x-show="masterOpen" class="{{ Request::is('master/murid*') ? 'active' : '' }}">
                    <a href="{{ route('murid.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Murid
                    </a>
                </li>
                <li x-show="masterOpen" class="{{ Request::is('master/guru*') ? 'active' : '' }}">
                    <a href="{{ route('guru.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m-6 4h6"/>
                        </svg>
                        Guru
                    </a>
                </li>
                <li x-show="masterOpen" class="{{ Request::is('master/dudi*') ? 'active' : '' }}">
                    <a href="{{ route('dudi.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Mitra DUDI
                    </a>
                </li>
                <li x-show="masterOpen" class="{{ Request::is('penempatan*') ? 'active' : '' }}">
                    <a href="{{ route('penempatan.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Plotting Penempatan
                    </a>
                </li>
                @endif

                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'guru')
                <li x-show="masterOpen" class="{{ Request::is('master/tujuan-pembelajaran*') ? 'active' : '' }}">
                    <a href="{{ route('tujuan-pembelajaran.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Tujuan Pembelajaran
                    </a>
                </li>
                <li x-show="masterOpen" class="{{ Request::is('master/indikator*') ? 'active' : '' }}">
                    <a href="{{ route('indikator.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 00-2 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Indikator Penilaian
                    </a>
                </li>
                @endif

                <!-- Aktivitas PKL Collapsible Section -->
                <li class="sidebar-header-text d-flex justify-content-between align-items-center px-4 pt-3 pb-1 text-uppercase font-heading" style="font-size: 10px; font-weight: 700; color: var(--text-secondary); cursor: pointer; user-select: none;" @click="aktivitasOpen = !aktivitasOpen">
                    <span>Aktivitas PKL</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" :style="aktivitasOpen ? 'transform: rotate(0deg); transition: transform 0.2s;' : 'transform: rotate(-90deg); transition: transform 0.2s;'" style="margin-right: 1.5rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                    </svg>
                </li>

                <li x-show="aktivitasOpen" class="{{ Request::is('presensi') ? 'active' : '' }}">
                    <a href="{{ route('presensi.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ auth()->user()->role === 'murid' ? 'Presensi Harian' : 'Monitoring Presensi' }}
                    </a>
                </li>

                <li x-show="aktivitasOpen" class="{{ Request::is('presensi/izin*') ? 'active' : '' }}">
                    <a href="{{ route('izin.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ auth()->user()->role === 'murid' ? 'Pengajuan Izin/Sakit' : 'Verifikasi Izin/Sakit' }}
                    </a>
                </li>

                <li x-show="aktivitasOpen" class="{{ Request::is('jurnal*') ? 'active' : '' }}">
                    <a href="{{ route('jurnal.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Jurnal Kegiatan
                    </a>
                </li>

                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'guru')
                <li x-show="aktivitasOpen" class="{{ Request::is('monitoring*') ? 'active' : '' }}">
                    <a href="{{ route('monitoring.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        Monitoring PKL
                    </a>
                </li>
                @endif

                <li x-show="aktivitasOpen" class="{{ Request::is('penilaian*') ? 'active' : '' }}">
                    <a href="{{ route('penilaian.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Penilaian PKL
                    </a>
                </li>

                <!-- Tambahan Collapsible Section -->
                <li class="sidebar-header-text d-flex justify-content-between align-items-center px-4 pt-3 pb-1 text-uppercase font-heading" style="font-size: 10px; font-weight: 700; color: var(--text-secondary); cursor: pointer; user-select: none;" @click="tambahanOpen = !tambahanOpen">
                    <span>Tambahan</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" :style="tambahanOpen ? 'transform: rotate(0deg); transition: transform 0.2s;' : 'transform: rotate(-90deg); transition: transform 0.2s;'" style="margin-right: 1.5rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                    </svg>
                </li>

                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'guru')
                <li x-show="tambahanOpen" class="{{ Request::is('laporan*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Laporan Rekap
                    </a>
                </li>
                @endif

                <li x-show="tambahanOpen" class="{{ Request::is('pengumuman*') ? 'active' : '' }}">
                    <a href="{{ route('pengumuman.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Pengumuman
                    </a>
                </li>

                @if(auth()->user()->role === 'admin')
                <li x-show="tambahanOpen" class="{{ Request::is('setting*') ? 'active' : '' }}">
                    <a href="{{ route('setting.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        Setting Sekolah
                    </a>
                </li>
                
                <li x-show="tambahanOpen" class="{{ Request::is('system*') ? 'active' : '' }}">
                    <a href="{{ route('system.index') }}">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Audit Log & Backup
                    </a>
                </li>
                @endif
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Topbar -->
            <nav class="topbar">
                <div class="d-flex align-items-center">
                    <button class="btn border-0 text-secondary p-2 me-2 d-flex align-items-center justify-content-center" @click="sidebarOpen = !sidebarOpen" style="outline: none; box-shadow: none; min-width: 40px; min-height: 40px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="pointer-events: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h5 class="m-0 d-none d-sm-block font-heading fw-bold">@yield('page_title', 'Sistem Informasi PKL')</h5>
                </div>

                <div class="d-flex align-items-center">
                    <!-- Dark theme toggle removed -->

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="btn dropdown-toggle d-flex align-items-center border-0 text-secondary p-0" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ auth()->user()->photo ? asset('storage/profiles/' . auth()->user()->photo) : 'https://www.gravatar.com/avatar/' . md5(auth()->user()->email) . '?d=mp' }}" alt="Foto Profile" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                            <span class="d-none d-md-inline text-dark dark-text-light" style="font-size: 14px; font-weight: 500;">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userMenu" style="background-color: var(--bg-card); border: 1px solid var(--border-color) !important;">
                            <li>
                                <div class="px-3 py-2 border-bottom mb-1" style="border-bottom-color: var(--border-color) !important;">
                                    <span class="d-block fw-bold text-dark dark-text-light" style="font-size: 13px;">{{ auth()->user()->name }}</span>
                                    <small class="text-muted text-uppercase" style="font-size: 10px; font-weight: 700;">{{ auth()->user()->role }}</small>
                                </div>
                            </li>
                            <li><a class="dropdown-item py-2" href="{{ route('profile') }}">Profil Saya</a></li>
                            <li><hr class="dropdown-divider" style="background-color: var(--border-color);"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger">Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="main-content">
                <!-- Alert Area -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="background-color: rgba(16, 185, 129, 0.1); color: var(--success); border-left: 4px solid var(--success) !important;">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="background-color: rgba(225, 29, 72, 0.1); color: var(--danger); border-left: 4px solid var(--danger) !important;">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="background-color: rgba(79, 70, 229, 0.1); color: var(--accent-primary); border-left: 4px solid var(--accent-primary) !important;">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="background-color: rgba(225, 29, 72, 0.1); color: var(--danger); border-left: 4px solid var(--danger) !important;">
                        <ul class="mb-0 ps-3" style="font-size: 13px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
</body>
</html>
