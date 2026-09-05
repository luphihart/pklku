<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Perjalanan Dinas - {{ $nama }}</title>
    <style>
        @page {
            margin: 12mm 15mm 12mm 15mm;
            size: a4 portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .page-frame {
            border: 1.5px solid #111;
            padding: 14px 18px;
            box-sizing: border-box;
        }
        .title {
            text-align: center;
            font-size: 12.5pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 0.5px;
            margin-top: 2px;
            margin-bottom: 16px;
            text-transform: uppercase;
        }
        .table-meta {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }
        .table-meta td {
            padding: 3.5px 0;
            vertical-align: top;
        }
        .label-col {
            width: 240px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111;
            letter-spacing: 0.2px;
        }
        .colon-col {
            width: 16px;
            font-weight: bold;
            text-align: center;
        }
        .value-col {
            width: auto;
            color: #111;
        }

        /* Area Laporan Pelaksanaan Kegiatan */
        .laporan-wrapper {
            margin-top: 2px;
            width: 100%;
        }
        .laporan-text {
            font-size: 10pt;
            line-height: 22px;
            text-align: justify;
            color: #111;
            margin-bottom: 0;
            white-space: pre-line;
            word-break: break-word;
        }
        .ruled-line {
            border-bottom: 1px solid #777;
            height: 22px;
            width: 100%;
            display: block;
        }

        /* Blok Tanda Tangan */
        .signature-section {
            width: 100%;
            margin-top: 16px;
            font-size: 10pt;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            vertical-align: top;
        }
        .sig-city-date {
            margin-bottom: 3px;
            text-transform: uppercase;
            font-weight: normal;
        }
        .sig-title {
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .sig-space {
            height: 48px;
        }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .sig-nip {
            font-size: 9.5pt;
            margin-top: 2px;
        }

        /* Halaman Kedua: Dokumentasi Foto (Opsional) */
        .page-break {
            page-break-before: always;
        }
        .doc-frame {
            border: 1.5px solid #111;
            padding: 14px 18px;
            box-sizing: border-box;
        }
        .doc-title {
            text-align: center;
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin-top: 2px;
            margin-bottom: 14px;
        }
        .doc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9.5pt;
        }
        .doc-table td {
            padding: 3.5px 6px;
            border: 1px solid #ccc;
        }
        .doc-photo-box {
            text-align: center;
            margin: 12px 0;
            padding: 8px;
            border: 1px dashed #999;
            background: #fafafa;
        }
    </style>
</head>
<body>

    <!-- HALAMAN 1: BLANGKO LAPORAN PERJALANAN DINAS -->
    <div class="page-frame">
        <div class="title">LAPORAN PERJALANAN DINAS</div>

        <table class="table-meta">
            <tr>
                <td class="label-col">NAMA</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $nama }}</td>
            </tr>
            <tr>
                <td class="label-col">NIP</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $nip }}</td>
            </tr>
            <tr>
                <td class="label-col">PANGKAT / GOLONGAN</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $pangkatGolongan ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">TEMPAT / TUJUAN</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $tempatTujuan }}</td>
            </tr>
            <tr>
                <td class="label-col">LAMA PERJALANAN</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $lamaPerjalanan }}</td>
            </tr>
            <tr>
                <td class="label-col">LAPORAN PELAKSANAAN KEGIATAN</td>
                <td class="colon-col">:</td>
                <td class="value-col"></td>
            </tr>
        </table>

        <!-- Garis & Isi Laporan Kegiatan -->
        <div class="laporan-wrapper">
            <div class="laporan-text">{{ $laporanKegiatan }}</div>

            @php
                // Hitung estimasi baris teks agar pas 1 halaman tanpa overflow
                $wrappedLines = explode("\n", wordwrap($laporanKegiatan, 80, "\n", true));
                $estLines = count($wrappedLines);
                // Total baris aman agar halaman 1 pas tanpa melompat ke halaman 2
                $maxAvailableLines = 15;
                $blankLinesCount = max(2, $maxAvailableLines - $estLines);
            @endphp

            @for($i = 0; $i < $blankLinesCount; $i++)
                <div class="ruled-line"></div>
            @endfor
        </div>

        <!-- Tanda Tangan (Sesuai Format Foto: Pati, ... Yang Melaksanakan Tugas) -->
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td style="width: 52%;"></td>
                    <td style="width: 48%; text-align: left; padding-left: 20px;">
                        <div class="sig-city-date">{{ $kota }}, {{ $tanggalFormatted }}</div>
                        <div class="sig-title">YANG MELAKSANAKAN TUGAS</div>
                        <div class="sig-space"></div>
                        <div class="sig-name">{{ $nama }}</div>
                        <div class="sig-nip">NIP. {{ $nip }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- HALAMAN 2: LAMPIRAN DOKUMENTASI FOTO (JIKA DIPILIH & TERSEDIA) -->
    @if($lampirkanFoto && $fotoBase64)
        <div class="page-break"></div>
        <div class="doc-frame">
            <div class="doc-title">LAMPIRAN DOKUMENTASI FOTO KEGIATAN KUNJUNGAN / MONITORING</div>

            <table class="doc-table">
                <tr>
                    <td style="width: 25%; font-weight: bold; background: #f3f4f6;">Mitra DUDI</td>
                    <td>{{ $tempatTujuan }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background: #f3f4f6;">Tanggal Kunjungan</td>
                    <td>{{ $tanggalFormatted }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background: #f3f4f6;">Jenis Kunjungan</td>
                    <td>{{ $kunjungan->jenis_kunjungan ?? 'Monitoring Berkala' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background: #f3f4f6;">Guru Pembimbing</td>
                    <td>{{ $nama }} (NIP. {{ $nip }})</td>
                </tr>
            </table>

            <div class="doc-photo-box">
                <img src="{{ $fotoBase64 }}" style="max-width: 480px; max-height: 400px; object-fit: contain; border-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
                <div style="font-size: 9pt; color: #555; margin-top: 6px; font-style: italic;">
                    Foto Bukti Dokumentasi Kegiatan Kunjungan di {{ $kunjungan->penempatanPkl?->dudi?->nama ?? 'Mitra DUDI' }}
                </div>
            </div>

            <div style="margin-top: 25px; text-align: right; padding-right: 30px; font-size: 10pt;">
                <div>{{ $kota }}, {{ $tanggalFormatted }}</div>
                <div style="font-weight: bold; margin-top: 3px;">Guru Pembimbing / Pelaksana,</div>
                <div style="height: 48px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ $nama }}</div>
                <div>NIP. {{ $nip }}</div>
            </div>
        </div>
    @endif

</body>
</html>
