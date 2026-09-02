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

                    <button type="submit" class="btn btn-sm btn-primary w-100 font-heading">Publikasikan Pengumuman</button>
                </form>
            </div>
        </div>

        <!-- History List Column -->
        <div class="col-md-8 mb-4">
            <div class="card-premium p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-bottom-color: var(--border-color) !important;">
                    <h6 class="fw-bold m-0 text-dark dark-text-light">Riwayat Pengumuman Terbit</h6>
                </div>

                <!-- Desktop Table View (md and up) -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-primary); font-size: 13px; min-width: 650px;">
                        <thead class="table-light">
                            <tr class="font-heading" style="font-size: 13px; font-weight: 600;">
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
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-light text-primary fw-semibold text-capitalize">{{ $a->target_role }}</span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button class="btn btn-sm btn-outline-warning btn-action" data-bs-toggle="modal" data-bs-target="#editAnnounceModal_{{ $a->id }}" title="Edit Pengumuman" aria-label="Edit Pengumuman {{ $a->judul }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <form action="{{ route('pengumuman.destroy', $a->id) }}" method="POST" id="deleteAnnounceForm_{{ $a->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-action" title="Hapus Pengumuman" aria-label="Hapus Pengumuman {{ $a->judul }}" onclick="window.confirmDelete('deleteAnnounceForm_{{ $a->id }}', 'pengumuman {{ addslashes($a->judul) }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="empty-state py-4">
                                            <div class="empty-state-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                </svg>
                                            </div>
                                            <h6 class="empty-state-title">Belum Ada Pengumuman</h6>
                                            <p class="empty-state-text">Gunakan form di sebelah kiri untuk mempublikasikan pengumuman sekolah baru.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List View (Visible on smartphone < md) -->
                <div class="d-md-none p-3">
                    @forelse($announcements as $a)
                        <div class="card p-3 mb-3 border rounded shadow-xs" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                            <!-- Top: Tanggal & Target -->
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                <div class="d-flex align-items-center gap-1.5 text-muted small">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>{{ $a->created_at->translatedFormat('d M Y') }}</span>
                                </div>
                                <span class="badge bg-primary-light text-primary fw-semibold text-capitalize" style="font-size: 10.5px;">
                                    {{ $a->target_role }}
                                </span>
                            </div>

                            <!-- Judul & Isi -->
                            <div class="mb-2">
                                <h6 class="fw-bold font-heading text-dark mb-1" style="font-size: 14px;">{{ $a->judul }}</h6>
                                <p class="text-secondary small m-0" style="font-size: 12.5px; line-height: 1.5;">
                                    {{ Str::limit($a->isi, 100) }}
                                    @if(strlen($a->isi) > 100)
                                        <a href="javascript:void(0);" class="text-primary fw-semibold ms-1 text-decoration-none" data-bs-toggle="modal" data-bs-target="#readAnnounceModal_{{ $a->id }}">
                                            Baca Selengkapnya &rarr;
                                        </a>
                                    @endif
                                </p>
                            </div>

                            <!-- Footer: Action buttons -->
                            <div class="pt-2 border-top d-flex align-items-center justify-content-end gap-2" style="border-top-color: var(--border-color) !important;">
                                <button type="button" class="btn btn-sm btn-outline-warning font-heading d-flex align-items-center gap-1 px-2.5 py-1" data-bs-toggle="modal" data-bs-target="#editAnnounceModal_{{ $a->id }}" style="font-size: 12px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Edit</span>
                                </button>
                                <form action="{{ route('pengumuman.destroy', $a->id) }}" method="POST" id="deleteAnnounceFormMob_{{ $a->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger px-2.5 py-1" title="Hapus Pengumuman" onclick="window.confirmDelete('deleteAnnounceFormMob_{{ $a->id }}', 'pengumuman {{ addslashes($a->judul) }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-4 text-center">
                            <h6 class="empty-state-title">Belum Ada Pengumuman</h6>
                            <p class="empty-state-text">Gunakan form di atas untuk mempublikasikan pengumuman sekolah baru.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Modals Read & Edit -->
                @foreach($announcements as $a)
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
                                        Dipublikasikan pada: {{ $a->created_at->translatedFormat('l, d F Y') }} &bull; Target: <span class="badge bg-primary-light text-primary text-capitalize">{{ $a->target_role }}</span>
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

                    <!-- Edit Modal -->
                    <div class="modal fade text-start" id="editAnnounceModal_{{ $a->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
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
                                        <button type="submit" class="btn btn-sm btn-primary" data-loading-text="Menyimpan...">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($announcements->hasPages())
                <div class="border-top px-3 py-2" style="border-top-color: var(--border-color) !important;">
                    {{ $announcements->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
