@extends('layouts.admin')

@section('title', 'Plotting Penempatan - PKLku')
@section('page_title', 'Plotting Penempatan Murid')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h5 class="fw-bold font-heading m-0 text-dark dark-text-light">Plotting Penempatan Murid & Guru Pembimbing</h5>
        <button class="btn btn-sm btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#massPlotModal">
            <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Plotting Massal Murid
        </button>
    </div>

    <!-- Active Placements Table -->
    <div class="card-premium p-0 overflow-hidden">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-bottom-color: var(--border-color) !important;">
            <h6 class="fw-bold m-0 text-dark dark-text-light">Daftar Penempatan Aktif</h6>
            <button type="submit" form="bulkDeleteForm" id="btnDeleteSelected" class="btn btn-xs btn-danger font-heading fw-bold" style="display: none; font-size: 11px; padding: 4px 8px;" onclick="return confirm('Apakah Anda yakin ingin membatalkan/menghapus penempatan yang terpilih?');">
                <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: inline-block; vertical-align: middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Terpilih
            </button>
        </div>

        <form action="{{ route('penempatan.destroy_bulk') }}" method="POST" id="bulkDeleteForm">
            @csrf
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="color: var(--text-primary);">
                    <thead class="table-light">
                        <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
                            <th class="ps-4" style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                            <th>Murid (NIS)</th>
                            <th>Kelas</th>
                            <th>Tempat DUDI</th>
                            <th>Guru Pembimbing</th>
                            <th>Pembimbing Industri</th>
                            <th class="text-center">Tanggal Pelaksanaan</th>
                            <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13px;">
                        @forelse($placements as $p)
                            <tr>
                                <td class="ps-4"><input type="checkbox" name="ids[]" value="{{ $p->id }}" class="row-checkbox"></td>
                                <td>
                                    <div class="fw-semibold">{{ $p->murid ? $p->murid->nama : 'Murid Terhapus' }}</div>
                                    <small class="text-muted">{{ $p->murid ? $p->murid->nis : '-' }}</small>
                                </td>
                                <td>{{ $p->murid && $p->murid->kelas ? $p->murid->kelas->nama : '-' }}</td>
                                <td><span class="fw-semibold text-primary">{{ $p->dudi ? $p->dudi->nama : 'DUDI Terhapus' }}</span></td>
                                <td>{{ $p->guru ? $p->guru->nama : 'Guru Terhapus' }}</td>
                                <td>{{ $p->pembimbingIndustri ? $p->pembimbingIndustri->nama : ($p->dudi->pic_nama ?? '-') }}</td>
                                <td class="text-center text-muted">
                                    {{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/y') }} s/d {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/y') }}
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-outline-warning p-1" data-bs-toggle="modal" data-bs-target="#editModal_{{ $p->id }}" title="Edit Penempatan">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1" title="Hapus Penempatan" onclick="if(confirm('Apakah Anda yakin ingin membatalkan/menghapus penempatan PKL murid ini?')) { document.getElementById('deleteForm_{{ $p->id }}').submit(); }">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Belum ada penempatan murid aktif saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
        
        @if($placements->hasPages())
        <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
            {{ $placements->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal: Plotting Massal -->
<div class="modal fade" id="massPlotModal" tabindex="-1" aria-labelledby="massPlotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                <h5 class="modal-title font-heading fw-bold" id="massPlotModalLabel">Plotting Penempatan Murid Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('penempatan.store_massal') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Step 1: Select Class & Students -->
                    <div class="mb-4">
                        <label for="classFilterSelect" class="form-label small fw-semibold text-uppercase text-muted" style="letter-spacing: 0.5px;">1. Pilih Kelas Murid</label>
                        <select id="classFilterSelect" class="form-select form-select-sm mb-3">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasOptions as $kls)
                                <option value="{{ $kls->id }}">{{ $kls->nama }}</option>
                            @endforeach
                        </select>

                        <label class="form-label small fw-semibold text-uppercase text-muted d-block mb-2" style="letter-spacing: 0.5px;">Pilih Murid (Dapat memilih lebih dari satu)</label>
                        <div class="border rounded p-3" style="max-height: 180px; overflow-y: auto; background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                            @forelse($kelasOptions as $kls)
                                <div class="class-student-list" id="class_students_{{ $kls->id }}" style="display: none;">
                                    @php
                                        $studentsInClass = $unassignedStudents->where('kelas_id', $kls->id);
                                    @endphp
                                    @foreach($studentsInClass as $s)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input student-checkbox" type="checkbox" name="murid_ids[]" value="{{ $s->id }}" id="checkMurid_{{ $s->id }}">
                                            <label class="form-check-label text-dark dark-text-light small" for="checkMurid_{{ $s->id }}">
                                                <strong>{{ $s->nama }}</strong> ({{ $s->nis }})
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @empty
                                <div class="text-center text-muted py-3 small" id="noStudentsPlaceholder">
                                    Semua murid sudah ter-plotting untuk periode aktif ini.
                                </div>
                            @endforelse
                            @if(count($kelasOptions) > 0)
                                <div class="text-center text-muted py-3 small" id="selectClassPlaceholder">
                                    Silakan pilih kelas terlebih dahulu untuk menampilkan daftar murid.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Step 2: Select Dudi & Guru -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="dudiSelect" class="form-label small fw-semibold">2. Pilih Mitra DUDI</label>
                            <select name="dudi_id" id="dudiSelect" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Mitra DUDI --</option>
                                @foreach($dudis as $d)
                                    <option value="{{ $d->id }}">{{ $d->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="guruSelect" class="form-label small fw-semibold">3. Pilih Guru Pembimbing</label>
                            <select name="guru_id" id="guruSelect" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Guru Pembimbing --</option>
                                @foreach($gurus as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Step 3: Dates -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_mulai" class="form-label small fw-semibold">Tanggal Mulai PKL</label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_selesai" class="form-label small fw-semibold">Tanggal Selesai PKL</label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control form-control-sm" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Plotting</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@foreach($placements as $p)
    <!-- Modal Edit -->
    <div class="modal fade" id="editModal_{{ $p->id }}" tabindex="-1" aria-labelledby="editModalLabel_{{ $p->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                    <h5 class="modal-title font-heading fw-bold" id="editModalLabel_{{ $p->id }}" style="font-size: 15px;">Edit Detail Penempatan PKL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('penempatan.update', $p->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Nama Murid</label>
                            <input type="text" class="form-control" value="{{ $p->murid ? $p->murid->nama : 'Murid Terhapus' }} ({{ $p->murid ? $p->murid->nis : '-' }})" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="dudiSelect_{{ $p->id }}" class="form-label small fw-semibold">Mitra DUDI</label>
                            <select name="dudi_id" id="dudiSelect_{{ $p->id }}" class="form-select form-select-sm" required>
                                @foreach($dudis as $d)
                                    <option value="{{ $d->id }}" {{ $p->dudi_id == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="guruSelect_{{ $p->id }}" class="form-label small fw-semibold">Guru Pembimbing</label>
                            <select name="guru_id" id="guruSelect_{{ $p->id }}" class="form-select form-select-sm" required>
                                @foreach($gurus as $g)
                                    <option value="{{ $g->id }}" {{ $p->guru_id == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="tglMulai_{{ $p->id }}" class="form-label small fw-semibold">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" id="tglMulai_{{ $p->id }}" class="form-control form-control-sm" value="{{ $p->tanggal_mulai }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tglSelesai_{{ $p->id }}" class="form-label small fw-semibold">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" id="tglSelesai_{{ $p->id }}" class="form-control form-control-sm" value="{{ $p->tanggal_selesai }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden delete form for single delete -->
    <form action="{{ route('penempatan.destroy', $p->id) }}" method="POST" id="deleteForm_{{ $p->id }}" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endforeach

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const classFilterSelect = document.getElementById('classFilterSelect');
        const selectClassPlaceholder = document.getElementById('selectClassPlaceholder');

        if (classFilterSelect) {
            classFilterSelect.addEventListener('change', function() {
                const selectedClassId = this.value;
                
                // Hide all lists
                document.querySelectorAll('.class-student-list').forEach(function(el) {
                    el.style.display = 'none';
                });
                
                if (selectedClassId) {
                    const targetList = document.getElementById('class_students_' + selectedClassId);
                    if (targetList) {
                        targetList.style.display = 'block';
                        if (selectClassPlaceholder) selectClassPlaceholder.style.display = 'none';
                    } else {
                        if (selectClassPlaceholder) selectClassPlaceholder.style.display = 'block';
                    }
                } else {
                    if (selectClassPlaceholder) selectClassPlaceholder.style.display = 'block';
                }
            });
        }

        // Bulk delete logic
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const btnDeleteSelected = document.getElementById('btnDeleteSelected');

        function toggleActionButtons() {
            const anyChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
            if (btnDeleteSelected) {
                btnDeleteSelected.style.display = anyChecked ? 'inline-block' : 'none';
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                rowCheckboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                toggleActionButtons();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
                if (selectAll) selectAll.checked = allChecked;
                toggleActionButtons();
            });
        });
    });
</script>
@endsection
