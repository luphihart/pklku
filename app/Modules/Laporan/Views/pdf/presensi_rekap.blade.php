<!DOCTYPE html>
<html>
<head>
    <title>Laporan Rekapitulasi Kehadiran Siswa PKL</title>
    <style>
        @page {
            margin: 15mm 12mm 15mm 12mm;
        }
        * {
            font-family: 'Arial', 'Helvetica', sans-serif;
            box-sizing: border-box;
        }
        body {
            font-size: 10px;
            line-height: 1.4;
            color: #222;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px double #333;
            padding-bottom: 6px;
        }
        .header h2 {
            margin: 0;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #111;
        }
        .header h3 {
            margin: 2px 0 0 0;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: bold;
            color: #222;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 9px;
            color: #555;
        }
        .title-section {
            text-align: center;
            margin: 10px 0 14px 0;
        }
        .title-section .main-title {
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-decoration: underline;
            margin-bottom: 3px;
        }
        .title-section .sub-title {
            font-size: 10.5px;
            color: #444;
            font-weight: bold;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9.5px;
        }
        .table-data th, .table-data td {
            border: 1px solid #444;
            padding: 5px 6px;
        }
        .table-data th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.2px;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .badge-hadir { color: #155724; font-weight: bold; }
        .badge-terlambat { color: #856404; font-weight: bold; }
        .badge-izin { color: #0c5460; font-weight: bold; }
        .badge-sakit { color: #383d41; font-weight: bold; }
        .badge-alpha { color: #721c24; font-weight: bold; }

        .summary-box {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .summary-box td {
            padding: 4px 8px;
            font-size: 9.5px;
            background-color: #fafafa;
            border: 1px solid #ddd;
        }

        .footer-sign {
            width: 100%;
            margin-top: 25px;
            font-size: 10px;
        }
        .footer-sign td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <!-- Kop Surat -->
    <div class="header">
        <h2>PEMERINTAH DAERAH PROVINSI</h2>
        <h3>DINAS PENDIDIKAN DAN KEBUDAYAAN</h3>
        <h2 style="font-size: 16px; margin-top: 2px;">{{ $branding['nama_sekolah'] }}</h2>
        <p>{{ $branding['alamat_sekolah'] }}</p>
    </div>

    <!-- Title -->
    <div class="title-section">
        <div class="main-title">LAPORAN REKAPITULASI KEHADIRAN SISWA PKL</div>
        <div class="sub-title">{{ $label }}</div>
    </div>

    @if($filterType === 'harian')
        @php
            $targetDate = $dates[0] ?? now()->toDateString();
            $totHadir = 0;
            $totTelat = 0;
            $totIzin = 0;
            $totSakit = 0;
            $totLiburShift = 0;
            $totAlpha = 0;
        @endphp
        <table class="table-data">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 22%;">Nama Siswa</th>
                    <th style="width: 11%;">NIS</th>
                    <th style="width: 12%;">Kelas</th>
                    <th style="width: 20%;">Tempat DUDI</th>
                    <th style="width: 10%;">Jam Masuk</th>
                    <th style="width: 10%;">Jam Pulang</th>
                    <th style="width: 11%;">Status Masuk</th>
                </tr>
            </thead>
            <tbody>
                @forelse($placements as $index => $p)
                    @php
                        $presensi = $presensiData[$p->id][$targetDate] ?? null;
                        $leave = $leavesByPlacementAndDate[$p->id][$targetDate] ?? null;
                        $holiday = $holidayMap[$targetDate] ?? null;
                        $isPlacementOff = $p->isPlacementHoliday($targetDate);
                        
                        if ($presensi) {
                            if ($presensi->status_masuk === 'libur_shift') $totLiburShift++;
                            elseif ($presensi->status_masuk === 'alpha') $totAlpha++;
                            elseif ($presensi->status_masuk === 'tepat_waktu') $totHadir++;
                            else $totTelat++;
                        } elseif ($leave) {
                            if (strtolower($leave) === 'izin') $totIzin++;
                            else $totSakit++;
                        } elseif ($isPlacementOff) {
                            $totLiburShift++;
                        } elseif (!$holiday) {
                            $totAlpha++;
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-left"><strong>{{ $p->murid?->nama ?? 'Siswa Terhapus' }}</strong></td>
                        <td class="text-center">{{ $p->murid?->nis ?? '-' }}</td>
                        <td class="text-center">{{ $p->murid?->kelas?->nama ?? '-' }}</td>
                        <td class="text-left">{{ $p->dudi?->nama ?? '-' }}</td>
                        <td class="text-center">{{ $presensi && $presensi->jam_masuk ? substr($presensi->jam_masuk, 0, 5) : '-' }}</td>
                        <td class="text-center">{{ $presensi && $presensi->jam_pulang ? substr($presensi->jam_pulang, 0, 5) : '-' }}</td>
                        <td class="text-center">
                            @if($presensi)
                                @if($presensi->status_masuk === 'libur_shift')
                                    <span style="color: #0284c7; font-weight: bold;">Libur Shift DUDI</span>
                                @elseif($presensi->status_masuk === 'alpha')
                                    <span class="badge-alpha">Alpha (Tidak Hadir)</span>
                                @else
                                    <span class="{{ $presensi->status_masuk === 'tepat_waktu' ? 'badge-hadir' : 'badge-terlambat' }}">
                                        {{ $presensi->status_masuk === 'tepat_waktu' ? 'Hadir (Tepat Waktu)' : 'Terlambat' }}
                                    </span>
                                @endif
                            @elseif($leave)
                                <span class="{{ strtolower($leave) === 'izin' ? 'badge-izin' : 'badge-sakit' }}">
                                    {{ $leave }} (Disetujui)
                                </span>
                            @elseif($holiday)
                                <span style="color: #666; font-style: italic;">{{ $holiday }}</span>
                            @elseif($isPlacementOff)
                                <span style="color: #666; font-style: italic;">Libur Operasional DUDI</span>
                            @else
                                <span class="badge-alpha">Belum / Tidak Hadir</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 15px;">Tidak ada data penempatan PKL aktif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Ringkasan Harian -->
        <table class="summary-box">
            <tr>
                <td style="font-weight: bold; width: 18%;">Total Siswa: {{ count($placements) }}</td>
                <td class="badge-hadir">Hadir: {{ $totHadir }}</td>
                <td class="badge-terlambat">Telat: {{ $totTelat }}</td>
                <td class="badge-izin">Izin: {{ $totIzin }}</td>
                <td class="badge-sakit">Sakit: {{ $totSakit }}</td>
                <td style="color: #0284c7; font-weight: bold;">Libur: {{ $totLiburShift }}</td>
                <td class="badge-alpha">Alpha: {{ $totAlpha }}</td>
            </tr>
        </table>
    @else
        <!-- Mode Rekap Rentang (Mingguan / Bulanan / Kustom) -->
        <table class="table-data">
            <thead>
                <tr>
                    <th style="width: 3.5%;">No</th>
                    <th style="width: 20%;">Nama Siswa</th>
                    <th style="width: 8.5%;">NIS</th>
                    <th style="width: 10%;">Kelas</th>
                    <th style="width: 16%;">Tempat DUDI</th>
                    <th style="width: 6%;">Hadir</th>
                    <th style="width: 6%;">Telat</th>
                    <th style="width: 6%;">Izin</th>
                    <th style="width: 6%;">Sakit</th>
                    <th style="width: 6%;">Alpa</th>
                    <th style="width: 5%;">Off</th>
                    <th style="width: 7%;">% Hadir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($placements as $index => $p)
                    @php
                        $hadirCount = 0;
                        $telatCount = 0;
                        $izinCount = 0;
                        $sakitCount = 0;
                        $alpaCount = 0;
                        $liburShiftCount = 0;
                        $hariKerjaEfektif = 0;
                        $todayStr = now()->toDateString();

                        foreach ($dates as $d) {
                            $presensi = $presensiData[$p->id][$d] ?? null;
                            $leave = $leavesByPlacementAndDate[$p->id][$d] ?? null;

                            // Catat log kehadiran fisik / shift off jika ada datanya
                            if ($presensi) {
                                if ($presensi->status_masuk === 'libur_shift') {
                                    $liburShiftCount++;
                                } elseif ($presensi->status_masuk === 'tepat_waktu') {
                                    $hadirCount++;
                                } elseif ($presensi->status_masuk === 'terlambat') {
                                    $telatCount++;
                                }
                            }

                            // 1. Abaikan tanggal yang belum berjalan (masa depan)
                            if ($d > $todayStr) {
                                continue;
                            }

                            // 2. Abaikan tanggal sebelum/sesudah masa penempatan aktif murid
                            if ($p->tanggal_mulai && $d < $p->tanggal_mulai) {
                                continue;
                            }
                            if ($p->tanggal_selesai && $d > $p->tanggal_selesai) {
                                continue;
                            }

                            // 3. Libur nasional / kalender pendidikan
                            $isHoliday = isset($holidayMap[$d]);

                            // 4. Libur rutin / mingguan DUDI (Sabtu & Minggu jika DUDI 5 hari kerja)
                            $isPlacementOff = $p->isPlacementHoliday($d);

                            // 5. Libur shift khusus
                            $isLiburShift = ($presensi && $presensi->status_masuk === 'libur_shift');

                            // Hari kerja efektif adalah hari di mana murid diwajibkan hadir
                            $isHariKerja = (!$isHoliday && !$isPlacementOff && !$isLiburShift);

                            if ($isHariKerja) {
                                $hariKerjaEfektif++;

                                if ($leave) {
                                    if (strtolower($leave) === 'izin') {
                                        $izinCount++;
                                    } else {
                                        $sakitCount++;
                                    }
                                } elseif (!$presensi || $presensi->status_masuk === 'alpha') {
                                    $alpaCount++;
                                }
                            } else {
                                // Jika izin/sakit tercatat di luar hari kerja reguler
                                if ($leave && !$presensi) {
                                    if (strtolower($leave) === 'izin') {
                                        $izinCount++;
                                    } else {
                                        $sakitCount++;
                                    }
                                }
                            }
                        }

                        // Kehadiran yang diakui sah: Hadir fisik (Tepat Waktu + Telat) + Izin resmi + Sakit resmi
                        $kehadiranSah = $hadirCount + $telatCount + $izinCount + $sakitCount;
                        $pembagi = max($hariKerjaEfektif, $kehadiranSah + $alpaCount);
                        $persen = $pembagi > 0 ? round(($kehadiranSah / $pembagi) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-left"><strong>{{ $p->murid?->nama ?? 'Siswa Terhapus' }}</strong></td>
                        <td class="text-center">{{ $p->murid?->nis ?? '-' }}</td>
                        <td class="text-center">{{ $p->murid?->kelas?->nama ?? '-' }}</td>
                        <td class="text-left">{{ $p->dudi?->nama ?? '-' }}</td>
                        <td class="text-center badge-hadir">{{ $hadirCount }}</td>
                        <td class="text-center badge-terlambat">{{ $telatCount }}</td>
                        <td class="text-center badge-izin">{{ $izinCount }}</td>
                        <td class="text-center badge-sakit">{{ $sakitCount }}</td>
                        <td class="text-center badge-alpha">{{ $alpaCount }}</td>
                        <td class="text-center" style="color: #0284c7; font-weight: bold;">{{ $liburShiftCount }}</td>
                        <td class="text-center fw-bold">{{ $persen }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center" style="padding: 15px;">Tidak ada data penempatan PKL aktif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="font-size: 8px; color: #555; margin-top: -10px; margin-bottom: 12px; font-style: italic;">
            * Catatan: % Hadir dihitung dari kehadiran sah (Hadir + Terlambat + Izin/Sakit resmi) dibagi total hari kerja efektif siswa. Hanya Alpa (tidak presensi tanpa keterangan) yang mengurangi persentase kehadiran.
        </div>
    @endif

    <!-- Tanda Tangan -->
    <table class="footer-sign">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Sekolah
                <div style="height: 50px;"></div>
                <strong>{{ $branding['kepala_sekolah'] }}</strong><br>
                NIP. {{ $branding['nip_kepala_sekolah'] }}
            </td>
            <td>
                {{ $branding['kota_sekolah'] }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Koordinator Pokja PKL
                <div style="height: 50px;"></div>
                <strong>{{ auth()->user()->name }}</strong><br>
                NIP. -
            </td>
        </tr>
    </table>

</body>
</html>
