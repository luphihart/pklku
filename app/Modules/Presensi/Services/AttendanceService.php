<?php

namespace App\Modules\Presensi\Services;

use App\Modules\Presensi\Repositories\AttendanceRepositoryInterface;
use App\Modules\Setting\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AttendanceService
{
    protected $repo;

    public function __construct(AttendanceRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getHistory(int $placementId) { return $this->repo->getStudentAttendanceHistory($placementId); }
    public function getToday(int $placementId) { return $this->repo->getTodayAttendance($placementId); }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     * Returns distance in meters.
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // 6,371,000 meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Helper to verify if today is a configured working day.
     */
    private function verifyWorkingDay(int $placementId): void
    {
        $placement = \App\Modules\PKL\Models\PenempatanPkl::with('dudi')->find($placementId);
        $hariKerja = ($placement && $placement->dudi && $placement->dudi->hari_kerja)
            ? $placement->dudi->hari_kerja
            : (Setting::where('key', 'hari_kerja')->value('value') ?: 'Senin,Selasa,Rabu,Kamis,Jumat');
            
        $allowedDays = array_map('trim', explode(',', $hariKerja));
        
        $daysMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        
        $currentDayNameIndo = $daysMap[now()->format('l')];

        if (!in_array($currentDayNameIndo, $allowedDays)) {
            throw new \Exception("Presensi gagal! Hari ini (" . $currentDayNameIndo . ") adalah hari libur.");
        }
    }

    /**
     * Process Check In (processes camera selfie, geofence, compression, watermark).
     */
    public function checkIn(int $placementId, float $lat, float $lng, string $photoBase64)
    {
        // 0. Verify Working Day
        $this->verifyWorkingDay($placementId);

        // 0.1. Verify Check-in Time Window
        $jamMasukLimit = Setting::where('key', 'jam_masuk')->value('value') ?: '07:30';
        $nowTime = now()->toTimeString();
        
        $startTime = $jamMasukLimit;
        $endTime = '11:59:59';

        if ($nowTime < $startTime) {
            throw new \Exception("Presensi Check-In belum dibuka! Presensi dibuka mulai pukul " . substr($startTime, 0, 5) . ".");
        }

        if ($nowTime > $endTime) {
            throw new \Exception("Batas waktu presensi Check-In telah berakhir (Pukul " . substr($endTime, 0, 5) . ").");
        }

        $placement = \App\Modules\PKL\Models\PenempatanPkl::with(['dudi', 'murid'])->findOrFail($placementId);
        
        // 1. Verify Geofence (Prioritise DUDI specific radius)
        $distance = $this->calculateDistance($lat, $lng, $placement->dudi->latitude, $placement->dudi->longitude);
        $allowedRadius = $placement->dudi->radius_meter ?: (int)Setting::where('key', 'radius_presensi')->value('value');
        if (!$allowedRadius) {
            $allowedRadius = 100; // ultimate fallback
        }

        if ($distance > $allowedRadius) {
            throw new \Exception("Presensi gagal! Anda berada di luar radius wilayah DUDI (" . round($distance) . " meter dari lokasi, batas radius: " . $allowedRadius . " meter).");
        }

        // 2. Decode, Compress & Watermark Photo (cPanel safe direct public path writing)
        $filename = 'checkin_' . $placementId . '_' . time() . '.jpg';
        $fullPath = public_path('storage/attendance/' . $filename);
        if (!file_exists(public_path('storage/attendance'))) {
            mkdir(public_path('storage/attendance'), 0777, true);
        }

        $this->processSelfiePhoto(
            $photoBase64, 
            $fullPath, 
            $lat, 
            $lng, 
            'CHECK IN', 
            $placement->murid->nama, 
            $placement->dudi->nama
        );

        // 3. Determine status (Tepat Waktu if within 30 minutes of start time)
        $lateLimit = date('H:i:s', strtotime($jamMasukLimit . ' +30 minutes'));
        $statusMasuk = $nowTime <= $lateLimit ? 'tepat_waktu' : 'terlambat';

        // 4. Save to DB
        return $this->repo->saveAttendance([
            'penempatan_pkl_id' => $placementId,
            'tanggal' => now()->toDateString(),
            'jam_masuk' => $nowTime,
            'lat_masuk' => $lat,
            'lng_masuk' => $lng,
            'foto_masuk' => $filename,
            'status_masuk' => $statusMasuk,
        ]);
    }

    /**
     * Process Check Out.
     */
    public function checkOut(int $placementId, float $lat, float $lng, string $photoBase64)
    {
        // 0. Verify Working Day
        $this->verifyWorkingDay($placementId);

        // 0.1. Verify Check-out Time Window
        $jamPulangLimit = Setting::where('key', 'jam_pulang')->value('value') ?: '16:00';
        $nowTime = now()->toTimeString();

        if ($nowTime < $jamPulangLimit) {
            throw new \Exception("Presensi Check-Out belum dibuka! Presensi dibuka mulai pukul " . substr($jamPulangLimit, 0, 5) . ".");
        }

        $attendance = $this->getToday($placementId);
        if (!$attendance) {
            throw new \Exception("Presensi gagal! Anda belum melakukan Check In hari ini.");
        }

        if ($attendance->jam_pulang) {
            throw new \Exception("Anda sudah melakukan Check Out hari ini.");
        }

        $placement = \App\Modules\PKL\Models\PenempatanPkl::with(['dudi', 'murid'])->findOrFail($placementId);
        
        // 1. Verify Geofence (Prioritise DUDI specific radius)
        $distance = $this->calculateDistance($lat, $lng, $placement->dudi->latitude, $placement->dudi->longitude);
        $allowedRadius = $placement->dudi->radius_meter ?: (int)Setting::where('key', 'radius_presensi')->value('value');
        if (!$allowedRadius) {
            $allowedRadius = 100; // ultimate fallback
        }

        if ($distance > $allowedRadius) {
            throw new \Exception("Presensi gagal! Anda berada di luar radius wilayah DUDI (" . round($distance) . " meter dari lokasi, batas radius: " . $allowedRadius . " meter).");
        }

        // 2. Decode, Compress & Watermark Photo (cPanel safe direct public path writing)
        $filename = 'checkout_' . $placementId . '_' . time() . '.jpg';
        $fullPath = public_path('storage/attendance/' . $filename);
        if (!file_exists(public_path('storage/attendance'))) {
            mkdir(public_path('storage/attendance'), 0777, true);
        }

        $this->processSelfiePhoto(
            $photoBase64, 
            $fullPath, 
            $lat, 
            $lng, 
            'CHECK OUT', 
            $placement->murid->nama, 
            $placement->dudi->nama
        );

        // 3. Determine status (Pulang Cepat / Tepat Waktu)
        $jamPulangLimit = Setting::where('key', 'jam_pulang')->value('value') ?: '16:00';
        $statusPulang = $nowTime >= $jamPulangLimit ? 'tepat_waktu' : 'pulang_cepat';

        // 4. Update DB
        return $this->repo->updateAttendance($attendance->id, [
            'jam_pulang' => $nowTime,
            'lat_pulang' => $lat,
            'lng_pulang' => $lng,
            'foto_pulang' => $filename,
            'status_pulang' => $statusPulang,
        ]);
    }

    /**
     * Helper to decode base64 canvas image, resize it, and write watermark.
     */
    private function processSelfiePhoto(
        string $base64Data, 
        string $outputPath, 
        float $lat, 
        float $lng, 
        string $type, 
        string $studentName, 
        string $dudiName
    ): void
    {
        // Clean base64 header
        $imgData = preg_replace('#^data:image/\w+;base64,#i', '', $base64Data);
        $imgData = base64_decode($imgData);

        // Save temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'selfie');
        file_put_contents($tempFile, $imgData);

        // Process with Intervention Image or Native GD
        try {
            if (class_exists(ImageManager::class)) {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($tempFile);
                
                // Resize to max 640 for bandwidth/storage saving
                $image->scale(width: 640);
                
                // Save compressed image
                $image->toJpeg(75)->save($outputPath);

                // Add Watermark via Native GD (safer than using intervention v3 text drivers which require gd extra packages)
                $this->addWatermarkNative($outputPath, $type, $studentName, $dudiName, $lat, $lng);
            } else {
                $this->compressImageNative($tempFile, $outputPath, 640, 480, 75);
                $this->addWatermarkNative($outputPath, $type, $studentName, $dudiName, $lat, $lng);
            }
        } catch (\Throwable $e) {
            // ultimate fallback: save directly
            file_put_contents($outputPath, $imgData);
        }

        @unlink($tempFile);
    }

    /**
     * Draw text watermark on the bottom of the photo.
     */
    private function addWatermarkNative(
        string $path, 
        string $type, 
        string $studentName, 
        string $dudiName, 
        float $lat, 
        float $lng
    ): void
    {
        $im = imagecreatefromjpeg($path);
        if (!$im) return;

        $white = imagecolorallocate($im, 255, 255, 255);
        
        // Semi-transparent black background overlay
        $bg = imagecolorallocatealpha($im, 0, 0, 0, 45); // Alpha 45 is approx 35% opacity

        $h = imagesy($im);
        $w = imagesx($im);

        // Draw a dark overlay band at the bottom (65 pixels height)
        imagefilledrectangle($im, 0, $h - 65, $w, $h, $bg);

        // Standard internal GD font (3 = medium font)
        $font = 3; 

        // Line 1: Type & Date
        $line1 = sprintf("[%s] - %s", $type, now()->format('d F Y - H:i:s'));
        // Line 2: Student & DUDI Info
        $line2 = sprintf("Siswa: %s | DUDI: %s", $studentName, $dudiName);
        // Line 3: GPS Coordinates
        $line3 = sprintf("Koordinat GPS: %s, %s", round($lat, 6), round($lng, 6));

        // Draw the 3 lines of text with clean padding
        imagestring($im, $font, 12, $h - 55, $line1, $white);
        imagestring($im, $font, 12, $h - 38, $line2, $white);
        imagestring($im, $font, 12, $h - 21, $line3, $white);

        imagejpeg($im, $path, 85);
        imagedestroy($im);
    }

    private function compressImageNative(string $sourcePath, string $destPath, int $width, int $height, int $quality): void
    {
        list($origWidth, $origHeight, $type) = getimagesize($sourcePath);
        
        switch ($type) {
            case IMAGETYPE_JPEG: $srcImg = imagecreatefromjpeg($sourcePath); break;
            case IMAGETYPE_PNG: $srcImg = imagecreatefrompng($sourcePath); break;
            default: $srcImg = imagecreatefromjpeg($sourcePath); break;
        }

        $destImg = imagecreatetruecolor($width, $height);
        imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);
        imagejpeg($destImg, $destPath, $quality);

        imagedestroy($srcImg);
        imagedestroy($destImg);
    }
}
