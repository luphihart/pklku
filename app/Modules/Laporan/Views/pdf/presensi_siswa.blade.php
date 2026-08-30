<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kehadiran PKL - {{ $placement->murid->nama }}</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #222;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 18px;
            border-bottom: 3px double #333;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #111;
        }
        .header h3 {
            margin: 4px 0 0 0;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: bold;
            color: #333;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 9px;
            font-style: italic;
            color: #555;
        }
        .title {
            text-align: center;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 13px;
            margin: 20px 0 18px 0;
            letter-spacing: 0.5px;
            text-decoration: underline;
        }
        .student-info {
            width: 100%;
            margin-bottom: 18px;
            font-size: 11px;
        }
        .student-info td {
            padding: 3px 0;
            vertical-align: top;
        }
        .student-info td.label {
            width: 22%;
            color: #444;
        }
        .student-info td.separator {
            width: 3%;
        }
        .student-info td.value {
            font-weight: bold;
            color: #111;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .table-data th, .table-data td {
            border: 1px solid #444;
            padding: 5px 7px;
        }
        .table-data th {
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .table-data td {
            text-align: center;
        }
        .badge-hadir {
            color: #155724;
            font-weight: bold;
        }
        .badge-terlambat {
            color: #856404;
            font-weight: bold;
        }
        .badge-izin {
            color: #0c5460;
            font-weight: bold;
        }
        .badge-sakit {
            color: #721c24;
            font-weight: bold;
        }
        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        .summary-box td {
            border: 1px solid #666;
            padding: 6px 10px;
            text-align: center;
        }
        .footer {
            width: 100%;
            margin-top: 30px;
            font-size: 11px;
        }
        .footer td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            display: inline-block;
            width: 180px;
            margin-top: 60px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $branding['nama_sekolah'] }}</h2>
        <h3>Rekapitulasi Presensi Siswa Praktek Kerja Lapangan</h3>
        <p>{{ $branding['alamat_sekolah'] }}</p>
    </div>

    <div class="title">Rekapitulasi Kehadiran Siswa</div>

    <table class="student-info">
        <tr>
            <td class="label">Nama Murid</td>
            <td class="separator">:</td>
            <td class="value">{{ $placement->murid ? $placement->murid->nama : '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIS</td>
            <td class="separator">:</td>
            <td class="value">{{ $placement->murid ? $placement->murid->nis : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kelas / Jurusan</td>
            <td class="separator">:</td>
            <td class="value">{{ ($placement->murid && $placement->murid->kelas) ? $placement->murid->kelas->nama : '-' }} / {{ ($placement->murid && $placement->murid->kelas && $placement->murid->kelas->jurusan) ? $placement->murid->kelas->jurusan->nama : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tempat DUDI</td>
            <td class="separator">:</td>
            <td class="value">{{ $placement->dudi ? $placement->dudi->nama : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Guru Pembimbing</td>
            <td class="separator">:</td>
            <td class="value">{{ $placement->guru ? $placement->guru->nama : '-' }}</td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Tanggal</th>
                <th style="width: 12%;">Jam Masuk</th>
                <th style="width: 12%;">Jam Pulang</th>
                <th style="width: 22%;">Status Kehadiran</th>
                <th style="width: 24%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($presensis as $index => $p)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('l, d F Y') }}</td>
                    <td>{{ $p->jam_masuk ? substr($p->jam_masuk, 0, 5) : '-' }}</td>
                    <td>{{ $p->jam_pulang ? substr($p->jam_pulang, 0, 5) : '-' }}</td>
                    <td>
                        @if($p->type === 'hadir')
                            <span class="badge-hadir">Hadir (Tepat Waktu)</span>
                        @elseif($p->type === 'terlambat')
                            <span class="badge-terlambat">Terlambat</span>
                        @elseif($p->type === 'izin')
                            <span class="badge-izin">Izin (Disetujui)</span>
                        @elseif($p->type === 'sakit')
                            <span class="badge-sakit">Sakit (Disetujui)</span>
                        @else
                            <span>{{ $p->status }}</span>
                        @endif
                    </td>
                    <td style="text-align: left;">{{ $p->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Belum ada riwayat kehadiran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Ringkasan Rekapitulasi -->
    <table class="summary-box">
        <tr style="background-color: #f2f2f2; font-weight: bold;">
            <td style="width: 25%;">Hadir Tepat Waktu</td>
            <td style="width: 25%;">Terlambat</td>
            <td style="width: 25%;">Izin Disetujui</td>
            <td style="width: 25%;">Sakit Disetujui</td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #155724;">{{ $summary['total_hadir'] ?? 0 }} Hari</td>
            <td style="font-weight: bold; color: #856404;">{{ $summary['total_terlambat'] ?? 0 }} Hari</td>
            <td style="font-weight: bold; color: #0c5460;">{{ $summary['total_izin'] ?? 0 }} Hari</td>
            <td style="font-weight: bold; color: #721c24;">{{ $summary['total_sakit'] ?? 0 }} Hari</td>
        </tr>
    </table>

    <table class="footer">
        <tr>
            <td>
                Mengetahui,<br>
                Pembimbing Industri
                <div style="margin-top: 65px;">
                    <strong>{{ $placement->pembimbingIndustri ? $placement->pembimbingIndustri->nama : ($placement->dudi->pic_nama ?? '_______________________') }}</strong>
                </div>
            </td>
            <td>
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}<br>
                Guru Pembimbing,
                <div style="margin-top: 65px;">
                    <strong>{{ $placement->guru ? $placement->guru->nama : '_______________________' }}</strong><br>
                    <span style="font-size: 10px;">NIP. {{ ($placement->guru && $placement->guru->nip) ? $placement->guru->nip : '-' }}</span>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
