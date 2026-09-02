@extends('layouts.admin')

@section('title', 'Jurnal Kegiatan - PKLku')
@section('page_title', 'Jurnal Aktivitas Harian')

@section('content')
<div class="container-fluid p-0">
    @if(!$placement)
        <div class="card-premium empty-state text-center py-5">
            <div class="empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h6 class="empty-state-title">Penempatan Belum Aktif</h6>
            <p class="empty-state-text">Akses pengisian jurnal hanya aktif ketika penempatan Anda telah diselesaikan oleh Admin.</p>
        </div>
    @else
        <div class="row">
            <!-- Journal submission form -->
            <div class="col-md-5 mb-4" x-data="journalHandler()">
                <div class="card-premium">
                    <h5 class="fw-bold font-heading mb-3 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Tulis Jurnal Baru</h5>
                    
                    <form action="{{ route('jurnal.store') }}" method="POST" enctype="multipart/form-data" @submit="submitForm($event)">
                        @csrf
                        <input type="hidden" name="penempatan_pkl_id" value="{{ $placement->id }}">

                        <div class="mb-3">
                            <label for="tanggal" class="form-label font-heading fw-semibold text-secondary" style="font-size: 13px;">Tanggal Kegiatan</label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi_aktivitas" class="form-label font-heading fw-semibold text-secondary mb-1" style="font-size: 13px;">Rincian Aktivitas Harian</label>
                            
                            <!-- Panduan Penulisan 5W + 1H -->
                            <div class="p-2.5 px-3 mb-2 rounded border" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                <div class="fw-bold text-dark mb-1.5 d-flex align-items-center gap-1" style="font-size: 13px;">
                                    <span>💡</span>
                                    <span>Panduan Penulisan (5W + 1H):</span>
                                </div>
                                <div class="d-flex flex-wrap gap-1.5" style="font-size: 12.5px;">
                                    <span class="badge bg-white text-dark border px-2 py-1.5 shadow-xs"><strong class="text-primary">What:</strong> Tugas/Materi</span>
                                    <span class="badge bg-white text-dark border px-2 py-1.5 shadow-xs"><strong class="text-primary">Why:</strong> Tujuan</span>
                                    <span class="badge bg-white text-dark border px-2 py-1.5 shadow-xs"><strong class="text-primary">Where:</strong> Lokasi/Tools</span>
                                    <span class="badge bg-white text-dark border px-2 py-1.5 shadow-xs"><strong class="text-primary">When:</strong> Waktu</span>
                                    <span class="badge bg-white text-dark border px-2 py-1.5 shadow-xs"><strong class="text-primary">Who:</strong> Pembimbing</span>
                                    <span class="badge bg-white text-dark border px-2 py-1.5 shadow-xs"><strong class="text-primary">How:</strong> Langkah & Hasil</span>
                                </div>
                            </div>

                            <textarea name="deskripsi_aktivitas" id="deskripsi_aktivitas" class="form-control form-control-sm" rows="5" placeholder="Tuliskan aktivitas, tugas yang dikerjakan, atau materi yg dipelajari hari ini sesuai panduan 5W + 1H..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="foto" class="form-label font-heading fw-semibold text-secondary" style="font-size: 13px;">Bukti Kegiatan (Wajib, Foto/PDF)</label>
                            <input type="file" name="foto" id="foto" class="form-control form-control-sm" accept="image/*, application/pdf" @change="handleFileSelect($event)" required>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Format: JPG, PNG, atau PDF (Maks. 2MB)</small>
                            
                            <!-- Live File Preview -->
                            <div class="mt-2" x-show="filePreview" style="display: none;">
                                <div class="p-2 border rounded d-flex align-items-center gap-2" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                    <template x-if="!isPdf && filePreview">
                                        <img :src="filePreview" alt="Pratinjau Foto" class="rounded border" width="48" height="48" style="object-fit: cover;">
                                    </template>
                                    <template x-if="isPdf">
                                        <div class="p-2 bg-danger-light text-danger rounded font-heading fw-bold" style="font-size: 12px;">PDF</div>
                                    </template>
                                    <div class="text-truncate" style="font-size: 12px;">
                                        <span class="fw-semibold text-dark d-block text-truncate" x-text="fileName"></span>
                                        <span class="text-success" style="font-size: 11px;">Siap diunggah</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 font-heading fw-semibold py-2 mt-2" :disabled="submitting">
                            <span x-text="submitting ? 'Mengirim Jurnal...' : 'Kirim Jurnal Harian'"></span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- History panel -->
            <div class="col-md-7 mb-4">
                <div class="card-premium">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                        <h5 class="fw-bold font-heading m-0 text-dark">Riwayat Jurnal Harian</h5>
                        <a href="{{ route('laporan.murid_jurnal_pdf') }}" class="btn btn-sm btn-outline-primary d-flex align-items-center" style="font-size: 12px; padding: 4px 8px;" aria-label="Unduh Riwayat Jurnal PDF">
                            <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Unduh PDF
                        </a>
                    </div>
                    
                    <!-- Desktop Table View (md and up) -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle mb-0" style="min-width: 520px; color: var(--text-primary); font-size: 13px;">
                            <thead>
                                <tr class="text-muted">
                                    <th style="width: 90px;">Tanggal</th>
                                    <th>Aktivitas</th>
                                    <th class="text-center" style="width: 70px;">Bukti</th>
                                    <th class="text-center" style="width: 110px;">Status</th>
                                    <th class="text-center" style="width: 65px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($journals as $j)
                                    <tr>
                                        <td class="fw-semibold">{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('d/m/y') }}</td>
                                        <td>
                                            <div style="line-height: 1.5; word-break: break-word; white-space: normal;">{{ $j->deskripsi_aktivitas }}</div>
                                            @if($j->catatan_verifikasi)
                                                <small class="text-danger d-block mt-1"><strong>Catatan Guru:</strong> {{ $j->catatan_verifikasi }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($j->foto_kegiatan)
                                                @php
                                                    $isPdf = Str::endsWith(strtolower($j->foto_kegiatan), '.pdf');
                                                @endphp
                                                <a href="{{ asset('storage/jurnal/' . $j->foto_kegiatan) }}" target="_blank" aria-label="Lihat lampiran bukti jurnal">
                                                    @if($isPdf)
                                                        <span class="badge bg-danger-light text-danger p-1 font-heading" style="font-size: 11px;">PDF</span>
                                                    @else
                                                        <img src="{{ asset('storage/jurnal/' . $j->foto_kegiatan) }}" class="rounded border" width="36" height="36" style="object-fit: cover;" alt="Bukti Kegiatan">
                                                    @endif
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($j->status_verifikasi === 'disetujui')
                                                <span class="status-badge bg-success-light text-success">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    Disetujui
                                                </span>
                                            @elseif($j->status_verifikasi === 'revisi')
                                                <span class="status-badge bg-warning-light text-warning">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    Revisi
                                                </span>
                                            @elseif($j->status_verifikasi === 'ditolak')
                                                <span class="status-badge bg-danger-light text-danger">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    Ditolak
                                                </span>
                                            @else
                                                <span class="status-badge bg-secondary-light text-secondary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(in_array($j->status_verifikasi, ['pending', 'revisi']))
                                                <a href="{{ route('jurnal.edit', $j->id) }}" class="btn btn-sm btn-outline-warning btn-action" title="Edit Jurnal" aria-label="Edit Jurnal">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="empty-state py-4">
                                                <div class="empty-state-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                    </svg>
                                                </div>
                                                <h6 class="empty-state-title">Belum Ada Jurnal</h6>
                                                <p class="empty-state-text">Mulai tulis aktivitas pekerjaan yang Anda lakukan hari ini pada form di sebelah kiri.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card List View (Visible on smartphone < md) -->
                    <div class="d-md-none">
                        @forelse($journals as $j)
                            <div class="card p-3 mb-3 border rounded shadow-xs" style="background-color: var(--bg-card); border-left: 4px solid {{ $j->status_verifikasi === 'disetujui' ? '#10b981' : ($j->status_verifikasi === 'revisi' ? '#f59e0b' : ($j->status_verifikasi === 'ditolak' ? '#ef4444' : '#64748b')) }} !important;">
                                <!-- Header: Tanggal & Status -->
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                                    <span class="fw-bold font-heading text-dark" style="font-size: 13px;">
                                        📅 {{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('l, d M Y') }}
                                    </span>
                                    <div>
                                        @if($j->status_verifikasi === 'disetujui')
                                            <span class="status-badge bg-success-light text-success" style="font-size: 11px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Disetujui
                                            </span>
                                        @elseif($j->status_verifikasi === 'revisi')
                                            <span class="status-badge bg-warning-light text-warning" style="font-size: 11px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                Revisi
                                            </span>
                                        @elseif($j->status_verifikasi === 'ditolak')
                                            <span class="status-badge bg-danger-light text-danger" style="font-size: 11px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Ditolak
                                            </span>
                                        @else
                                            <span class="status-badge bg-secondary-light text-secondary" style="font-size: 11px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Pending
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Aktivitas -->
                                <div class="p-2.5 rounded bg-light border mb-2" style="background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important; font-size: 13px; line-height: 1.6; color: var(--text-primary); white-space: pre-line; word-break: break-word;">
                                    {{ $j->deskripsi_aktivitas }}
                                </div>

                                <!-- Catatan Guru -->
                                @if($j->catatan_verifikasi)
                                    <div class="p-2 rounded mb-2 border" style="background-color: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.2) !important; font-size: 12px; line-height: 1.4;">
                                        <strong class="text-danger">💬 Catatan Guru:</strong>
                                        <div class="text-dark mt-0.5" style="white-space: pre-line;">{{ $j->catatan_verifikasi }}</div>
                                    </div>
                                @endif

                                <!-- Footer Bukti & Edit -->
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top flex-wrap gap-2" style="border-top-color: var(--border-color) !important;">
                                    <div>
                                        @if($j->foto_kegiatan)
                                            @php
                                                $isPdf = Str::endsWith(strtolower($j->foto_kegiatan), '.pdf');
                                            @endphp
                                            <a href="{{ asset('storage/jurnal/' . $j->foto_kegiatan) }}" target="_blank" class="btn btn-xs btn-outline-secondary font-heading d-flex align-items-center gap-1.5 py-1 px-2.5">
                                                @if($isPdf)
                                                    <span class="badge bg-danger text-white" style="font-size: 9.5px;">PDF</span>
                                                    <span style="font-size: 11.5px;">Lihat Dokumen</span>
                                                @else
                                                    <img src="{{ asset('storage/jurnal/' . $j->foto_kegiatan) }}" class="rounded border" width="22" height="22" style="object-fit: cover;" alt="Bukti Kegiatan">
                                                    <span style="font-size: 11.5px;">Lihat Foto ↗</span>
                                                @endif
                                            </a>
                                        @else
                                            <small class="text-muted" style="font-size: 11px;">Tanpa lampiran</small>
                                        @endif
                                    </div>

                                    @if(in_array($j->status_verifikasi, ['pending', 'revisi']))
                                        <a href="{{ route('jurnal.edit', $j->id) }}" class="btn btn-sm btn-outline-warning font-heading d-flex align-items-center gap-1 px-2.5 py-1" style="font-size: 12px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            <span>Edit Jurnal</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="empty-state py-4 text-center">
                                <div class="empty-state-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <h6 class="empty-state-title">Belum Ada Jurnal</h6>
                                <p class="empty-state-text">Mulai tulis aktivitas harian Anda pada form di atas.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($journals instanceof \Illuminate\Pagination\LengthAwarePaginator && $journals->hasPages())
                        <div class="px-3 py-2 border-top" style="border-top-color: var(--border-color) !important;">
                            {{ $journals->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function journalHandler() {
        return {
            filePreview: null,
            fileName: '',
            isPdf: false,
            rawBlob: null,
            submitting: false,
            handleFileSelect(e) {
                const file = e.target.files[0];
                if (!file) {
                    this.filePreview = null;
                    this.fileName = '';
                    this.rawBlob = null;
                    return;
                }
                this.fileName = file.name;
                
                // Immediately read file into memory to prevent Android Chrome ERR_UPLOAD_FILE_CHANGED
                if (file.type.startsWith('image/')) {
                    this.isPdf = false;
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        this.filePreview = ev.target.result;
                    };
                    reader.readAsDataURL(file);

                    // Read ArrayBuffer for clean Blob
                    const blobReader = new FileReader();
                    blobReader.onload = (ev) => {
                        this.rawBlob = new Blob([ev.target.result], { type: file.type || 'image/jpeg' });
                    };
                    blobReader.readAsArrayBuffer(file);
                } else if (file.type === 'application/pdf') {
                    this.isPdf = true;
                    this.filePreview = 'pdf';
                    const blobReader = new FileReader();
                    blobReader.onload = (ev) => {
                        this.rawBlob = new Blob([ev.target.result], { type: 'application/pdf' });
                    };
                    blobReader.readAsArrayBuffer(file);
                } else {
                    this.rawBlob = file;
                }
            },
            async submitForm(e) {
                e.preventDefault();
                if (this.submitting) return;

                const form = e.target;
                const deskripsi = form.querySelector('#deskripsi_aktivitas').value.trim();

                if (!deskripsi) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Rincian aktivitas harian wajib diisi.',
                        confirmButtonColor: 'var(--accent-primary)'
                    });
                    return;
                }

                this.submitting = true;
                const formData = new FormData(form);

                // If in-memory Blob exists, attach it to FormData directly
                if (this.rawBlob) {
                    formData.set('foto', this.rawBlob, this.fileName || 'bukti_kegiatan.jpg');
                }

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token || ''
                        }
                    });

                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message || 'Jurnal harian berhasil dikirim.',
                            timer: 1800,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        let msg = data.message || 'Gagal mengirim jurnal. Silakan periksa kembali isian form Anda.';
                        if (data.errors) {
                            msg = Object.values(data.errors).flat().join('\n');
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengirim Jurnal',
                            text: msg,
                            confirmButtonColor: 'var(--accent-primary)'
                        });
                        this.submitting = false;
                    }
                } catch (err) {
                    console.error('Fetch submit error:', err);
                    // Fallback to standard form submit
                    form.submit();
                }
            }
        };
    }
</script>
@endsection
