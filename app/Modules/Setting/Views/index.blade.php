@extends('layouts.admin')

@section('title', 'Pengaturan Sistem - PKLku')
@section('page_title', 'Konfigurasi Parameter Sistem')

@section('content')
<div class="container-fluid p-0">
    <div class="col-md-9 mx-auto">
        <div class="card-premium">
            <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                Konfigurasi Parameter & Branding
            </h5>

            <form action="{{ route('setting.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Tab navigation -->
                <ul class="nav nav-tabs mb-4" id="settingsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="branding-tab" data-bs-toggle="tab" data-bs-target="#branding" type="button" role="tab" aria-controls="branding" aria-selected="true">
                            Branding & Sekolah
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab" aria-controls="attendance" aria-selected="false">
                            Jam Kerja & Geofence
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="grading-tab" data-bs-toggle="tab" data-bs-target="#grading" type="button" role="tab" aria-controls="grading" aria-selected="false">
                            Bobot Nilai Rapor
                        </button>
                    </li>
                </ul>

                <!-- Tab contents -->
                <div class="tab-content" id="settingsTabContent">
                    
                    <!-- Tab 1: Branding -->
                    <div class="tab-pane fade show active" id="branding" role="tabpanel" aria-labelledby="branding-tab">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="nama_sekolah" class="form-label small fw-semibold">Nama Sekolah Resmi</label>
                                    <input type="text" name="nama_sekolah" id="nama_sekolah" class="form-control" value="{{ $settings['nama_sekolah'] ?? 'SMK Negeri 1 Antigravity' }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="alamat_sekolah" class="form-label small fw-semibold">Alamat Lengkap Sekolah</label>
                                    <textarea name="alamat_sekolah" id="alamat_sekolah" class="form-control" rows="3" required>{{ $settings['alamat_sekolah'] ?? 'Jl. Teknologi Canggih No. 42, Kota Digital' }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="kota_sekolah" class="form-label small fw-semibold">Kabupaten / Kota Sekolah (Untuk Tanda Tangan PDF)</label>
                                    <input type="text" name="kota_sekolah" id="kota_sekolah" class="form-control" value="{{ $settings['kota_sekolah'] ?? 'Pati' }}" placeholder="Contoh: Pati" required>
                                </div>
                                <div class="mb-3">
                                    <label for="footer_rapor" class="form-label small fw-semibold">Petunjuk/Footer Rapor PDF</label>
                                    <textarea name="footer_rapor" id="footer_rapor" class="form-control" rows="2" placeholder="Contoh: Nilai diisi rentang 0 - 100. Keterangan diisi jika dibutuhkan." required>{{ $settings['footer_rapor'] ?? 'Nilai diisi rentang 0 - 100. Keterangan diisi jika dibutuhkan.' }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="footer_login" class="form-label small fw-semibold">Copyright / Footer Halaman Login</label>
                                    <input type="text" name="footer_login" id="footer_login" class="form-control" value="{{ $settings['footer_login'] ?? '© 2026 SMK Negeri 1 Antigravity. All rights reserved.' }}" placeholder="Contoh: © 2026 SMK Negeri 2 Pati. All rights reserved." required>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <label class="form-label small fw-semibold d-block">Logo Sekolah</label>
                                @if(!empty($settings['logo_sekolah']))
                                    <img src="{{ asset('storage/branding/' . $settings['logo_sekolah']) }}" class="img-thumbnail mb-2" style="max-height: 100px; object-fit: contain;">
                                @else
                                    <div class="border rounded d-flex align-items-center justify-content-center mx-auto mb-2 text-muted" style="width: 100px; height: 100px; background-color: var(--bg-canvas);">
                                        No Logo
                                    </div>
                                @endif
                                <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                            </div>
                        </div>

                        <hr class="my-4" style="color: var(--border-color);">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama_kepala_sekolah" class="form-label small fw-semibold">Nama Kepala Sekolah & Gelar</label>
                                <input type="text" name="nama_kepala_sekolah" id="nama_kepala_sekolah" class="form-control" value="{{ $settings['nama_kepala_sekolah'] ?? 'Dr. Antigravity, M.T.' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nip_kepala_sekolah" class="form-label small fw-semibold">NIP Kepala Sekolah</label>
                                <input type="text" name="nip_kepala_sekolah" id="nip_kepala_sekolah" class="form-control" value="{{ $settings['nip_kepala_sekolah'] ?? '198001012005011001' }}">
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Attendance -->
                    <div class="tab-pane fade" id="attendance" role="tabpanel" aria-labelledby="attendance-tab">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jam_masuk" class="form-label small fw-semibold">Jam Mulai Presensi Masuk</label>
                                <input type="text" name="jam_masuk" id="jam_masuk" class="form-control" placeholder="07:00" value="{{ $settings['jam_masuk'] ?? '07:00' }}" required>
                                <small class="text-muted">Format 24 jam (Contoh: 07:00). Murid dianggap terlambat jika absen 30 menit setelah jam ini.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="jam_pulang" class="form-label small fw-semibold">Jam Mulai Presensi Pulang (Bisa Check Out)</label>
                                <input type="text" name="jam_pulang" id="jam_pulang" class="form-control" placeholder="16:00" value="{{ $settings['jam_pulang'] ?? '16:00' }}" required>
                                <small class="text-muted">Format 24 jam (Contoh: 16:00). Murid baru bisa melakukan Check Out pulang setelah jam ini.</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="radius_presensi" class="form-label small fw-semibold">Jarak Geofence Default (Meter)</label>
                            <input type="number" name="radius_presensi" id="radius_presensi" class="form-control" value="{{ $settings['radius_presensi'] ?? 50 }}" min="10" required>
                            <small class="text-muted">Radius aman default dalam meter untuk wilayah DUDI presensi jika DUDI spesifik tidak diatur.</small>
                        </div>
                    </div>

                    <!-- Tab 3: Grading Weights -->
                    <div class="tab-pane fade" id="grading" role="tabpanel" aria-labelledby="grading-tab">
                        <div class="alert alert-info border-0 mb-4" style="background-color: rgba(79, 70, 229, 0.1); color: var(--accent-primary);">
                            Mengatur bobot presentase kontribusi nilai guru dan industri terhadap Nilai Akhir (Combined Score). Total gabungan wajib sama dengan 100%.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bobot_nilai_guru" class="form-label small fw-semibold">Bobot Nilai Guru Sekolah (%)</label>
                                <input type="number" name="bobot_nilai_guru" id="bobot_nilai_guru" class="form-control" value="{{ $settings['bobot_nilai_guru'] ?? 50 }}" min="0" max="100" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="bobot_nilai_industri" class="form-label small fw-semibold">Bobot Nilai Lapangan DUDI (%)</label>
                                <input type="number" name="bobot_nilai_industri" id="bobot_nilai_industri" class="form-control" value="{{ $settings['bobot_nilai_industri'] ?? 50 }}" min="0" max="100" required>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-top-color: var(--border-color) !important;">
                    <button type="submit" class="btn btn-primary px-4 font-heading">Simpan Konfigurasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
