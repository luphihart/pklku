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
                                    <input type="text" name="nama_sekolah" id="nama_sekolah" class="form-control" value="{{ $settings['nama_sekolah'] ?? 'SMK Negeri 1 Jakarta' }}" required>
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
                                    <input type="text" name="footer_login" id="footer_login" class="form-control" value="{{ $settings['footer_login'] ?? '© 2026 SMK Negeri 1 Jakarta. All rights reserved.' }}" placeholder="Contoh: © 2026 SMK Negeri 2 Pati. All rights reserved." required>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <label class="form-label small fw-semibold d-block">Logo Sekolah</label>
                                @php
                                    $settingLogo = $settings['logo_sekolah'] ?? null;
                                    $hasCustomLogo = !empty($settingLogo) && (file_exists(public_path('storage/branding/' . $settingLogo)) || file_exists(public_path($settingLogo)));
                                    $logoUrl = $hasCustomLogo ? (file_exists(public_path('storage/branding/' . $settingLogo)) ? asset('storage/branding/' . $settingLogo) : asset($settingLogo)) : null;
                                @endphp
                                @if($hasCustomLogo)
                                    <div class="mb-2 position-relative d-inline-block">
                                        <img src="{{ $logoUrl }}" class="img-thumbnail" style="max-height: 100px; max-width: 140px; object-fit: contain;">
                                    </div>
                                    <div class="form-check d-flex justify-content-center align-items-center gap-1 mb-2">
                                        <input class="form-check-input mt-0" type="checkbox" name="hapus_logo" value="1" id="hapus_logo">
                                        <label class="form-check-label small text-danger fw-semibold" for="hapus_logo" style="font-size: 11px;">
                                            Hapus Logo (Gunakan Icon Default)
                                        </label>
                                    </div>
                                @else
                                    <div class="border rounded d-flex flex-column align-items-center justify-content-center mx-auto mb-2 p-2" style="width: 100px; height: 100px; background-color: var(--bg-canvas);">
                                        <div class="p-2 rounded-3 text-primary d-flex align-items-center justify-content-center bg-primary-light mb-1" style="width: 42px; height: 42px;">
                                            <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='var(--accent-primary)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' width="24" height="24"><path d='M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z'></path><path d='M12 11v6'></path><path d='M9 14h6'></path></svg>
                                        </div>
                                        <small class="text-muted" style="font-size: 10px;">Icon Default</small>
                                    </div>
                                @endif
                                <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">Format: PNG, JPG, JPEG, SVG (Maks. 1MB)</small>
                            </div>
                        </div>

                        <hr class="my-4" style="color: var(--border-color);">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama_kepala_sekolah" class="form-label small fw-semibold">Nama Kepala Sekolah & Gelar</label>
                                <input type="text" name="nama_kepala_sekolah" id="nama_kepala_sekolah" class="form-control" value="{{ $settings['nama_kepala_sekolah'] ?? 'Dr. H. Akhmad Yusuf, M.T.' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nip_kepala_sekolah" class="form-label small fw-semibold">NIP Kepala Sekolah</label>
                                <input type="text" name="nip_kepala_sekolah" id="nip_kepala_sekolah" class="form-control" value="{{ $settings['nip_kepala_sekolah'] ?? '198001012005011001' }}">
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Attendance -->
                    <div class="tab-pane fade" id="attendance" role="tabpanel" aria-labelledby="attendance-tab">
                        <div class="alert alert-info py-2 px-3 mb-4 d-flex align-items-center gap-2" style="font-size: 13px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                Anda dapat mengatur <strong>Jam Kerja Reguler, Shift Pagi, dan Shift Siang</strong> standar sekolah di bawah ini. Pengaturan ini dapat dipilih atau dikustomisasi per murid saat melakukan <strong>Plotting Penempatan</strong>.
                            </div>
                        </div>

                        <!-- Section 1: Shift Reguler -->
                        <div class="card p-3 mb-4 border" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                            <h6 class="fw-bold font-heading mb-3 d-flex align-items-center gap-2 text-dark">
                                <span class="badge bg-primary-light text-primary">1</span>
                                Jam Kerja Shift Reguler / Standar
                            </h6>
                            <div class="row">
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="jam_masuk" class="form-label small fw-semibold">Buka Jam Masuk</label>
                                    <input type="text" name="jam_masuk" id="jam_masuk" class="form-control form-control-sm" placeholder="06:00" value="{{ $settings['jam_masuk'] ?? '06:00' }}" required>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="batas_terlambat" class="form-label small fw-semibold">Batas Terlambat</label>
                                    <input type="text" name="batas_terlambat" id="batas_terlambat" class="form-control form-control-sm" placeholder="07:30" value="{{ $settings['batas_terlambat'] ?? '07:30' }}" required>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="jam_pulang" class="form-label small fw-semibold">Buka Jam Pulang</label>
                                    <input type="text" name="jam_pulang" id="jam_pulang" class="form-control form-control-sm" placeholder="15:00" value="{{ $settings['jam_pulang'] ?? '15:00' }}" required>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="tutup_jam_pulang" class="form-label small fw-semibold">Tutup Jam Pulang</label>
                                    <input type="text" name="tutup_jam_pulang" id="tutup_jam_pulang" class="form-control form-control-sm" placeholder="21:00" value="{{ $settings['tutup_jam_pulang'] ?? '21:00' }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Shift Pagi -->
                        <div class="card p-3 mb-4 border" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                            <h6 class="fw-bold font-heading mb-3 d-flex align-items-center gap-2 text-dark">
                                <span class="badge bg-success-light text-success">2</span>
                                Jam Kerja Shift Pagi (Standar Sekolah)
                            </h6>
                            <div class="row">
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="shift_pagi_masuk" class="form-label small fw-semibold">Buka Jam Masuk</label>
                                    <input type="text" name="shift_pagi_masuk" id="shift_pagi_masuk" class="form-control form-control-sm" placeholder="06:30" value="{{ $settings['shift_pagi_masuk'] ?? '06:30' }}">
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="shift_pagi_terlambat" class="form-label small fw-semibold">Batas Terlambat</label>
                                    <input type="text" name="shift_pagi_terlambat" id="shift_pagi_terlambat" class="form-control form-control-sm" placeholder="07:15" value="{{ $settings['shift_pagi_terlambat'] ?? '07:15' }}">
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="shift_pagi_pulang" class="form-label small fw-semibold">Buka Jam Pulang</label>
                                    <input type="text" name="shift_pagi_pulang" id="shift_pagi_pulang" class="form-control form-control-sm" placeholder="14:30" value="{{ $settings['shift_pagi_pulang'] ?? '14:30' }}">
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="shift_pagi_tutup_pulang" class="form-label small fw-semibold">Tutup Jam Pulang</label>
                                    <input type="text" name="shift_pagi_tutup_pulang" id="shift_pagi_tutup_pulang" class="form-control form-control-sm" placeholder="21:00" value="{{ $settings['shift_pagi_tutup_pulang'] ?? '21:00' }}">
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Shift Siang / Sore -->
                        <div class="card p-3 mb-4 border" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                            <h6 class="fw-bold font-heading mb-3 d-flex align-items-center gap-2 text-dark">
                                <span class="badge bg-warning-light text-warning">3</span>
                                Jam Kerja Shift Siang / Sore (Standar Sekolah)
                            </h6>
                            <div class="row">
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="shift_siang_masuk" class="form-label small fw-semibold">Buka Jam Masuk</label>
                                    <input type="text" name="shift_siang_masuk" id="shift_siang_masuk" class="form-control form-control-sm" placeholder="13:00" value="{{ $settings['shift_siang_masuk'] ?? '13:00' }}">
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="shift_siang_terlambat" class="form-label small fw-semibold">Batas Terlambat</label>
                                    <input type="text" name="shift_siang_terlambat" id="shift_siang_terlambat" class="form-control form-control-sm" placeholder="13:30" value="{{ $settings['shift_siang_terlambat'] ?? '13:30' }}">
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="shift_siang_pulang" class="form-label small fw-semibold">Buka Jam Pulang</label>
                                    <input type="text" name="shift_siang_pulang" id="shift_siang_pulang" class="form-control form-control-sm" placeholder="21:00" value="{{ $settings['shift_siang_pulang'] ?? '21:00' }}">
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="shift_siang_tutup_pulang" class="form-label small fw-semibold">Tutup Jam Pulang</label>
                                    <input type="text" name="shift_siang_tutup_pulang" id="shift_siang_tutup_pulang" class="form-control form-control-sm" placeholder="23:59" value="{{ $settings['shift_siang_tutup_pulang'] ?? '23:59' }}">
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Geofence -->
                        <div class="card p-3 mb-3 border" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                            <h6 class="fw-bold font-heading mb-3 text-dark">Jarak Radius Geofence</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="radius_presensi" class="form-label small fw-semibold">Jarak Geofence Default (Meter)</label>
                                    <input type="number" name="radius_presensi" id="radius_presensi" class="form-control form-control-sm" value="{{ $settings['radius_presensi'] ?? 50 }}" min="10" required>
                                    <small class="text-muted">Radius toleransi default dalam meter untuk wilayah DUDI presensi jika DUDI spesifik tidak diatur.</small>
                                </div>
                            </div>
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
                    <button type="submit" class="btn btn-primary px-4 font-heading" data-loading-text="Menyimpan...">Simpan Konfigurasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
