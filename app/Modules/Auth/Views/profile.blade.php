@extends('layouts.admin')

@section('title', 'Profil Saya - PKLku')
@section('page_title', 'Profil Saya')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <!-- Profile Photo Card -->
        <div class="col-md-4 mb-4">
            <div class="card-premium text-center">
                <div class="position-relative d-inline-block mb-3">
                    <img src="{{ $user->photo ? asset('storage/profiles/' . $user->photo) : 'https://www.gravatar.com/avatar/' . md5($user->email) . '?d=mp&s=150' }}" 
                         alt="Foto Profil" 
                         class="rounded-circle border" 
                         width="150" 
                         height="150" 
                         style="object-fit: cover;">
                </div>
                <h5 class="fw-bold font-heading m-0">{{ $user->name }}</h5>
                <p class="text-uppercase text-muted mb-3" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">{{ $user->role }}</p>
                <p class="text-muted small mb-0">{{ $user->email }}</p>
                
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                    @csrf
                    <div class="mb-3">
                        <input type="file" name="photo" id="photoInput" class="form-control form-control-sm @error('photo') is-invalid @enderror" accept="image/*" required>
                        @error('photo')
                            <div class="invalid-feedback text-start">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100 font-heading">Unggah Foto Baru</button>
                </form>
            </div>
        </div>

        <!-- Account Settings Card -->
        <div class="col-md-8 mb-4">
            <div class="card-premium">
                <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Informasi Akun & Keamanan</h5>
                
                <form action="{{ route('profile.update') }}" method="POST" class="mb-4 pb-3 border-bottom" style="border-bottom-color: var(--border-color) !important;">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label class="form-label text-muted small text-uppercase fw-semibold">Nama Lengkap <span class="text-danger" style="font-size: 10px;">(Terkunci)</span></label>
                            <input type="text" class="form-control form-control-sm" value="{{ $user->name }}" readonly style="background-color: var(--bg-canvas); cursor: not-allowed;" title="Nama lengkap terkunci">
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label text-muted small text-uppercase fw-semibold">Email Pengguna <span class="text-danger" style="font-size: 10px;">(Terkunci)</span></label>
                            <input type="text" class="form-control form-control-sm" value="{{ $user->email }}" readonly style="background-color: var(--bg-canvas); cursor: not-allowed;" title="Email terkunci">
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label for="phone" class="form-label small fw-semibold">Nomor Telepon</label>
                            <input type="text" name="phone" id="phone" class="form-control form-control-sm" value="{{ $user->phone }}" placeholder="Masukkan No. Telp / WhatsApp">
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label for="tanggal_lahir" class="form-label small fw-semibold">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control form-control-sm" value="{{ $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label text-muted small text-uppercase fw-semibold">Hak Akses Role <span class="text-danger" style="font-size: 10px;">(Terkunci)</span></label>
                            <input type="text" class="form-control form-control-sm text-uppercase" value="{{ $user->role }}" readonly style="background-color: var(--bg-canvas); cursor: not-allowed;" title="Hak akses terkunci">
                        </div>
                    </div>
                    <div class="text-end mt-2">
                        <button type="submit" class="btn btn-sm btn-primary font-heading">Simpan Perubahan</button>
                    </div>
                </form>

                <h5 class="fw-bold font-heading mb-3 pb-2 border-bottom" style="border-bottom-color: var(--border-color) !important;">Ubah Password Keamanan</h5>
                
                <form action="{{ route('profile.update') }}" method="POST" x-data="{ showCurrent: false, showNew: false }">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label small fw-semibold">Password Saat Ini</label>
                        <div class="input-group input-group-sm">
                            <input :type="showCurrent ? 'text' : 'password'" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Masukkan password Anda saat ini">
                            <button class="btn btn-outline-secondary" type="button" @click="showCurrent = !showCurrent">
                                <span x-text="showCurrent ? 'Sembunyikan' : 'Tampilkan'"></span>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label for="new_password" class="form-label small fw-semibold">Password Baru</label>
                            <div class="input-group input-group-sm">
                                <input :type="showNew ? 'text' : 'password'" name="new_password" id="new_password" class="form-control @error('new_password') is-invalid @enderror" placeholder="Minimal 6 karakter">
                                <button class="btn btn-outline-secondary" type="button" @click="showNew = !showNew">
                                    <span x-text="showNew ? 'Sembunyikan' : 'Tampilkan'"></span>
                                </button>
                            </div>
                            @error('new_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label for="new_password_confirmation" class="form-label small fw-semibold">Konfirmasi Password Baru</label>
                            <div class="input-group input-group-sm">
                                <input :type="showNew ? 'text' : 'password'" name="new_password_confirmation" id="new_password_confirmation" class="form-control" placeholder="Ulangi password baru">
                                <button class="btn btn-outline-secondary" type="button" @click="showNew = !showNew">
                                    <span x-text="showNew ? 'Sembunyikan' : 'Tampilkan'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary px-4 mt-2 font-heading">Simpan Password Baru</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
