@if($filterType === 'harian')
    @php
        $targetDate = $dates[0] ?? now()->toDateString();
    @endphp
    <table>
        <thead>
            <tr>
                <th colspan="10" style="font-weight: bold; font-size: 14px; text-align: center;">LAPORAN KEHADIRAN REKAPITULASI SISWA PKL</th>
            </tr>
            <tr>
                <th colspan="10" style="font-weight: bold; text-align: center;">{{ $label }}</th>
            </tr>
            <tr>
                <th colspan="10"></th>
            </tr>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <th style="border: 1px solid #000; text-align: center; width: 5%;">No</th>
                <th style="border: 1px solid #000; width: 25%;">Nama Siswa</th>
                <th style="border: 1px solid #000; width: 12%;">NIS</th>
                <th style="border: 1px solid #000; width: 12%;">Kelas</th>
                <th style="border: 1px solid #000; width: 20%;">Tempat DUDI</th>
                <th style="border: 1px solid #000; text-align: center; width: 12%;">Jam Masuk</th>
                <th style="border: 1px solid #000; text-align: center; width: 12%;">Jam Pulang</th>
                <th style="border: 1px solid #000; text-align: center; width: 15%;">Status Masuk</th>
                <th style="border: 1px solid #000; text-align: center; width: 15%;">Status Pulang</th>
                <th style="border: 1px solid #000; text-align: center; width: 12%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($placements as $index => $p)
                @php
                    $presensi = isset($presensiData[$p->id][$targetDate]) ? $presensiData[$p->id][$targetDate] : null;
                    $leave = isset($leavesByPlacementAndDate[$p->id][$targetDate]) ? $leavesByPlacementAndDate[$p->id][$targetDate] : null;
                @endphp
                <tr>
                    <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #000;">{{ $p->murid->nama }}</td>
                    <td style="border: 1px solid #000; text-align: left;">{{ $p->murid->nis }}</td>
                    <td style="border: 1px solid #000;">{{ $p->murid->kelas->nama ?? '-' }}</td>
                    <td style="border: 1px solid #000;">{{ $p->dudi->nama ?? '-' }}</td>
                    
                    @if($presensi)
                        <td style="border: 1px solid #000; text-align: center;">{{ $presensi->jam_masuk ? substr($presensi->jam_masuk, 0, 5) : '-' }}</td>
                        <td style="border: 1px solid #000; text-align: center;">{{ $presensi->jam_pulang ? substr($presensi->jam_pulang, 0, 5) : '-' }}</td>
                        <td style="border: 1px solid #000; text-align: center;">{{ $presensi->status_masuk === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat' }}</td>
                        <td style="border: 1px solid #000; text-align: center;">
                            @if($presensi->jam_pulang)
                                {{ $presensi->status_pulang === 'tepat_waktu' ? 'Tepat Waktu' : 'Pulang Cepat' }}
                            @else
                                -
                            @endif
                        </td>
                        <td style="border: 1px solid #000; text-align: center;">-</td>
                    @elseif($leave)
                        <td style="border: 1px solid #000; text-align: center;">-</td>
                        <td style="border: 1px solid #000; text-align: center;">-</td>
                        <td style="border: 1px solid #000; text-align: center;">-</td>
                        <td style="border: 1px solid #000; text-align: center;">-</td>
                        <td style="border: 1px solid #000; text-align: center;">{{ $leave }}</td>
                    @else
                        <td style="border: 1px solid #000; text-align: center;">-</td>
                        <td style="border: 1px solid #000; text-align: center;">-</td>
                        <td style="border: 1px solid #000; text-align: center;">-</td>
                        <td style="border: 1px solid #000; text-align: center;">-</td>
                        <td style="border: 1px solid #000; text-align: center;">-</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="border: 1px solid #000; text-align: center;">Tidak ada data penempatan aktif.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@else
    @php
        $totalCols = 5 + (2 * count($dates));
    @endphp
    <table>
        <thead>
            <tr>
                <th colspan="{{ $totalCols }}" style="font-weight: bold; font-size: 14px; text-align: center;">LAPORAN KEHADIRAN REKAPITULASI SISWA PKL</th>
            </tr>
            <tr>
                <th colspan="{{ $totalCols }}" style="font-weight: bold; text-align: center;">{{ $label }}</th>
            </tr>
            <tr>
                <th colspan="{{ $totalCols }}"></th>
            </tr>
            <tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                <th rowspan="2" style="border: 1px solid #000; vertical-align: middle; width: 5%;">No</th>
                <th rowspan="2" style="border: 1px solid #000; vertical-align: middle; width: 25%;">Nama Siswa</th>
                <th rowspan="2" style="border: 1px solid #000; vertical-align: middle; width: 12%;">NIS</th>
                <th rowspan="2" style="border: 1px solid #000; vertical-align: middle; width: 12%;">Kelas</th>
                <th rowspan="2" style="border: 1px solid #000; vertical-align: middle; width: 20%;">Tempat DUDI</th>
                @foreach($dates as $date)
                    <th colspan="2" style="border: 1px solid #000; text-align: center; width: 28px;">
                        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                    </th>
                @endforeach
            </tr>
            <tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                @foreach($dates as $date)
                    <th style="border: 1px solid #000; text-align: center; width: 14px;">Waktu</th>
                    <th style="border: 1px solid #000; text-align: center; width: 14px;">Keterangan</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($placements as $index => $p)
                <tr>
                    <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #000;">{{ $p->murid->nama }}</td>
                    <td style="border: 1px solid #000; text-align: left;">{{ $p->murid->nis }}</td>
                    <td style="border: 1px solid #000;">{{ $p->murid->kelas->nama ?? '-' }}</td>
                    <td style="border: 1px solid #000;">{{ $p->dudi->nama ?? '-' }}</td>
                    
                    @foreach($dates as $date)
                        @php
                            $presensi = isset($presensiData[$p->id][$date]) ? $presensiData[$p->id][$date] : null;
                            $leave = isset($leavesByPlacementAndDate[$p->id][$date]) ? $leavesByPlacementAndDate[$p->id][$date] : null;
                        @endphp
                        
                        @if($presensi)
                            @php
                                $waktu = substr($presensi->jam_masuk, 0, 5) . ' - ' . ($presensi->jam_pulang ? substr($presensi->jam_pulang, 0, 5) : '-');
                                
                                $statusParts = [];
                                if ($presensi->status_masuk === 'terlambat') {
                                    $statusParts[] = 'Terlambat';
                                }
                                if ($presensi->jam_pulang && $presensi->status_pulang === 'pulang_cepat') {
                                    $statusParts[] = 'Pulang Cepat';
                                }
                                
                                $keterangan = empty($statusParts) ? 'Tepat Waktu' : implode(' & ', $statusParts);
                            @endphp
                            <td style="border: 1px solid #000; text-align: center;">{{ $waktu }}</td>
                            <td style="border: 1px solid #000; text-align: center;">{{ $keterangan }}</td>
                        @elseif($leave)
                            <td style="border: 1px solid #000; text-align: center;">-</td>
                            <td style="border: 1px solid #000; text-align: center;">{{ $leave }}</td>
                        @else
                            <td style="border: 1px solid #000; text-align: center;">-</td>
                            <td style="border: 1px solid #000; text-align: center;">-</td>
                        @endif
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $totalCols }}" style="border: 1px solid #000; text-align: center;">Tidak ada data penempatan aktif.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endif

