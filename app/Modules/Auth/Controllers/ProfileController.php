<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * View user profile.
     */
    public function index()
    {
        return view('auth::profile', [
            'user' => auth()->user()
        ]);
    }

    /**
     * Update user profile password or photo.
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        $hasChanges = false;

        // 1. Update Password
        if ($request->filled('current_password')) {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ], [
                'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
                'new_password.min' => 'Password baru minimal 6 karakter.',
            ]);

            $this->authService->updatePassword($user->id, $request->current_password, $request->new_password);
            $hasChanges = true;
        }

        // 2. Update Profile Picture
        if ($request->hasFile('photo')) {
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ], [
                'photo.max' => 'Ukuran foto maksimal 2MB.',
                'photo.mimes' => 'Format foto harus JPEG, JPG, atau PNG.',
            ]);

            $this->authService->updatePhoto($user->id, $request->file('photo'));
            $hasChanges = true;
        }

        // 3. Update Phone Number & Birthday
        if ($request->has('phone') || $request->has('tanggal_lahir')) {
            $request->validate([
                'phone' => 'nullable|string|max:20',
                'tanggal_lahir' => 'nullable|date',
            ]);
            
            $updateData = [];
            if ($request->has('phone') && $user->phone !== $request->phone) {
                $updateData['phone'] = $request->phone ?: null;
            }
            if ($request->has('tanggal_lahir') && ($user->tanggal_lahir?->format('Y-m-d') !== $request->tanggal_lahir)) {
                $updateData['tanggal_lahir'] = $request->tanggal_lahir ?: null;
            }

            if (!empty($updateData)) {
                $user->update($updateData);
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            return back()->with('success', 'Profil berhasil diperbarui.');
        }

        return back()->with('info', 'Tidak ada perubahan data.');
    }
}
