@extends('layouts.admin')

@section('title', 'Manajemen Pengumuman - PKLku')
@section('page_title', 'Pengumuman Sekolah')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <!-- Input Form Column -->
        <div class="col-md-4 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Buat Pengumuman Baru</h5>
                
                <form action="{{ route('pengumuman.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="judul" class="form-label small fw-semibold">Judul Pengumuman</label>
                        <input type="text" name="judul" id="judul" class="form-control form-control-sm" placeholder="Jurnal Mingguan Wajib Dikumpulkan" required>
                    </div>

                    <div class="mb-3">
                        <label for="isi" class="form-label small fw-semibold">Isi Pengumuman</label>
                        <textarea name="isi" id="isi" class="form-control form-control-sm" rows="6" placeholder="Tulis pengumuman di sini..." required></textarea>
                    </div>

                    <div class="mb-4" x-data="{ target: 'semua' }">
                        <label for="target_role" class="form-label small fw-semibold">Target Penerima</label>
                        <select name="target_role" id="target_role" class="form-select form-select-sm mb-3" x-model="target" required>
                            <option value="semua">Semua (Guru & Siswa)</option>
                            <option value="guru">Hanya Guru Pembimbing</option>
                            <option value="murid">Hanya Siswa PKL</option>
                            <option value="kustom">Pengguna Kustom</option>
                        </select>

                        <!-- Custom users dropdown -->
                        <div x-show="target === 'kustom'" style="display: none;">
                            <label class="form-label small fw-semibold">Pilih Pengguna Kustom</label>
                            <div class="border rounded p-3" style="max-height: 150px; overflow-y: auto; background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                @foreach($users as $user)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox" name="user_ids[]" value="{{ $user->id }}" id="user_{{ $user->id }}">
                                        <label class="form-check-label small" for="user_{{ $user->id }}">
                                            {{ $user->name }} ({{ ucfirst($user->role) }})
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 font-heading">Publikasikan Pengumuman</button>
                </form>
            </div>
        </div>

        <!-- History List Column -->
        <div class="col-md-8 mb-4">
            <div class="card-premium p-0 overflow-hidden">
                <div class="p-3 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                    <h6 class="fw-bold m-0 text-dark dark-text-light">Riwayat Pengumuman Terbit</h6>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px;">
                        <thead>
                            <tr class="text-muted">
                                <th class="ps-4" style="width: 100px;">Tanggal</th>
                                <th>Pengumuman</th>
                                <th>Target</th>
                                <th class="text-center pe-4" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($announcements as $a)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $a->created_at->format('d/m/y') }}</td>
                                    <td>
                                        <div class="fw-bold text-dark dark-text-light">{{ $a->judul }}</div>
                                        <p class="text-secondary small m-0">
                                            {{ Str::limit($a->isi, 90) }}
                                            @if(strlen($a->isi) > 90)
                                                <a href="javascript:void(0);" class="text-primary fw-semibold ms-1" data-bs-toggle="modal" data-bs-target="#readAnnounceModal_{{ $a->id }}">Baca Selengkapnya</a>
                                            @endif
                                        </p>

                                        <!-- Read Modal -->
                                        <div class="modal fade text-start" id="readAnnounceModal_{{ $a->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                                <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                                    <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                                        <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">{{ $a->judul }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="text-muted small mb-3">
                                                            Dipublikasikan pada: {{ $a->created_at->translatedFormat('l, d F Y') }}
                                                        </div>
                                                        <div style="white-space: pre-line; font-size: 13px; line-height: 1.6; color: var(--text-primary);">
                                                            {!! e($a->isi) !!}
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top" style="border-top-color: var(--border-color) !important;">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-capitalize">{{ $a->target_role }}</span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button class="btn btn-sm btn-outline-warning btn-action" data-bs-toggle="modal" data-bs-target="#editAnnounceModal_{{ $a->id }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <form action="{{ route('pengumuman.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger btn-action">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Edit Modal -->
                                        <div class="modal fade text-start" id="editAnnounceModal_{{ $a->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                                    <div class="modal-header border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                                        <h5 class="modal-title font-heading fw-bold" style="font-size: 15px;">Edit Pengumuman</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('pengumuman.update', $a->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body" x-data="{ targetEdit: '{{ $a->target_role }}' }">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Judul</label>
                                                                <input type="text" name="judul" class="form-control form-control-sm" value="{{ $a->judul }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Isi Pengumuman</label>
                                                                <textarea name="isi" class="form-control form-control-sm" rows="5" required>{{ $a->isi }}</textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Target Penerima</label>
                                                                <select name="target_role" class="form-select form-select-sm mb-2" x-model="targetEdit" required>
                                                                    <option value="semua">Semua (Guru & Siswa)</option>
                                                                    <option value="guru">Hanya Guru Pembimbing</option>
                                                                    <option value="murid">Hanya Siswa PKL</option>
                                                                    <option value="kustom">Pengguna Kustom</option>
                                                                </select>
                                                                
                                                                <!-- Custom users dropdown -->
                                                                <div x-show="targetEdit === 'kustom'" style="display: none;">
                                                                    <label class="form-label small fw-semibold">Pilih Pengguna Kustom</label>
                                                                    <div class="border rounded p-3" style="max-height: 150px; overflow-y: auto; background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                                                        @foreach($users as $user)
                                                                            <div class="form-check mb-1">
                                                                                <input class="form-check-input" type="checkbox" name="user_ids[]" value="{{ $user->id }}" id="edit_user_{{ $a->id }}_{{ $user->id }}" {{ $a->penerima->contains('user_id', $user->id) ? 'checked' : '' }}>
                                                                                <label class="form-check-label small" for="edit_user_{{ $a->id }}_{{ $user->id }}">
                                                                                    {{ $user->name }}
                                                                                </label>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
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
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada pengumuman yang dipublikasikan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($announcements->hasPages())
                <div class="px-4 py-3 border-top d-flex justify-content-end" style="border-top-color: var(--border-color) !important;">
                    {{ $announcements->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
