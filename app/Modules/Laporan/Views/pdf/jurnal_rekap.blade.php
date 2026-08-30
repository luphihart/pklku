<!DOCTYPE html>
<html>
<head>
    <title>Laporan Rekapitulasi Jurnal Kegiatan PKL</title>
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
            vertical-align: top;
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
        .status-disetujui { color: #155724; font-weight: bold; }
        .status-ditolak { color: #721c24; font-weight: bold; }
        .status-revisi { color: #856404; font-weight: bold; }
        .status-pending { color: #666; font-weight: bold; }

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
        <div class="main-title">LAPORAN REKAPITULASI JURNAL KEGIATAN PKL</div>
        <div class="sub-title">{{ $label }}</div>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 18%;">Nama Siswa</th>
                <th style="width: 11%;">Kelas</th>
                <th style="width: 17%;">Tempat DUDI</th>
                <th style="width: 28%;">Ringkasan Aktivitas / Pekerjaan</th>
                <th style="width: 12%;">Status Verifikasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($journals as $index => $j)
                @php
                    $murid = $j->penempatanPkl?->murid;
                    $dudi = $j->penempatanPkl?->dudi;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($j->tanggal)->format('d/m/Y') }}</td>
                    <td class="text-left"><strong>{{ $murid?->nama ?? 'Siswa Terhapus' }}</strong></td>
                    <td class="text-center">{{ $murid?->kelas?->nama ?? '-' }}</td>
                    <td class="text-left">{{ $dudi?->nama ?? '-' }}</td>
                    <td class="text-left">{{ $j->deskripsi_aktivitas }}</td>
                    <td class="text-center">
                        @if($j->status_verifikasi === 'disetujui')
                            <span class="status-disetujui">Disetujui</span>
                        @elseif($j->status_verifikasi === 'ditolak')
                            <span class="status-ditolak">Ditolak</span>
                        @elseif($j->status_verifikasi === 'revisi')
                            <span class="status-revisi">Revisi</span>
                        @else
                            <span class="status-pending">Menunggu</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px;">Tidak ada catatan jurnal kegiatan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Ringkasan Jurnal -->
    <div style="font-size: 9.5px; margin-bottom: 15px; color: #444;">
        <strong>Total Catatan Jurnal:</strong> {{ count($journals) }} aktivitas | 
        <strong>Disetujui:</strong> {{ $journals->where('status_verifikasi', 'disetujui')->count() }} | 
        <strong>Menunggu / Revisi:</strong> {{ $journals->whereIn('status_verifikasi', ['pending', 'revisi'])->count() }}
    </div>

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
