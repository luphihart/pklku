<!DOCTYPE html>
<html>
<head>
    <title>Lembar Penilaian PKL - {{ $placement->murid->nama }}</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
        }
        * {
            font-family: 'Arial', 'Helvetica', sans-serif;
        }
        body {
            font-size: 11px;
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
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #111;
        }
        .header h3 {
            margin: 3px 0 0 0;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: bold;
            color: #222;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 9.5px;
            color: #555;
        }
        .title {
            text-align: center;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 12.5px;
            margin: 12px 0;
            letter-spacing: 0.5px;
            text-decoration: underline;
        }
        .student-info {
            width: 100%;
            margin-bottom: 12px;
            font-size: 11px;
        }
        .student-info td {
            padding: 2.5px 0;
            vertical-align: top;
        }
        .student-info td.label {
            width: 20%;
            color: #444;
        }
        .student-info td.separator {
            width: 3%;
        }
        .student-info td.value {
            font-weight: bold;
            color: #111;
        }
        .table-marks {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10px;
        }
        .table-marks th, .table-marks td {
            border: 1px solid #444;
            padding: 5px 7px;
            text-align: left;
        }
        .table-marks th {
            background-color: #e8e8e8;
            text-transform: uppercase;
            font-size: 9.5px;
            text-align: center;
            letter-spacing: 0.2px;
        }
        .summary-box {
            border: 1.5px solid #333;
            padding: 8px 12px;
            margin-bottom: 12px;
            background-color: #fafafa;
        }
        .summary-box table {
            width: 100%;
            font-size: 11px;
        }
        .summary-box td {
            padding: 3px 0;
        }
        .signatures {
            width: 100%;
            margin-top: 15px;
            font-size: 11px;
        }
        .signatures td {
            text-align: center;
            vertical-align: top;
        }
        .signature-space {
            height: 50px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
        tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        thead {
            display: table-header-group;
        }
        .signatures, .summary-box {
            page-break-inside: avoid;
            break-inside: avoid;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Surat -->
    <div class="header">
        <h2>{{ $branding['nama_sekolah'] }}</h2>
        <h3>Lembar Hasil Penilaian Praktik Kerja Lapangan</h3>
        <p>{{ $branding['alamat_sekolah'] }}</p>
    </div>

    <!-- Document Title -->
    <div class="title">
        Lembar Hasil Penilaian Praktik Kerja Lapangan (PKL)
    </div>

    <!-- Student Profile -->
    <table class="student-info">
        <tr>
            <td class="label">Nama Murid</td>
            <td class="separator">:</td>
            <td class="value">{{ $placement->murid->nama }}</td>
        </tr>
        <tr>
            <td class="label">NIS</td>
            <td class="separator">:</td>
            <td class="value">{{ $placement->murid->nis }}</td>
        </tr>
        <tr>
            <td class="label">Kelas / Jurusan</td>
            <td class="separator">:</td>
            <td class="value">{{ $placement->murid->kelas ? $placement->murid->kelas->nama : '-' }} / {{ ($placement->murid->kelas && $placement->murid->kelas->jurusan) ? $placement->murid->kelas->jurusan->nama : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tempat PKL (DUDI)</td>
            <td class="separator">:</td>
            <td class="value">{{ $placement->dudi->nama }}</td>
        </tr>
        <tr>
            <td class="label">Periode PKL</td>
            <td class="separator">:</td>
            <td>{{ \Carbon\Carbon::parse($placement->tanggal_mulai)->translatedFormat('l, d F Y') }} s/d {{ \Carbon\Carbon::parse($placement->tanggal_selesai)->translatedFormat('l, d F Y') }}</td>
        </tr>
    </table>

    <!-- Detailed Scores (Single Unified Table matching the sheet format exactly) -->
    <div style="margin-bottom: 8px;">
        <table class="table-marks">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">No.</th>
                    <th style="width: 49%;">Tujuan Pembelajaran / Indikator</th>
                    <th style="width: 10%; text-align: center;">Nilai</th>
                    <th style="width: 36%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $tps = \App\Modules\Penilaian\Models\TujuanPembelajaran::with(['indikators' => function($q) {
                        $q->orderBy('nomor_urut', 'asc');
                    }])->orderBy('nomor', 'asc')->get();
                @endphp
                @foreach($tps as $tp)
                    <!-- TP Row (Header) -->
                    @php
                        $tpComment = $placement->penilaianPkl && isset($placement->penilaianPkl->keterangan_tp_json[$tp->id]) ? $placement->penilaianPkl->keterangan_tp_json[$tp->id] : '';
                        $hasDivider = $tp->indikators->contains(fn($ind) => $ind->nomor_urut === '3.7');
                        $rowspan = 1 + count($tp->indikators) + ($hasDivider ? 1 : 0);
                    @endphp
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td style="text-align: center;">{{ $tp->nomor }}</td>
                        <td>{{ $tp->nama }}</td>
                        <td></td>
                        <td rowspan="{{ $rowspan }}" style="vertical-align: top; font-size: 10px;">
                            @if($tp->nomor == '2' && empty($tpComment))
                                <span style="font-size: 9px; font-weight: normal; font-style: italic; color: #555;">Bidang yang dikuasai siswa:</span>
                            @else
                                {{ $tpComment }}
                            @endif
                        </td>
                    </tr>

                    @php
                        $pushedDivider = false;
                    @endphp
                    @foreach($tp->indikators as $ind)
                        @if($ind->nomor_urut == '3.7' && !$pushedDivider)
                            <tr style="background-color: #e0e0e0; font-weight: bold; text-align: center; font-size: 9.5px;">
                                <td colspan="3">Point 3.7 kebawah, diisi oleh sekolah (Guru Pembimbing)</td>
                            </tr>
                            @php $pushedDivider = true; @endphp
                        @endif

                        @php
                            $isGuru = $ind->tipe === 'guru';
                            $score = '-';

                            if ($placement->penilaianPkl) {
                                if ($isGuru && isset($placement->penilaianPkl->nilai_guru_json[$ind->id])) {
                                    $item = $placement->penilaianPkl->nilai_guru_json[$ind->id];
                                    $score = is_array($item) ? ($item['nilai'] ?? '-') : $item;
                                } elseif (!$isGuru && isset($placement->penilaianPkl->nilai_industri_json[$ind->id])) {
                                    $item = $placement->penilaianPkl->nilai_industri_json[$ind->id];
                                    $score = is_array($item) ? ($item['nilai'] ?? '-') : $item;
                                }
                            }
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold; color: #555;">{{ $ind->nomor_urut }}</td>
                            <td style="padding-left: 15px;">
                                <span style="font-weight: bold;">{{ $ind->nama }}</span>
                                @if($ind->deskripsi)
                                    <br><span style="font-size: 9px; color: #666; font-style: italic; font-weight: normal;">{{ $ind->deskripsi }}</span>
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: bold;">{{ $score }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
        
        <div style="font-size: 9.5px; margin-top: 4px; color: #555;">
            <strong>Petunjuk:</strong> {{ $branding['footer_rapor'] }}
        </div>
    </div>

    <!-- Combined Calculation Box -->
    <div class="summary-box">
        <table>
            <tr>
                <td style="width: 45%;"><strong>Rata-rata Evaluasi Sekolah (R1)</strong></td>
                <td style="width: 3%;">:</td>
                <td style="font-weight: bold;">{{ number_format($placement->penilaianPkl->rata_nilai_guru, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Rata-rata Evaluasi Industri/DUDI (R2)</strong></td>
                <td>:</td>
                <td style="font-weight: bold;">{{ number_format($placement->penilaianPkl->rata_nilai_industri, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Nilai Final Rapor PKL</strong></td>
                <td>:</td>
                <td style="font-size: 13px; font-weight: bold; color: #1a237e;">
                    {{ number_format($placement->penilaianPkl->nilai_akhir, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Catatan Selama PKL -->
    <div style="margin-bottom: 8px;">
        <h4 style="margin: 0 0 4px 0; font-size: 11px; text-transform: uppercase; font-weight: bold;">Catatan Selama Praktik Kerja Lapangan (PKL)</h4>
        <div style="border: 1px solid #444; padding: 6px 10px; min-height: 25px; font-style: italic; font-size: 10px; background-color: #fafafa; color: #333; line-height: 1.3;">
            @if($placement->penilaianPkl && $placement->penilaianPkl->catatan)
                "{{ $placement->penilaianPkl->catatan }}"
            @else
                - Tidak ada catatan khusus selama pelaksanaan PKL -
            @endif
        </div>
    </div>

    <!-- Attendance & Signatures Layout -->
    @php
        $city = $branding['kota_sekolah'] ?? 'Pati';
        $placementId = $placement->id;
        
        // Count Sick Days
        $sakitCount = \App\Modules\Presensi\Models\IzinSakit::where('penempatan_pkl_id', $placementId)
            ->where('tipe', 'sakit')
            ->where('status_approval', 'approved')
            ->get()
            ->sum(function($iz) {
                return \Carbon\Carbon::parse($iz->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($iz->tanggal_selesai)) + 1;
            });

        // Count Izin Days
        $izinCount = \App\Modules\Presensi\Models\IzinSakit::where('penempatan_pkl_id', $placementId)
            ->where('tipe', 'izin')
            ->where('status_approval', 'approved')
            ->get()
            ->sum(function($iz) {
                return \Carbon\Carbon::parse($iz->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($iz->tanggal_selesai)) + 1;
            });

        // Count Absent Days (Alfa)
        $hariKerjaSetting = $placement->dudi->hari_kerja ?? 'Senin,Selasa,Rabu,Kamis,Jumat';
        $allowedDaysMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $workingDays = array_map('trim', explode(',', $hariKerjaSetting));

        $startDate = \Carbon\Carbon::parse($placement->tanggal_mulai);
        $endDate = \Carbon\Carbon::parse($placement->tanggal_selesai);
        $today = \Carbon\Carbon::today();
        $lastDateToCount = $endDate->greaterThan($today) ? $today : $endDate;
        
        $totalWorkingDays = 0;
        $allWorkingDates = [];
        $current = $startDate->copy();
        while ($current->lessThanOrEqualTo($lastDateToCount)) {
            $dayNameIndo = $allowedDaysMap[$current->format('l')] ?? '';
            if (in_array($dayNameIndo, $workingDays)) {
                $totalWorkingDays++;
                $allWorkingDates[] = $current->toDateString();
            }
            $current->addDay();
        }

        $attendedDates = \App\Modules\Presensi\Models\Presensi::where('penempatan_pkl_id', $placementId)
            ->pluck('tanggal')
            ->toArray();

        $approvedLeaveDates = [];
        $approvedLeaves = \App\Modules\Presensi\Models\IzinSakit::where('penempatan_pkl_id', $placementId)
            ->where('status_approval', 'approved')
            ->get();
            
        foreach ($approvedLeaves as $iz) {
            $start = \Carbon\Carbon::parse($iz->tanggal_mulai);
            $end = \Carbon\Carbon::parse($iz->tanggal_selesai);
            $curr = $start->copy();
            while ($curr->lessThanOrEqualTo($end)) {
                $approvedLeaveDates[] = $curr->toDateString();
                $curr->addDay();
            }
        }

        $alfaCount = 0;
        foreach ($allWorkingDates as $date) {
            if (!in_array($date, $attendedDates) && !in_array($date, $approvedLeaveDates)) {
                $alfaCount++;
            }
        }
    @endphp

    <table class="signatures" style="width: 100%; border: 0; margin-top: 15px;">
        <tr>
            <!-- Top Row: Kehadiran on Left, Date on Right -->
            <td style="width: 50%; vertical-align: top; padding-right: 30px;">
                <table style="border-collapse: collapse; border: 1px solid #000; width: 100%; font-size: 11px;">
                    <tr>
                        <td colspan="4" style="border: 1px solid #000; padding: 4px 6px; font-weight: bold; text-align: left;">Kehadiran</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; border-right: 1px solid #000; padding: 4px 6px; width: 45%; text-align: left;">Sakit</td>
                        <td style="border: 1px solid #000; border-left: none; border-right: none; padding: 4px 2px; width: 5%; text-align: center;">:</td>
                        <td style="border: 1px solid #000; border-left: none; border-right: none; padding: 4px 2px; width: 25%; text-align: center; font-style: italic; font-weight: bold;">{{ $sakitCount }}</td>
                        <td style="border: 1px solid #000; border-left: none; padding: 4px 6px; width: 25%; text-align: left;">Hari</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; border-right: 1px solid #000; padding: 4px 6px; text-align: left;">Hari Ijin</td>
                        <td style="border: 1px solid #000; border-left: none; border-right: none; padding: 4px 2px; text-align: center;">:</td>
                        <td style="border: 1px solid #000; border-left: none; border-right: none; padding: 4px 2px; text-align: center; font-style: italic; font-weight: bold;">{{ $izinCount }}</td>
                        <td style="border: 1px solid #000; border-left: none; padding: 4px 6px; text-align: left;">Hari</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; border-right: 1px solid #000; padding: 4px 6px; text-align: left;">Hari Tanpa Keterangan</td>
                        <td style="border: 1px solid #000; border-left: none; border-right: none; padding: 4px 2px; text-align: center;">:</td>
                        <td style="border: 1px solid #000; border-left: none; border-right: none; padding: 4px 2px; text-align: center; font-style: italic; font-weight: bold;">{{ $alfaCount }}</td>
                        <td style="border: 1px solid #000; border-left: none; padding: 4px 6px; text-align: left;">Hari</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <!-- Empty space in top row right column to align with Kehadiran table -->
            </td>
        </tr>
        <tr>
            <!-- Spacer row -->
            <td style="height: 15px;" colspan="2"></td>
        </tr>
        <tr>
            <!-- Guru Pembimbing and Pembimbing Industri Titles (aligned vertically) -->
            <td style="text-align: center; vertical-align: bottom; padding-right: 30px; font-size: 11px;">
                Guru Pembimbing
            </td>
            <td style="text-align: center; vertical-align: bottom; font-size: 11px;">
                {{ $city }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Pembimbing Industri
            </td>
        </tr>
        <tr>
            <!-- Signature spaces -->
            <td style="height: 50px;"></td>
            <td style="height: 50px;"></td>
        </tr>
        <tr>
            <!-- Names and NIPs -->
            <td style="text-align: center; vertical-align: top; padding-right: 30px; font-size: 11px;">
                <div class="signature-name">{{ $placement->guru ? $placement->guru->nama : '_______________________' }}</div>
                @if($placement->guru && $placement->guru->nip)
                    <div style="font-size: 9.5px; margin-top: 2px;">NIP. {{ $placement->guru->nip }}</div>
                @endif
            </td>
            <td style="text-align: center; vertical-align: top; font-size: 11px;">
                <div class="signature-name">
                    {{ $placement->pembimbingIndustri ? $placement->pembimbingIndustri->nama : ($placement->dudi->pic_nama ?? '_______________________') }}
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
