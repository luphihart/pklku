<table>
    <thead>
        <tr>
            <th colspan="8" style="font-weight: bold; font-size: 14px; text-align: center;">LAPORAN KEHADIRAN REKAPITULASI SISWA PKL</th>
        </tr>
        <tr>
            <th colspan="8" style="font-weight: bold; text-align: center;">{{ $label }}</th>
        </tr>
        <tr>
            <th colspan="7"></th>
        </tr>
        <tr style="background-color: #f2f2f2; font-weight: bold;">
            <th style="border: 1px solid #000;">No</th>
            <th style="border: 1px solid #000;">Nama Siswa</th>
            <th style="border: 1px solid #000;">NIS</th>
            <th style="border: 1px solid #000;">Kelas</th>
            <th style="border: 1px solid #000;">Tempat DUDI</th>
            <th style="border: 1px solid #000;">Jam Masuk</th>
            <th style="border: 1px solid #000;">Jam Pulang</th>
            <th style="border: 1px solid #000;">Status Masuk</th>
        </tr>
    </thead>
    <tbody>
        @forelse($presensis as $index => $p)
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000;">{{ $p->penempatanPkl->murid->nama }}</td>
                <td style="border: 1px solid #000; text-align: left;">{{ $p->penempatanPkl->murid->nis }}</td>
                <td style="border: 1px solid #000;">{{ $p->penempatanPkl->murid->kelas->nama }}</td>
                <td style="border: 1px solid #000;">{{ $p->penempatanPkl->dudi->nama }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $p->jam_masuk ? substr($p->jam_masuk, 0, 5) : '-' }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $p->jam_pulang ? substr($p->jam_pulang, 0, 5) : '-' }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $p->status_masuk === 'tepat_waktu' ? 'Hadir' : 'Terlambat' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="border: 1px solid #000; text-align: center;">Tidak ada data presensi.</td>
            </tr>
        @endforelse
    </tbody>
</table>
