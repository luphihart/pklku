<!DOCTYPE html>
<html>
<head>
    <title>Laporan Jurnal Kegiatan PKL - {{ $placement->murid->nama }}</title>
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
            font-weight: 600;
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
            margin-bottom: 20px;
            font-size: 10px;
        }
        .table-data th, .table-data td {
            border: 1px solid #444;
            padding: 6px 8px;
        }
        .table-data th {
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .status-disetujui { color: #155724; font-weight: 600; }
        .status-ditolak { color: #721c24; font-weight: 600; }
        .status-revisi { color: #856404; font-weight: 600; }
        .status-pending { color: #666; font-weight: 600; }
        .footer {
            width: 100%;
            margin-top: 35px;
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
        <h3>Laporan Jurnal Kegiatan Praktek Kerja Lapangan</h3>
        <p>{{ $branding['alamat_sekolah'] }}</p>
    </div>

    <div class="title">Laporan Jurnal Harian Murid</div>

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
            <td class="label">Tempat DUDI</td>
            <td class="separator">:</td>
            <td class="value">{{ $placement->dudi->nama }}</td>
        </tr>
        <tr>
            <td class="label">Guru Pembimbing</td>
            <td class="separator">:</td>
            <td class="value">{{ $placement->guru->nama }}</td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 18%;">Tanggal</th>
                <th>Aktivitas Kegiatan / Pekerjaan</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 18%;">Catatan Guru</th>
            </tr>
        </thead>
        <tbody>
            @forelse($journals as $index => $j)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('l, d F Y') }}</td>
                    <td>{{ $j->deskripsi_aktivitas }}</td>
                    <td style="text-align: center; text-transform: capitalize;">
                        @if($j->status_verifikasi === 'disetujui')
                            <span class="status-disetujui">Disetujui</span>
                        @elseif($j->status_verifikasi === 'ditolak')
                            <span class="status-ditolak">Ditolak</span>
                        @elseif($j->status_verifikasi === 'revisi')
                            <span class="status-revisi">Revisi</span>
                        @else
                            <span class="status-pending">Pending</span>
                        @endif
                    </td>
                    <td>{{ $j->catatan_verifikasi ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Belum ada entri jurnal kegiatan.</td>
                </tr>
            @endforelse
        </tbody>
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
                    <strong>{{ $placement->guru->nama }}</strong><br>
                    <span style="font-size: 10px;">NIP. {{ $placement->guru->nip ?? '-' }}</span>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
