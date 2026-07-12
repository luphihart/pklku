<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kunjungan Pembimbing - PKLku</title>
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
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        .table-data th, .table-data td {
            border: 1px solid #444;
            padding: 6px 8px;
            vertical-align: top;
        }
        .table-data th {
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .table-data td.center {
            text-align: center;
        }
        .badge-type {
            font-size: 9px;
            font-weight: bold;
            color: #4f46e5;
        }
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
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $branding['nama_sekolah'] }}</h2>
        <h3>Laporan Rekapitulasi Kunjungan Pembimbing PKL</h3>
        <p>{{ $branding['alamat_sekolah'] }}</p>
    </div>

    <div class="title">Laporan Kunjungan Pembimbing ke DUDI</div>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 23%;">Mitra DUDI & Jenis</th>
                <th style="width: 20%;">Guru Pembimbing & Siswa</th>
                <th style="width: 30%;">Catatan Kunjungan</th>
                <th style="width: 10%;" class="center">Bukti Foto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kunjungans as $index => $k)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}</td>
                    <td>
                        <strong>{{ $k->penempatanPkl->dudi->nama }}</strong><br>
                        <span class="badge-type">{{ $k->jenis_kunjungan ?? 'Monitoring Berkala' }}</span>
                    </td>
                    <td>
                        <strong>{{ $k->penempatanPkl->guru->nama }}</strong><br>
                        <small style="color: #666;">Siswa: {{ $k->penempatanPkl->murid->nama }}</small>
                    </td>
                    <td>{{ $k->deskripsi_kunjungan }}</td>
                    <td class="center">
                        @if($k->foto_kunjungan && file_exists(public_path('storage/kunjungan/' . $k->foto_kunjungan)))
                            <img src="{{ public_path('storage/kunjungan/' . $k->foto_kunjungan) }}" style="width: 45px; height: 45px; object-fit: cover;">
                        @else
                            <span style="color: #888; font-size: 9px;">Tidak Ada Foto</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">Belum ada riwayat catatan kunjungan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer">
        <tr>
            <td></td>
            <td>
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}<br>
                Mengetahui,<br>
                Kepala Sekolah
                <div style="margin-top: 65px;">
                    <strong>{{ $branding['kepala_sekolah'] }}</strong><br>
                    <span style="font-size: 10px;">NIP. {{ $branding['nip_kepala_sekolah'] }}</span>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
