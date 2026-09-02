@extends('layouts.admin')

@section('title', 'Dashboard - PKLku')
@section('page_title', 'Dashboard Utama')

@section('content')
<div class="container-fluid p-0">
    <!-- Birthday Greeting (If applicable) -->
    @if(auth()->user()->tanggal_lahir && auth()->user()->tanggal_lahir->isBirthday())
    @php
        $role = auth()->user()->role;

        // Ucapan Murid (Gaya Gen Z, santai, seputar PKL, laporan, jurnal, doa & humor)
        $muridBirthdayMessages = [
            "HBD ya! Manifesting jurnal harian langsung di-ACC sat-set, laporan no revisi-revisi club, dan dapet nilai PKL super gacor! Tetap menyala! 🔥🎂",
            "Met ultah! Semoga makin slay di tempat PKL, presensi selalu on time no telat-telat, dan dapet pembimbing industri yang chill abis! 🎉✨",
            "Happy birthday bro/sis! Kado terindah tahun ini: Dapet tugas PKL yang seru, snack kantor melimpah, dan laporan langsung ACC tanpa drama! 🥳🍰",
            "Met berkurang umur! Doa terbaik buat kamu: sehat selalu, skill makin nambah, dan ga kena plot twist pembimbing galak haha. Traktirannya spill dong! 😋🎁",
            "Happy level up day! Semoga perjalanan PKL kamu lancar jaya, mental aman ga gampang burnout, dan masa depan makin cerah menyala! 🚀🎓",
            "Met ultah kawan seper-PKL-an! Semoga hari ini ga ada revisian jurnal, presensi anti radius error, dan jalan rezekimu makin deras! 🎈🌟",
            "Happy birthday! Tetap semangat walau deadline laporan menanti. Semoga selalu dikelilingi circle positif dan impianmu terkabul satu per satu! 💪🎉",
            "Selamat ulang tahun! Jangan overthinking mikirin laporan hari ini, nikmati harimu dulu. Semoga sukses selalu dan dapet nilai A mutlak! 🏆🎂",
        ];

        // Ucapan Guru (Gaya Milenial, sedikit formal, seputar bimbingan, monitoring, doa berkah & humor ringan)
        $guruBirthdayMessages = [
            "Selamat ulang tahun, Bapak/Ibu Guru! Semoga panjang umur, berkah rezeki, dan selalu dilimpahi kesehatan. Semoga murid bimbingan PKL selalu tertib dan rajin setor laporan tanpa perlu diingatkan berkali-kali! 🎂✨",
            "Barakallah fii umrik! Semoga segala dedikasi dan ilmu yang Bapak/Ibu bagikan menjadi amal jariyah yang abadi. Doa terbaik untuk karir, keluarga, dan semoga monitoring PKL selalu lancar tanpa kendala! 🤲🌸",
            "Selamat bertambah usia! Semoga senantiasa diberikan kesabaran ekstra, work-life balance yang harmonis, dan murid-murid PKL yang taat aturan serta berakhlak mulia. Jangan lupa luangkan waktu untuk self-reward hari ini! ☕🍰",
            "Selamat ulang tahun Bapak/Ibu Guru terhebat! Semoga sehat walafiat, rezeki berkah melimpah, dan proses validasi jurnal serta nilai PKL selalu sat-set tanpa drama administrasi! 🎉💐",
            "Happy birthday, Bapak/Ibu! Semoga di usia yang baru ini makin sukses menginspirasi generasi muda. Bonus doa: semoga agenda monitoring PKL selalu pas dengan jadwal kulineran enak di sekitar DUDI! 🚗🍽️",
            "Selamat ulang tahun! Terima kasih atas dedikasi tanpa lelah membimbing siswa. Semoga hari ini penuh berkah, bahagia, dan bebas dari tumpukan notifikasi revisi jurnal! 🎁🌟",
            "Barakallah fii umrik, Bapak/Ibu Guru! Semoga selalu diberi energi positif, kebahagiaan bersama keluarga tercinta, dan seluruh siswa bimbingan lulus PKL dengan predikat membanggakan! 🏆🎈",
            "Selamat hari lahir! Semoga langkah Bapak/Ibu selalu dimudahkan, senantiasa dilindungi Allah SWT, dan tetap semangat mendidik calon penerus bangsa dengan senyuman terbaik! 🎂🎉",
        ];

        // Ucapan Admin (Gaya Milenial santai, seputar server, plotting, database, doa & humor IT/admin)
        $adminBirthdayMessages = [
            "Happy birthday, Min! Semoga panjang umur, sehat selalu, dan server PKLku selalu adem ayem uptime 99.99% tanpa drama error 500! 🚀🎂",
            "Met ultah, pahlawan di balik layar! Doa terbaik buat karir dan rezekimu. Semoga plotting massal selalu lancar, import Excel ga pernah corrupt, dan kopi selalu hangat! ☕✨",
            "Selamat ulang tahun! Semoga makin berkah usianya, hidup makin terstruktur kayak database yang ternormalisasi, dan bebas dari komplain user yang lupa password! 💾🥳",
            "Happy level up, Admin andalan! Semoga rezekinya auto-increment, beban kerja scalable, dan segala urusan plotting penempatan murid beres dalam sekali klik! 🏆🎁",
            "Met ultah bro/sis! Semoga hari ini full senyum, server anti-down, query database secepat kilat, dan ada yang ngirimin pizza atau kopi ke ruang admin! 🍕💻",
            "Selamat bertambah usia! Semoga sehat walafiat, terhindar dari bug misterius di hari Senin, dan ekosistem PKLku makin solid di tangan dinginmu! 🔥🎉",
            "Happy birthday! Tetap santai dan jangan panik walau tiket bantuan masuk bertubi-tubi. Semoga selalu dilancarkan segala urusan dunia dan akhirat! 🎈🌟",
            "Met ulang tahun! Doa spesial: semoga sistem bebas bug, data siswa selalu rapi, dan bonus tahunan turun tepat waktu tanpa pending! 🍰💸",
        ];

        if ($role === 'murid') {
            $birthdayMessages = $muridBirthdayMessages;
            $titleGreeting = "Selamat Ulang Tahun, " . auth()->user()->name . "! 🎂";
        } elseif ($role === 'guru') {
            $birthdayMessages = $guruBirthdayMessages;
            $titleGreeting = "Selamat Ulang Tahun, Bapak/Ibu " . auth()->user()->name . "! 🎂";
        } else {
            $birthdayMessages = $adminBirthdayMessages;
            $titleGreeting = "Selamat Ulang Tahun, " . auth()->user()->name . " (Admin)! 🎂";
        }

        $randomBirthdayMsg = $birthdayMessages[array_rand($birthdayMessages)];
    @endphp
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
                <h4 class="fw-bold font-heading m-0">{{ $titleGreeting }}</h4>
                <p class="m-0 mt-1" style="font-size: 13px; opacity: 0.95; line-height: 1.5;">{{ $randomBirthdayMsg }}</p>
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
            <div class="card-premium h-100">
                @php
                    $totalPkl     = $attendance['total_pkl']    ?? 0;
                    $hadirTotal   = $attendance['hadir']         ?? 0;
                    $tepat        = $attendance['tepat_waktu']   ?? 0;
                    $telat        = $attendance['terlambat']     ?? 0;
                    $izin         = $attendance['izin']          ?? 0;
                    $sakit        = $attendance['sakit']         ?? 0;
                    $liburShift   = $attendance['libur_shift']   ?? 0;
                    $belum        = $attendance['belum_hadir']   ?? 0;
                    $alpha        = $attendance['alpha']         ?? 0;
                    $rate         = $totalPkl > 0 ? round(($hadirTotal / $totalPkl) * 100) : 0;
                    $tepatP       = $totalPkl > 0 ? ($tepat      / $totalPkl) * 100 : 0;
                    $telatP       = $totalPkl > 0 ? ($telat      / $totalPkl) * 100 : 0;
                    $izinP        = $totalPkl > 0 ? (($izin + $sakit) / $totalPkl) * 100 : 0;
                    $liburP       = $totalPkl > 0 ? ($liburShift / $totalPkl) * 100 : 0;
                    $alphaP       = $totalPkl > 0 ? ($alpha      / $totalPkl) * 100 : 0;
                @endphp

                {{-- Header --}}
                <div class="mb-3">
                    <h5 class="fw-bold font-heading m-0 text-dark dark-text-light" style="font-size: 15px;">Kehadiran Hari Ini</h5>
                    <p class="text-muted m-0 mt-0.5" style="font-size: 12px;">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }} &bull; <span class="fw-semibold">{{ $totalPkl }} Siswa Aktif</span></p>
                </div>

                {{-- Progress Bar --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted" style="font-size: 11px;">Persentase Kehadiran</span>
                        <span class="fw-bold text-success" style="font-size: 12px;">{{ $rate }}%</span>
                    </div>
                    <div class="rounded-pill overflow-hidden" style="height: 6px; background-color: var(--border-color);">
                        <div class="h-100 d-flex">
                            <div style="width: {{ $tepatP }}%; background-color: #10b981; transition: width 0.4s;"></div>
                            <div style="width: {{ $telatP }}%; background-color: #f59e0b; transition: width 0.4s;"></div>
                            <div style="width: {{ $izinP  }}%; background-color: #0ea5e9; transition: width 0.4s;"></div>
                            <div style="width: {{ $liburP }}%; background-color: #6366f1; transition: width 0.4s;"></div>
                            <div style="width: {{ $alphaP }}%; background-color: #ef4444; transition: width 0.4s;"></div>
                        </div>
                    </div>
                </div>

                {{-- Stat Cards --}}
                <div class="row g-2">

                    {{-- Hadir --}}
                    <div class="col-6 col-sm-6 col-lg">
                        <div class="rounded-3 p-3 text-center h-100 d-flex flex-column gap-1" style="background: rgba(16,185,129,.07); border: 1.5px solid rgba(16,185,129,.2);">
                            <span class="fw-bold text-uppercase" style="font-size: 10px; letter-spacing:.5px; color:#059669;">Hadir</span>
                            <span class="fw-bold font-heading lh-1" style="font-size: 26px; color:#059669;">{{ $hadirTotal }}</span>
                            <span class="text-muted" style="font-size: 10.5px;">{{ $tepat }} Tepat &bull; {{ $telat }} Telat</span>
                        </div>
                    </div>

                    {{-- Izin / Sakit --}}
                    <div class="col-6 col-sm-6 col-lg">
                        <div class="rounded-3 p-3 text-center h-100 d-flex flex-column gap-1" style="background: rgba(14,165,233,.07); border: 1.5px solid rgba(14,165,233,.2);">
                            <span class="fw-bold text-uppercase" style="font-size: 10px; letter-spacing:.5px; color:#0284c7;">Izin / Sakit</span>
                            <span class="fw-bold font-heading lh-1" style="font-size: 26px; color:#0284c7;">{{ $izin + $sakit }}</span>
                            <span class="text-muted" style="font-size: 10.5px;">{{ $izin }} Izin &bull; {{ $sakit }} Sakit</span>
                        </div>
                    </div>

                    {{-- Libur Shift --}}
                    <div class="col-4 col-sm-4 col-lg">
                        <div class="rounded-3 p-3 text-center h-100 d-flex flex-column gap-1" style="background: rgba(99,102,241,.07); border: 1.5px solid rgba(99,102,241,.2);">
                            <span class="fw-bold text-uppercase" style="font-size: 10px; letter-spacing:.5px; color:#4f46e5;">Libur Shift</span>
                            <span class="fw-bold font-heading lh-1" style="font-size: 26px; color:#4f46e5;">{{ $liburShift }}</span>
                            <span class="text-muted" style="font-size: 10.5px;">Off DUDI</span>
                        </div>
                    </div>

                    {{-- Belum Absen --}}
                    <div class="col-4 col-sm-4 col-lg">
                        <div class="rounded-3 p-3 text-center h-100 d-flex flex-column gap-1" style="background: rgba(100,116,139,.07); border: 1.5px solid rgba(100,116,139,.2);">
                            <span class="fw-bold text-uppercase" style="font-size: 10px; letter-spacing:.5px; color:#475569;">Belum Absen</span>
                            <span class="fw-bold font-heading lh-1" style="font-size: 26px; color:#475569;">{{ $belum }}</span>
                            <span class="text-muted" style="font-size: 10.5px;">Menunggu</span>
                        </div>
                    </div>

                    {{-- Alpha --}}
                    <div class="col-4 col-sm-4 col-lg">
                        <div class="rounded-3 p-3 text-center h-100 d-flex flex-column gap-1" style="background: rgba(239,68,68,.06); border: 1.5px solid rgba(239,68,68,.2);">
                            <span class="fw-bold text-uppercase" style="font-size: 10px; letter-spacing:.5px; color:#dc2626;">Alpha</span>
                            <span class="fw-bold font-heading lh-1" style="font-size: 26px; color:#dc2626;">{{ $alpha }}</span>
                            <span style="font-size: 10.5px; color:#dc2626;">Tanpa Ket.</span>
                        </div>
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
            <div class="card-premium h-100">
                <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Status Penempatan PKL</h5>
                @php
                    $murid = auth()->user()->murid;
                    $penempatan = $murid ? $murid->penempatanAktif : null;
                @endphp

                @if($penempatan)
                    <div class="d-flex flex-column gap-3 mt-3">
                        <!-- Tempat DUDI -->
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background-color: var(--bg-canvas); border: 1px solid var(--border-color);">
                            <div class="p-2 rounded bg-primary-light text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-muted d-block small mb-1 text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Tempat DUDI</span>
                                <span class="font-heading fw-bold text-dark" style="font-size: 15px; line-height: 1.4;">{{ $penempatan->dudi?->nama ?? 'DUDI Terhapus' }}</span>
                            </div>
                        </div>

                        <!-- Guru Pembimbing -->
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background-color: var(--bg-canvas); border: 1px solid var(--border-color);">
                            <div class="p-2 rounded bg-success-light text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m-6 4h6"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-muted d-block small mb-1 text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Guru Pembimbing</span>
                                <span class="fw-semibold text-dark font-heading" style="font-size: 14px; line-height: 1.4;">{{ $penempatan->guru?->nama ?? 'Guru Terhapus' }}</span>
                            </div>
                        </div>

                        <!-- Pembimbing Industri -->
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background-color: var(--bg-canvas); border: 1px solid var(--border-color);">
                            <div class="p-2 rounded bg-warning-light text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-muted d-block small mb-1 text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Pembimbing Industri</span>
                                <span class="fw-semibold text-dark font-heading" style="font-size: 14px; line-height: 1.4;">
                                    {{ $penempatan->pembimbingIndustri ? $penempatan->pembimbingIndustri->nama : ($penempatan->dudi?->pic_nama ? $penempatan->dudi->pic_nama . ' (' . $penempatan->dudi->pic_phone . ')' : 'Belum di-assign') }}
                                </span>
                            </div>
                        </div>

                        <!-- Tanggal Pelaksanaan -->
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background-color: var(--bg-canvas); border: 1px solid var(--border-color);">
                            <div class="p-2 rounded bg-indigo-light text-indigo d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-muted d-block small mb-1 text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Periode PKL</span>
                                <span class="fw-semibold text-dark font-heading" style="font-size: 14px; line-height: 1.4;">
                                    {{ \Carbon\Carbon::parse($penempatan->tanggal_mulai)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($penempatan->tanggal_selesai)->translatedFormat('d F Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="empty-state my-auto">
                        <div class="empty-state-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h6 class="empty-state-title">Belum Ada Penempatan</h6>
                        <p class="empty-state-text">Anda belum di-plotting ke mitra DUDI manapun. Hubungi Tim Hubungan Industri untuk informasi lebih lanjut.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Presensi & Quick Action -->
        <div class="col-md-6 mb-4">
            <div class="card-premium h-100 d-flex flex-column justify-content-between">
                <div>
                    <h5 class="fw-bold font-heading mb-3 text-dark dark-text-light">Aktivitas Harian</h5>
                    @if($penempatan)
                        <p class="text-muted mb-4" style="font-size: 13.5px; line-height: 1.5;">
                            Pastikan Anda melakukan <strong>Presensi Masuk & Pulang</strong> di area kantor DUDI serta mengisi <strong>Jurnal Kegiatan Harian</strong> beserta foto/dokumen bukti kerja.
                        </p>
                        
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('presensi.index') }}" class="btn btn-primary py-3 font-heading fw-bold d-flex align-items-center justify-content-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Buka Presensi GPS & Kamera
                            </a>
                            <a href="{{ route('jurnal.index') }}" class="btn btn-outline-primary py-3 font-heading fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                Tulis Jurnal Kegiatan Harian
                            </a>
                        </div>
                    @else
                        <div class="empty-state my-auto">
                            <div class="empty-state-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h6 class="empty-state-title">Aktivitas Belum Aktif</h6>
                            <p class="empty-state-text">Menu presensi harian dan jurnal kegiatan akan aktif otomatis saat status penempatan Anda telah ditentukan.</p>
                        </div>
                    @endif
                </div>
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
                                <span class="fw-bold text-primary font-heading" style="font-size: 14px;">{{ $dudiItem['dudi']->nama }}</span>
                                <span class="badge bg-primary-light text-primary" style="font-size: 11px; font-weight: 700;">{{ count($dudiItem['placements']) }} Siswa</span>
                            </div>
                            <small class="text-muted d-block mt-1 mb-2" style="font-size: 12px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="align-middle me-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>{{ $dudiItem['dudi']->alamat }}
                            </small>
                            
                            <!-- Student List with Attendance Badges -->
                            <div class="mt-2 pt-2 border-top" style="border-top-color: var(--border-color) !important;">
                                <span class="text-muted d-block mb-1.5 fw-semibold text-uppercase" style="font-size: 10px; letter-spacing: 0.3px;">Status Siswa Hari Ini:</span>
                                <ul class="list-unstyled mb-0" style="font-size: 12px; color: var(--text-primary);">
                                    @foreach($dudiItem['placements'] as $placement)
                                        @php
                                            $pres = $todayPresensi[$placement->id] ?? null;
                                            $leave = $todayLeaves[$placement->id] ?? null;
                                        @endphp
                                        <li class="py-1 d-flex justify-content-between align-items-center border-bottom" style="border-bottom-color: rgba(226, 232, 240, 0.4) !important;">
                                            <div class="text-truncate me-2" style="max-width: 60%;">
                                                <strong class="text-dark d-block text-truncate" style="font-size: 12px;">{{ $placement->murid?->nama ?? 'Murid' }}</strong>
                                                <small class="text-muted d-block" style="font-size: 10.5px;">{{ $placement->murid?->kelas?->nama ?? '-' }}</small>
                                            </div>
                                            <div class="flex-shrink-0 text-end">
                                                @if($pres)
                                                    @if($pres->status_masuk === 'libur_shift')
                                                        <span class="badge bg-info-light text-info fw-semibold" style="font-size: 9.5px; padding: 2px 6px;">Libur Shift</span>
                                                    @elseif($pres->status_masuk === 'alpha')
                                                        <span class="badge bg-danger-light text-danger fw-semibold" style="font-size: 9.5px; padding: 2px 6px;">Alpha</span>
                                                    @elseif($pres->status_masuk === 'tepat_waktu')
                                                        <span class="badge bg-success-light text-success fw-semibold" style="font-size: 9.5px; padding: 2px 6px;">Hadir ({{ substr($pres->jam_masuk, 0, 5) }})</span>
                                                    @elseif($pres->status_masuk === 'terlambat')
                                                        <span class="badge bg-warning-light text-warning fw-semibold" style="font-size: 9.5px; padding: 2px 6px;">Telat ({{ substr($pres->jam_masuk, 0, 5) }})</span>
                                                    @else
                                                        <span class="badge bg-success-light text-success fw-semibold" style="font-size: 9.5px; padding: 2px 6px;">Hadir</span>
                                                    @endif
                                                @elseif($leave)
                                                    @if($leave->tipe === 'izin')
                                                        <span class="badge bg-info-light text-info fw-semibold" style="font-size: 9.5px; padding: 2px 6px;" title="{{ $leave->alasan }}">Izin</span>
                                                    @else
                                                        <span class="badge bg-danger-light text-danger fw-semibold" style="font-size: 9.5px; padding: 2px 6px;" title="{{ $leave->alasan }}">Sakit</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary-light text-muted fw-semibold" style="font-size: 9.5px; padding: 2px 6px;">Belum Absen</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-4">
                            <div class="empty-state-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <span class="empty-state-text d-block m-0">Belum ada bimbingan aktif di DUDI saat ini.</span>
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
@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #monitoringMap {
        z-index: 1;
    }
    .leaflet-container {
        font-family: inherit;
    }
</style>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const mapContainer = document.getElementById('monitoringMap');
        if (!mapContainer || typeof L === 'undefined') return;

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
                const lat = parseFloat(p.dudi.latitude);
                const lng = parseFloat(p.dudi.longitude);
                if (!isNaN(lat) && !isNaN(lng)) {
                    if (!dudiGroups[p.dudi_id]) {
                        dudiGroups[p.dudi_id] = {
                            name: p.dudi.nama,
                            alamat: p.dudi.alamat,
                            lat: lat,
                            lng: lng,
                            students: []
                        };
                    }
                    dudiGroups[p.dudi_id].students.push(p);
                }
            }
        });

        // Initialize map centered at first DUDI or default coordinate
        let mapCenter = [-7.005145, 110.438125]; // Default Jawa Tengah
        const dudiKeys = Object.keys(dudiGroups);
        
        if (dudiKeys.length > 0) {
            mapCenter = [dudiGroups[dudiKeys[0]].lat, dudiGroups[dudiKeys[0]].lng];
        }

        const map = L.map('monitoringMap').setView(mapCenter, 12);

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

        // Draw student presensi markers (for Guru and Admin)
        const presensiList = Array.isArray(todayPresensi) 
            ? todayPresensi 
            : Object.values(todayPresensi || {});

        if (presensiList && presensiList.length > 0) {
            presensiList.forEach(presensi => {
                const placement = placements.find(pl => String(pl.id) === String(presensi.penempatan_pkl_id));
                if (placement) {
                    const studentName = placement.murid ? placement.murid.nama : 'Siswa';
                    const kelasName = placement.murid && placement.murid.kelas ? placement.murid.kelas.nama : '-';
                    
                    // 1. Check-in Marker
                    if (presensi.lat_masuk !== null && presensi.lat_masuk !== undefined && presensi.lng_masuk !== null && presensi.lng_masuk !== undefined) {
                        const checkinLat = parseFloat(presensi.lat_masuk);
                        const checkinLng = parseFloat(presensi.lng_masuk);
                        
                        if (!isNaN(checkinLat) && !isNaN(checkinLng) && checkinLat !== 0) {
                            let statusText = presensi.status_masuk === 'terlambat' ? 'Terlambat' : (presensi.status_masuk === 'libur_shift' ? 'Libur Shift' : (presensi.status_masuk === 'alpha' ? 'Alpha' : 'Tepat Waktu'));
                            let checkinTooltip = `<div class="p-1">` +
                                                 `<strong style="color: var(--accent-primary); font-size: 12px;">${studentName}</strong> <small class="text-muted">(${kelasName})</small><br>` +
                                                 `<span class="badge bg-success-light text-success mt-1 mb-1" style="font-size: 10px;">Presensi Masuk</span><br>` +
                                                 `<small class="text-muted">Jam: <strong>${presensi.jam_masuk ? presensi.jam_masuk.substring(0,5) : '-'}</strong> | Status: <strong>${statusText}</strong></small>` +
                                                 `</div>`;
                            
                            const checkinMarker = L.marker([checkinLat, checkinLng], { icon: studentIcon }).addTo(map)
                                .bindTooltip(checkinTooltip, { direction: 'top', permanent: false });
                            
                            markers.push(checkinMarker);
                        }
                    }
                    
                    // 2. Check-out Marker
                    if (presensi.lat_pulang !== null && presensi.lat_pulang !== undefined && presensi.lng_pulang !== null && presensi.lng_pulang !== undefined) {
                        const checkoutLat = parseFloat(presensi.lat_pulang);
                        const checkoutLng = parseFloat(presensi.lng_pulang);
                        
                        if (!isNaN(checkoutLat) && !isNaN(checkoutLng) && checkoutLat !== 0) {
                            let checkoutTooltip = `<div class="p-1">` +
                                                  `<strong style="color: var(--accent-primary); font-size: 12px;">${studentName}</strong> <small class="text-muted">(${kelasName})</small><br>` +
                                                  `<span class="badge bg-warning-light text-warning mt-1 mb-1" style="font-size: 10px;">Presensi Pulang</span><br>` +
                                                  `<small class="text-muted">Jam: <strong>${presensi.jam_pulang ? presensi.jam_pulang.substring(0,5) : '-'}</strong></small>` +
                                                  `</div>`;
                            
                            const checkoutMarker = L.marker([checkoutLat, checkoutLng], { icon: studentIcon }).addTo(map)
                                .bindTooltip(checkoutTooltip, { direction: 'top', permanent: false });
                            
                            markers.push(checkoutMarker);
                        }
                    }
                }
            });
        }

        // Adjust bounds to fit all markers
        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.15));
        }

        // Fix Leaflet tile loading when container sizes are calculated
        setTimeout(function() {
            map.invalidateSize();
        }, 300);
    });
</script>
@endsection
@endif
