@extends('layouts.auth')

@section('title', 'Login - PKLku')

@section('content')
<style>
    body.auth-container {
        background: radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.08) 0px, transparent 50%), 
                    radial-gradient(at 50% 0%, rgba(217, 70, 239, 0.05) 0px, transparent 50%), 
                    radial-gradient(at 100% 100%, rgba(79, 70, 229, 0.08) 0px, transparent 50%), 
                    #f8fafc !important;
    }

    .login-card {
        border-radius: 1.25rem;
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.08);
        transition: all 0.3s ease;
        padding: 2.5rem;
    }

    .login-card:hover {
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.12);
        border-color: rgba(79, 70, 229, 0.2);
    }

    .form-control-premium {
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: 1.5px solid var(--border-color);
        background-color: rgba(255, 255, 255, 0.8) !important;
        transition: all 0.2s ease;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .form-control-premium {
            font-size: 16px !important;
            padding-top: 0.7rem !important;
            padding-bottom: 0.7rem !important;
        }
    }

    .form-control-premium:focus {
        border-color: var(--accent-primary);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        background-color: #ffffff !important;
    }

    .btn-premium {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        border: none;
        border-radius: 0.75rem;
        padding: 0.75rem 1.5rem;
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        transition: all 0.2s ease;
    }

    .btn-premium:hover {
        background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3);
        transform: translateY(-1px);
        color: white;
    }

    .btn-premium:active {
        transform: translateY(1px);
    }

    .logo-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 1.25rem;
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(217, 70, 239, 0.1) 100%);
        color: var(--accent-primary);
        box-shadow: 0 8px 16px -4px rgba(79, 70, 229, 0.1);
        margin-bottom: 1.25rem;
    }
</style>

<div class="auth-card">
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="logo-badge">
                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' width="28" height="28"><path d='M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z'></path><path d='M12 11v6'></path><path d='M9 14h6'></path></svg>
            </div>
            <h3 class="fw-bold font-heading m-0" style="color: var(--text-primary); letter-spacing: -0.5px;">PKLku</h3>
            <p class="text-muted mt-1 mb-0" style="font-size: 13px;">Sistem Informasi Praktek Kerja Lapangan</p>
        </div>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label font-heading fw-semibold text-secondary" style="font-size: 13px;">Email Sekolah / Siswa</label>
                <input type="email" name="email" id="email" class="form-control form-control-premium @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="nama@sekolah.sch.id" required autofocus>
                @error('email')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3" x-data="{ show: false }">
                <label for="password" class="form-label font-heading fw-semibold text-secondary" style="font-size: 13px;">Password</label>
                <div class="position-relative">
                    <input :type="show ? 'text' : 'password'" name="password" id="password" class="form-control form-control-premium @error('password') is-invalid @enderror" placeholder="••••••••" style="padding-right: 45px;" required>
                    <button type="button" @click="show = !show" class="position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent text-muted pe-3 d-flex align-items-center justify-content-center" style="outline: none; z-index: 10;" title="Tampilkan/Sembunyikan password">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label text-muted" for="remember" style="font-size: 13px; cursor: pointer;">Ingat Saya</label>
                </div>
            </div>

            <button type="submit" class="btn btn-premium w-100 py-2.5 font-heading fw-semibold">Masuk Sekarang</button>
        </form>
    </div>
    
    <div class="text-center mt-3">
        <small class="text-muted">{{ $globalSettings['footer_login'] ?? ('© ' . date('Y') . ' ' . ($globalSettings['nama_sekolah'] ?? 'SMK Negeri') . '. All rights reserved.') }}</small>
    </div>
</div>
@endsection
