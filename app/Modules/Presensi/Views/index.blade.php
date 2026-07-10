@extends('layouts.admin')

@section('title', 'Riwayat Presensi - PKLku')
@section('page_title', 'Pemantauan Kehadiran Murid')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">Jurnal Kehadiran Harian Murid</h5>
    </div>

    <!-- Search / Filter Card -->
    <div class="card-premium mb-4">
        <form action="{{ route('presensi.index') }}" method="GET" class="row g-3">
            <div class="col-md-8">
                <label class="form-label small fw-semibold">Pilih Tanggal Absensi</label>
                <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ request('tanggal', now()->toDateString()) }}">
            </div>
            <div class="col-md-4 d-grid align-items-end">
                <button type="submit" class="btn btn-sm btn-primary">Filter Tanggal</button>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="card-premium p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="color: var(--text-primary);">
                <thead class="table-light">
                    <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                        <th class="ps-4">Murid (Kelas)</th>
                        <th>DUDI Tempat PKL</th>
                        <th class="text-center">Jam Check In</th>
                        <th class="text-center">Foto Check In</th>
                        <th class="text-center">Jam Check Out</th>
                        <th class="text-center">Foto Check Out</th>
                        <th class="text-center pe-4" style="width: 150px;">Status @if(auth()->user()->role === 'admin') / Aksi @endif</th>
                    </tr>
                </thead>
                <tbody style="font-size: 13px;">
                    @forelse($presensis as $p)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $p->penempatanPkl->murid->nama }}</div>
                                <small class="text-muted">{{ $p->penempatanPkl->murid->kelas->nama }}</small>
                            </td>
                            <td>{{ $p->penempatanPkl->dudi->nama }}</td>
                            <td class="text-center fw-semibold text-success">
                                {{ $p->jam_masuk ? substr($p->jam_masuk, 0, 5) : '-' }}
                            </td>
                            <td class="text-center">
                                @if($p->foto_masuk)
                                    <a href="{{ asset('storage/attendance/' . $p->foto_masuk) }}" target="_blank">
                                        <img src="{{ asset('storage/attendance/' . $p->foto_masuk) }}" class="rounded border" width="40" height="40" style="object-fit: cover;">
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center fw-semibold text-warning">
                                {{ $p->jam_pulang ? substr($p->jam_pulang, 0, 5) : '-' }}
                            </td>
                            <td class="text-center">
                                @if($p->foto_pulang)
                                    <a href="{{ asset('storage/attendance/' . $p->foto_pulang) }}" target="_blank">
                                        <img src="{{ asset('storage/attendance/' . $p->foto_pulang) }}" class="rounded border" width="40" height="40" style="object-fit: cover;">
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="mb-1">
                                    <span class="badge {{ $p->status_masuk === 'tepat_waktu' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $p->status_masuk === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat' }}
                                    </span>
                                </div>
                                @if($p->status_pulang === 'pulang_cepat')
                                    <div class="mb-1">
                                        <span class="badge bg-warning text-dark">Pulang Cepat</span>
                                    </div>
                                @endif
                                @if(auth()->user()->role === 'admin')
                                    <div class="mt-2">
                                        <form action="{{ route('presensi.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi ini?');" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Hapus Presensi">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Tidak ada catatan presensi untuk tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($presensis->hasPages())
        <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
            {{ $presensis->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
