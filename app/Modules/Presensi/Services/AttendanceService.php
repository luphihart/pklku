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
    public function getTodayLeave(int $placementId) { return $this->repo->getTodayApprovedLeave($placementId); }

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

        // 1. Check Placement-specific custom day off (or DUDI/global working days fallback)
        if ($placement && $placement->isPlacementHoliday()) {
            throw new \Exception("Presensi ditutup! Hari ini (" . $currentDayNameIndo . ") adalah jadwal hari libur Anda.");
        }

        // 2. Check National Holiday
        $today = now()->toDateString();
        $holiday = \App\Modules\MasterData\Models\HariLibur::getHoliday($today);
        if ($holiday) {
            throw new \Exception("Presensi ditutup! Hari ini adalah hari libur: " . $holiday->nama . ".");
        }
    }

    /**
     * Process Check In (processes camera selfie, geofence, compression, watermark).
     */
    public function checkIn(int $placementId, float $lat, float $lng, string $photoBase64)
    {
        // 0. Verify Working Day
        $this->verifyWorkingDay($placementId);

        // 0.0. Verify Student Has Not Already Checked In Today
        $existing = $this->getToday($placementId);
        if ($existing && $existing->jam_masuk) {
            throw new \Exception("Anda sudah melakukan Check In hari ini.");
        }

        // 0.1. Fetch Placement and Determine Shift Hours
        $placement = \App\Modules\PKL\Models\PenempatanPkl::with(['dudi', 'murid'])->findOrFail($placementId);
        $nowTime = now()->toTimeString();
        $detectedShift = $placement->tipe_shift ?? 'reguler';

        if ($placement->tipe_shift === 'rolling') {
            $settings = Setting::whereIn('key', [
                'shift_pagi_masuk', 'shift_pagi_terlambat', 'shift_pagi_pulang', 'shift_pagi_tutup_pulang',
                'shift_siang_masuk', 'shift_siang_terlambat', 'shift_siang_pulang', 'shift_siang_tutup_pulang',
                'shift_sore_masuk', 'shift_sore_terlambat', 'shift_sore_pulang', 'shift_sore_tutup_pulang'
            ])->pluck('value', 'key');

            $pagiMasuk       = ($settings->get('shift_pagi_masuk') ?: '06:30') . ':00';
            $pagiTerlambat   = ($settings->get('shift_pagi_terlambat') ?: '07:15') . ':00';
            $pagiTutupPulang = ($settings->get('shift_pagi_tutup_pulang') ?: '21:00') . ':00';

            $siangMasuk       = ($settings->get('shift_siang_masuk') ?: '11:00') . ':00';
            $siangTerlambat   = ($settings->get('shift_siang_terlambat') ?: '11:30') . ':00';
            $siangTutupPulang = ($settings->get('shift_siang_tutup_pulang') ?: '22:00') . ':59';

            $hasSore = $settings->has('shift_sore_masuk') && !empty($settings->get('shift_sore_masuk'));
            $soreMasuk       = ($settings->get('shift_sore_masuk') ?: '15:00') . ':00';
            $soreTerlambat   = ($settings->get('shift_sore_terlambat') ?: '15:30') . ':00';
            $soreTutupPulang = ($settings->get('shift_sore_tutup_pulang') ?: '23:59') . ':59';

            if ($nowTime >= $pagiMasuk && $nowTime < $siangMasuk) {
                $detectedShift = 'pagi';
                $batasTerlambat = $pagiTerlambat;
                $shiftLabel = 'Shift Pagi';
            } elseif ($hasSore && $nowTime >= $siangMasuk && $nowTime < $soreMasuk) {
                $detectedShift = 'siang';
                $batasTerlambat = $siangTerlambat;
                $shiftLabel = 'Shift Siang';
            } elseif ($hasSore && $nowTime >= $soreMasuk && $nowTime <= $soreTutupPulang) {
                $detectedShift = 'sore';
                $batasTerlambat = $soreTerlambat;
                $shiftLabel = 'Shift Sore';
            } elseif (!$hasSore && $nowTime >= $siangMasuk && $nowTime <= $siangTutupPulang) {
                $detectedShift = 'siang';
                $batasTerlambat = $siangTerlambat;
                $shiftLabel = 'Shift Siang';
            } else {
                throw new \Exception("Presensi Check-In ditolak! Jam saat ini (" . substr($nowTime, 0, 5) . ") berada di luar jadwal buka shift yang ditentukan.");
            }
        } else {
            $shiftHours = $placement->getEffectiveShiftHours();
            $jamMasukLimit  = $shiftHours['jam_masuk'] . ':00';
            $batasTerlambat = $shiftHours['batas_terlambat'] . ':00';
            $tutupJamPulang = $shiftHours['tutup_jam_pulang'] . ':00';
            $shiftLabel     = $shiftHours['label'];

            if ($nowTime < $jamMasukLimit) {
                throw new \Exception("Presensi Check-In belum dibuka! Presensi " . $shiftLabel . " dibuka mulai pukul " . substr($jamMasukLimit, 0, 5) . ".");
            }

            if ($nowTime > $tutupJamPulang) {
                throw new \Exception("Batas waktu presensi untuk " . $shiftLabel . " telah berakhir (Pukul " . substr($tutupJamPulang, 0, 5) . ").");
            }
        }

        $settings = Setting::whereIn('key', ['radius_presensi'])->pluck('value', 'key');
        
        $isWfaToday = $placement->isWfaToday();

        // 1. Verify Geofence (Prioritise DUDI specific radius) if NOT WFA
        if (!$isWfaToday) {
            $distance = $this->calculateDistance($lat, $lng, $placement->dudi->latitude, $placement->dudi->longitude);
            $allowedRadius = $placement->dudi->radius_meter ?: (int)$settings->get('radius_presensi', 100);
            if (!$allowedRadius) {
                $allowedRadius = 100; // ultimate fallback
            }

            if ($distance > $allowedRadius) {
                throw new \Exception("Presensi gagal! Hari ini adalah jadwal WFO dan Anda berada di luar radius wilayah DUDI (" . round($distance) . " meter dari lokasi, batas radius: " . $allowedRadius . " meter).");
            }
        }

        // 2. Decode, Compress & Watermark Photo (cPanel safe direct public path writing)
        $filename = 'checkin_' . $placementId . '_' . time() . '.jpg';
        $fullPath = public_path('storage/attendance/' . $filename);
        if (!file_exists(public_path('storage/attendance'))) {
            mkdir(public_path('storage/attendance'), 0777, true);
        }

        $watermarkType = 'CHECK IN' . ($isWfaToday ? ' (WFA)' : ' (WFO)');

        $this->processSelfiePhoto(
            $photoBase64, 
            $fullPath, 
            $lat, 
            $lng, 
            $watermarkType, 
            $placement->murid->nama, 
            $placement->dudi->nama
        );

        // 3. Determine status (Tepat Waktu if before/equal to batasTerlambat)
        $statusMasuk = $nowTime <= $batasTerlambat ? 'tepat_waktu' : 'terlambat';

        // 4. Save to DB
        return $this->repo->saveAttendance([
            'penempatan_pkl_id' => $placementId,
            'tanggal' => now()->toDateString(),
            'jam_masuk' => $nowTime,
            'lat_masuk' => $lat,
            'lng_masuk' => $lng,
            'foto_masuk' => $filename,
            'status_masuk' => $statusMasuk,
            'shift_harian' => $detectedShift,
            'is_wfa' => $isWfaToday ? 1 : 0,
        ]);
    }

    /**
     * Process Check Out.
     */
    public function checkOut(int $placementId, float $lat, float $lng, string $photoBase64)
    {
        // 0. Verify Working Day
        $this->verifyWorkingDay($placementId);

        // 0.1. Check existing check-in
        $attendance = $this->getToday($placementId);
        if (!$attendance || !$attendance->jam_masuk) {
            throw new \Exception("Presensi gagal! Anda belum melakukan Check In hari ini.");
        }

        if ($attendance->jam_pulang) {
            throw new \Exception("Anda sudah melakukan Check Out hari ini.");
        }

        // 0.2. Fetch Placement and Effective Shift Hours
        $placement = \App\Modules\PKL\Models\PenempatanPkl::with(['dudi', 'murid'])->findOrFail($placementId);
        $recordedShift = $attendance->shift_harian ?: ($placement->tipe_shift ?? 'reguler');
        $shiftHours = $placement->getEffectiveShiftHours($recordedShift);

        $jamPulangLimit = $shiftHours['jam_pulang'] . ':00';
        $tutupJamPulang = $shiftHours['tutup_jam_pulang'] . ':00';
        $nowTime        = now()->toTimeString();

        if ($nowTime > $tutupJamPulang) {
            throw new \Exception("Batas waktu presensi Check-Out untuk " . $shiftHours['label'] . " telah berakhir (Pukul " . substr($tutupJamPulang, 0, 5) . ").");
        }

        $settings = Setting::whereIn('key', ['radius_presensi'])->pluck('value', 'key');
        
        $isWfaToday = $placement->isWfaToday();

        // 1. Verify Geofence (Prioritise DUDI specific radius) if NOT WFA
        if (!$isWfaToday) {
            $distance = $this->calculateDistance($lat, $lng, $placement->dudi->latitude, $placement->dudi->longitude);
            $allowedRadius = $placement->dudi->radius_meter ?: (int)$settings->get('radius_presensi', 100);
            if (!$allowedRadius) {
                $allowedRadius = 100; // ultimate fallback
            }

            if ($distance > $allowedRadius) {
                throw new \Exception("Presensi gagal! Hari ini adalah jadwal WFO dan Anda berada di luar radius wilayah DUDI (" . round($distance) . " meter dari lokasi, batas radius: " . $allowedRadius . " meter).");
            }
        }

        // 2. Decode, Compress & Watermark Photo (cPanel safe direct public path writing)
        $filename = 'checkout_' . $placementId . '_' . time() . '.jpg';
        $fullPath = public_path('storage/attendance/' . $filename);
        if (!file_exists(public_path('storage/attendance'))) {
            mkdir(public_path('storage/attendance'), 0777, true);
        }

        $watermarkType = 'CHECK OUT' . ($isWfaToday ? ' (WFA)' : ' (WFO)');

        $this->processSelfiePhoto(
            $photoBase64, 
            $fullPath, 
            $lat, 
            $lng, 
            $watermarkType, 
            $placement->murid->nama, 
            $placement->dudi->nama
        );

        // 3. Determine status (Pulang Cepat if before jamPulangLimit, Tepat Waktu if on/after jamPulangLimit)
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
                $this->compressImageNative($tempFile, $outputPath, 640, 75);
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

    private function compressImageNative(string $sourcePath, string $destPath, int $maxWidth, int $quality): void
    {
        list($origWidth, $origHeight, $type) = getimagesize($sourcePath);
        
        $width = $origWidth;
        $height = $origHeight;

        // Proportional scaling if larger than max width
        if ($origWidth > $maxWidth) {
            $width = $maxWidth;
            $height = (int)($origHeight * ($maxWidth / $origWidth));
        }

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
