<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Handle user login.
     */
    public function login(array $credentials, bool $remember = false): bool
    {
        if (Auth::attempt($credentials, $remember)) {
            // Log audit for successful login
            $this->logActivity('Login Sukses');
            return true;
        }

        // Log audit for failed login attempt
        $this->logActivity('Gagal Login dengan email: ' . ($credentials['email'] ?? ''));

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(): void
    {
        $this->logActivity('Logout');
        Auth::logout();
    }

    /**
     * Update user profile password.
     */
    public function updatePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userRepository->findById($userId);

        if (!$user || !Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini salah.'],
            ]);
        }

        $updated = $this->userRepository->update($userId, [
            'password' => Hash::make($newPassword),
        ]);

        if ($updated) {
            $this->logActivity('Mengubah Password', $userId);
        }

        return $updated;
    }

    /**
     * Update user profile photo with compression.
     */
    public function updatePhoto(int $userId, $photoFile): string
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \Exception('Pengguna tidak ditemukan.');
        }

        // Generate filename
        $filename = 'profile_' . $userId . '_' . time() . '.' . $photoFile->getClientOriginalExtension();
        $directory = 'profiles';
        $dirPath = public_path('storage/' . $directory);

        // Ensure directory exists in public path
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0755, true);
        }
        $fullPath = $dirPath . '/' . $filename;

        // Perform compression using Intervention Image if available, otherwise fallback to move
        try {
            if (class_exists(\Intervention\Image\ImageManager::class)) {
                // Intervention Image v3 API
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $image = $manager->read($photoFile->getRealPath());
                
                // Resize to max 400x400 while maintaining aspect ratio
                $image->cover(400, 400); 
                
                // Save to path
                $image->toJpeg(80)->save($fullPath);
            } elseif (class_exists(\Intervention\Image\Facades\Image::class) || class_exists(\Intervention\Image\Image::class)) {
                // Intervention Image v2 API fallback
                $img = \Intervention\Image\Facades\Image::make($photoFile->getRealPath());
                $img->fit(400, 400);
                $img->save($fullPath, 80);
            } else {
                // Fallback GD library native code
                $this->compressImageNative($photoFile->getRealPath(), $fullPath, 400, 400, 80);
            }
        } catch (\Throwable $e) {
            // Ultimate fallback: save original file
            $photoFile->move($dirPath, $filename);
        }

        // Delete old photo if exists
        if ($user->photo && file_exists($dirPath . '/' . $user->photo)) {
            @unlink($dirPath . '/' . $user->photo);
        }

        // Save to DB
        $this->userRepository->update($userId, [
            'photo' => $filename,
        ]);

        $this->logActivity('Memperbarui Foto Profil', $userId);

        return $filename;
    }

    /**
     * Native GD Image compression fallback.
     */
    private function compressImageNative(string $sourcePath, string $destPath, int $width, int $height, int $quality): void
    {
        list($origWidth, $origHeight, $type) = getimagesize($sourcePath);
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                $srcImg = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $srcImg = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $srcImg = imagecreatefromgif($sourcePath);
                break;
            default:
                throw new \Exception('Tipe gambar tidak didukung.');
        }

        // Crop to square
        $squareSize = min($origWidth, $origHeight);
        $xOffset = ($origWidth - $squareSize) / 2;
        $yOffset = ($origHeight - $squareSize) / 2;

        $destImg = imagecreatetruecolor($width, $height);
        
        // Preserve transparency for PNGs
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($destImg, false);
            imagesavealpha($destImg, true);
        }

        imagecopyresampled(
            $destImg, $srcImg,
            0, 0,
            $xOffset, $yOffset,
            $width, $height,
            $squareSize, $squareSize
        );

        imagejpeg($destImg, $destPath, $quality);

        imagedestroy($srcImg);
        imagedestroy($destImg);
    }

    /**
     * Audit logger helper.
     */
    private function logActivity(string $aktivitas, ?int $userId = null): void
    {
        $uId = $userId ?? Auth::id();
        
        // Use try-catch so auth works even if AuditLog module is not fully loaded
        try {
            \App\Modules\System\Models\AuditLog::create([
                'user_id' => $uId,
                'aktivitas' => $aktivitas,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Unknown',
                'payload' => null,
            ]);
        } catch (\Throwable $e) {
            // Ignore if audit_logs table does not exist or isn't migrated
        }
    }
}
