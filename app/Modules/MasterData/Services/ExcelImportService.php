<?php

namespace App\Modules\MasterData\Services;

use App\Models\User;
use App\Modules\MasterData\Models\Murid;
use App\Modules\MasterData\Models\Guru;
use App\Modules\MasterData\Models\Dudi;
use App\Modules\MasterData\Models\Kelas;
use App\Modules\MasterData\Models\TahunAjaran;
use App\Modules\PKL\Models\PenempatanPkl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelImportService
{
    /**
     * Import Murid with transaksional rollback.
     */
    public function importMurid($filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Check if there is data (row 0 is header)
        if (count($rows) <= 1) {
            throw new \Exception('File Excel kosong atau tidak memiliki data.');
        }

        DB::transaction(function() use ($rows) {
            $processedEmails = [];
            $processedNis = [];

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip header

                $nama = trim($row[0] ?? '');
                $email = trim($row[1] ?? '');
                $phone = trim($row[2] ?? '');
                $nis = trim($row[3] ?? '');
                $kelasNama = trim($row[4] ?? '');
                $excelPassword = trim($row[5] ?? '');
                $tanggalLahir = trim($row[6] ?? '');

                if (empty($nama) || empty($email) || empty($nis) || empty($kelasNama)) {
                    throw new \Exception("Baris " . ($index + 1) . ": Nama, Email, NIS, dan Kelas wajib diisi.");
                }

                // Check in-memory duplicates
                $emailLower = strtolower($email);
                if (in_array($emailLower, $processedEmails)) {
                    throw new \Exception("Baris " . ($index + 1) . ": Email '{$email}' duplikat di dalam file Excel.");
                }
                if (in_array($nis, $processedNis)) {
                    throw new \Exception("Baris " . ($index + 1) . ": NIS '{$nis}' duplikat di dalam file Excel.");
                }

                // Verify Kelas exists
                $kelas = Kelas::where('nama', $kelasNama)->first();
                if (!$kelas) {
                    throw new \Exception("Baris " . ($index + 1) . ": Kelas '{$kelasNama}' tidak ditemukan di database.");
                }

                // Track in memory
                $processedEmails[] = $emailLower;
                $processedNis[] = $nis;

                // Upsert logic (including soft-deleted)
                $existingMurid = Murid::withTrashed()->where('nis', $nis)->first();
                $existingUser = User::withTrashed()->where('email', $email)->first();

                if ($existingMurid) {
                    if ($existingMurid->trashed()) {
                        $existingMurid->restore();
                    }
                    $user = $existingMurid->user()->withTrashed()->first();
                    if ($user) {
                        if ($user->trashed()) {
                            $user->restore();
                        }
                        $userData = [
                            'name' => $nama,
                            'email' => $email,
                            'phone' => $phone ?: null,
                            'tanggal_lahir' => $tanggalLahir ?: null,
                        ];
                        if (!empty($excelPassword)) {
                            $userData['password'] = Hash::make($excelPassword);
                        }
                        $user->update($userData);
                    }
                    $existingMurid->update([
                        'nama' => $nama,
                        'kelas_id' => $kelas->id,
                    ]);
                } elseif ($existingUser) {
                    if ($existingUser->trashed()) {
                        $existingUser->restore();
                    }
                    $existingMuridByUser = Murid::withTrashed()->where('user_id', $existingUser->id)->first();
                    if ($existingMuridByUser) {
                        if ($existingMuridByUser->trashed()) {
                            $existingMuridByUser->restore();
                        }
                        $userData = [
                            'name' => $nama,
                            'phone' => $phone ?: null,
                            'tanggal_lahir' => $tanggalLahir ?: null,
                        ];
                        if (!empty($excelPassword)) {
                            $userData['password'] = Hash::make($excelPassword);
                        }
                        $existingUser->update($userData);
                        $existingMuridByUser->update([
                            'nis' => $nis,
                            'nama' => $nama,
                            'kelas_id' => $kelas->id,
                        ]);
                    } else {
                        $userData = [
                            'name' => $nama,
                            'phone' => $phone ?: null,
                            'tanggal_lahir' => $tanggalLahir ?: null,
                        ];
                        if (!empty($excelPassword)) {
                            $userData['password'] = Hash::make($excelPassword);
                        }
                        $existingUser->update($userData);
                        Murid::create([
                            'user_id' => $existingUser->id,
                            'nis' => $nis,
                            'nama' => $nama,
                            'kelas_id' => $kelas->id,
                        ]);
                    }
                } else {
                    $user = User::create([
                        'name' => $nama,
                        'email' => $email,
                        'tanggal_lahir' => $tanggalLahir ?: null,
                        'password' => Hash::make($excelPassword ?: 'siswa123'),
                        'role' => 'murid',
                        'phone' => $phone ?: null,
                    ]);

                    Murid::create([
                        'user_id' => $user->id,
                        'nis' => $nis,
                        'nama' => $nama,
                        'kelas_id' => $kelas->id,
                    ]);
                }
            }
        });
    }

    /**
     * Import Guru with transaksional rollback.
     */
    public function importGuru($filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) <= 1) {
            throw new \Exception('File Excel kosong atau tidak memiliki data.');
        }

        DB::transaction(function() use ($rows) {
            $processedEmails = [];
            $processedNips = [];

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip header

                $nama = trim($row[0] ?? '');
                $email = trim($row[1] ?? '');
                $phone = trim($row[2] ?? '');
                $nip = trim($row[3] ?? '');
                $excelPassword = trim($row[4] ?? '');
                $tanggalLahir = trim($row[5] ?? '');

                if (empty($nama) || empty($email)) {
                    throw new \Exception("Baris " . ($index + 1) . ": Nama dan Email wajib diisi.");
                }

                // Check in-memory duplicates
                $emailLower = strtolower($email);
                if (in_array($emailLower, $processedEmails)) {
                    throw new \Exception("Baris " . ($index + 1) . ": Email '{$email}' duplikat di dalam file Excel.");
                }
                if (!empty($nip) && in_array($nip, $processedNips)) {
                    throw new \Exception("Baris " . ($index + 1) . ": NIP '{$nip}' duplikat di dalam file Excel.");
                }

                // Track
                $processedEmails[] = $emailLower;
                if (!empty($nip)) {
                    $processedNips[] = $nip;
                }

                // Upsert logic (including soft-deleted)
                $existingGuru = null;
                if (!empty($nip)) {
                    $existingGuru = Guru::withTrashed()->where('nip', $nip)->first();
                }
                $existingUser = User::withTrashed()->where('email', $email)->first();

                if ($existingGuru) {
                    if ($existingGuru->trashed()) {
                        $existingGuru->restore();
                    }
                    $user = $existingGuru->user()->withTrashed()->first();
                    if ($user) {
                        if ($user->trashed()) {
                            $user->restore();
                        }
                        $userData = [
                            'name' => $nama,
                            'email' => $email,
                            'phone' => $phone ?: null,
                            'tanggal_lahir' => $tanggalLahir ?: null,
                        ];
                        if (!empty($excelPassword)) {
                            $userData['password'] = Hash::make($excelPassword);
                        }
                        $user->update($userData);
                    }
                    $existingGuru->update([
                        'nama' => $nama,
                    ]);
                } elseif ($existingUser) {
                    if ($existingUser->trashed()) {
                        $existingUser->restore();
                    }
                    $existingGuruByUser = Guru::withTrashed()->where('user_id', $existingUser->id)->first();
                    if ($existingGuruByUser) {
                        if ($existingGuruByUser->trashed()) {
                            $existingGuruByUser->restore();
                        }
                        $userData = [
                            'name' => $nama,
                            'phone' => $phone ?: null,
                            'tanggal_lahir' => $tanggalLahir ?: null,
                        ];
                        if (!empty($excelPassword)) {
                            $userData['password'] = Hash::make($excelPassword);
                        }
                        $existingUser->update($userData);
                        $existingGuruByUser->update([
                            'nip' => $nip ?: null,
                            'nama' => $nama,
                        ]);
                    } else {
                        $userData = [
                            'name' => $nama,
                            'phone' => $phone ?: null,
                            'tanggal_lahir' => $tanggalLahir ?: null,
                        ];
                        if (!empty($excelPassword)) {
                            $userData['password'] = Hash::make($excelPassword);
                        }
                        $existingUser->update($userData);
                        Guru::create([
                            'user_id' => $existingUser->id,
                            'nip' => $nip ?: null,
                            'nama' => $nama,
                        ]);
                    }
                } else {
                    $user = User::create([
                        'name' => $nama,
                        'email' => $email,
                        'tanggal_lahir' => $tanggalLahir ?: null,
                        'password' => Hash::make($excelPassword ?: 'guru123'),
                        'role' => 'guru',
                        'phone' => $phone ?: null,
                    ]);

                    Guru::create([
                        'user_id' => $user->id,
                        'nip' => $nip ?: null,
                        'nama' => $nama,
                    ]);
                }
            }
        });
    }

    public function importDudi($filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) <= 1) {
            throw new \Exception('File Excel kosong atau tidak memiliki data.');
        }

        DB::transaction(function() use ($rows) {
            foreach ($rows as $index => $row) {
                if ($index === 0) continue;

                $nama = trim($row[0] ?? '');
                $alamat = trim($row[1] ?? '');
                $lat = trim($row[2] ?? '');
                $lng = trim($row[3] ?? '');
                $radius = trim($row[4] ?? '50');
                $pic = trim($row[5] ?? '');
                $picPhone = trim($row[6] ?? '');

                if (empty($nama) || empty($alamat) || empty($lat) || empty($lng) || empty($pic) || empty($picPhone)) {
                    throw new \Exception("Baris " . ($index + 1) . ": Kolom Nama, Alamat, Lat, Lng, Nama Pembimbing Industri, No. Telp Pembimbing Industri wajib diisi.");
                }

                $existingDudi = Dudi::withTrashed()->where('nama', $nama)->first();
                if ($existingDudi) {
                    if ($existingDudi->trashed()) {
                        $existingDudi->restore();
                    }
                    $existingDudi->update([
                        'alamat' => $alamat,
                        'latitude' => (double)$lat,
                        'longitude' => (double)$lng,
                        'radius_meter' => (int)$radius,
                        'pic_nama' => $pic,
                        'pic_phone' => $picPhone,
                    ]);
                } else {
                    Dudi::create([
                        'nama' => $nama,
                        'alamat' => $alamat,
                        'latitude' => (double)$lat,
                        'longitude' => (double)$lng,
                        'radius_meter' => (int)$radius,
                        'pic_nama' => $pic,
                        'pic_phone' => $picPhone,
                    ]);
                }
            }
        });
    }

    /**
     * Import Penempatan PKL with transaksional rollback.
     */
    public function importPenempatan($filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) <= 1) {
            throw new \Exception('File Excel kosong atau tidak memiliki data.');
        }

        // Get active school year
        $ta = TahunAjaran::where('is_aktif', true)->first();
        if (!$ta) {
            throw new \Exception('Tidak ada Tahun Ajaran aktif di database. Silakan aktifkan tahun ajaran terlebih dahulu.');
        }

        DB::transaction(function() use ($rows, $ta) {
            foreach ($rows as $index => $row) {
                if ($index === 0) continue;

                $nis = trim($row[0] ?? '');
                $dudiNama = trim($row[1] ?? '');
                $nip = trim($row[2] ?? '');
                $tglMulai = trim($row[3] ?? '');
                $tglSelesai = trim($row[4] ?? '');

                if (empty($nis) || empty($dudiNama) || empty($nip) || empty($tglMulai) || empty($tglSelesai)) {
                    throw new \Exception("Baris " . ($index + 1) . ": NIS, Nama DUDI, NIP, Tgl Mulai, dan Tgl Selesai wajib diisi.");
                }

                // Find student
                $murid = Murid::where('nis', $nis)->first();
                if (!$murid) {
                    throw new \Exception("Baris " . ($index + 1) . ": Murid dengan NIS '{$nis}' tidak ditemukan.");
                }

                // Find DUDI
                $dudi = Dudi::where('nama', $dudiNama)->first();
                if (!$dudi) {
                    throw new \Exception("Baris " . ($index + 1) . ": Mitra DUDI '{$dudiNama}' tidak ditemukan.");
                }

                // Find Guru
                $guru = Guru::where('nip', $nip)->first();
                if (!$guru) {
                    throw new \Exception("Baris " . ($index + 1) . ": Guru Pembimbing dengan NIP '{$nip}' tidak ditemukan.");
                }

                // Check duplicate placement in this active year
                if (PenempatanPkl::where('murid_id', $murid->id)->where('tahun_ajaran_id', $ta->id)->where('status', 'aktif')->exists()) {
                    throw new \Exception("Baris " . ($index + 1) . ": Murid '{$murid->nama}' sudah ditempatkan pada Tahun Ajaran berjalan.");
                }

                // Create placement
                PenempatanPkl::create([
                    'murid_id' => $murid->id,
                    'dudi_id' => $dudi->id,
                    'guru_id' => $guru->id,
                    'tahun_ajaran_id' => $ta->id,
                    'tanggal_mulai' => $tglMulai,
                    'tanggal_selesai' => $tglSelesai,
                    'status' => 'aktif',
                ]);
            }
        });
    }

    /**
     * Download Excel templates programmatically.
     */
    public function downloadTemplate(string $type): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        switch ($type) {
            case 'murid':
                $sheet->setCellValue('A1', 'Nama Lengkap');
                $sheet->setCellValue('B1', 'Email');
                $sheet->setCellValue('C1', 'No. Telp (WhatsApp)');
                $sheet->setCellValue('D1', 'NIS (Nomor Induk Siswa)');
                $sheet->setCellValue('E1', 'Nama Kelas');
                $sheet->setCellValue('F1', 'Password Default (Opsional)');
                $sheet->setCellValue('G1', 'Tanggal Lahir (YYYY-MM-DD)');
                
                // Example row
                $sheet->setCellValue('A2', 'Ahmad Fauzi');
                $sheet->setCellValue('B2', 'ahmad@siswa.sch.id');
                $sheet->setCellValue('C2', '08123456789');
                $sheet->setCellValue('D2', '102911');
                $sheet->setCellValue('E2', 'XII RPL 1');
                $sheet->setCellValue('F2', 'siswa123');
                $sheet->setCellValue('G2', '2008-05-15');
                break;

            case 'guru':
                $sheet->setCellValue('A1', 'Nama Lengkap');
                $sheet->setCellValue('B1', 'Email');
                $sheet->setCellValue('C1', 'No. Telp');
                $sheet->setCellValue('D1', 'NIP');
                $sheet->setCellValue('E1', 'Password Default (Opsional)');
                $sheet->setCellValue('F1', 'Tanggal Lahir (YYYY-MM-DD)');
                
                // Example
                $sheet->setCellValue('A2', 'Budi Hermawan, S.Kom');
                $sheet->setCellValue('B2', 'budi@guru.sch.id');
                $sheet->setCellValue('C2', '08134567890');
                $sheet->setCellValue('D2', '198503112010011002');
                $sheet->setCellValue('E2', 'guru123');
                $sheet->setCellValue('F2', '1985-03-11');
                break;

            case 'dudi':
                $sheet->setCellValue('A1', 'Nama Perusahaan');
                $sheet->setCellValue('B1', 'Alamat');
                $sheet->setCellValue('C1', 'Latitude');
                $sheet->setCellValue('D1', 'Longitude');
                $sheet->setCellValue('E1', 'Radius Toleransi (Meter)');
                $sheet->setCellValue('F1', 'Nama Pembimbing Industri');
                $sheet->setCellValue('G1', 'No. Telp Pembimbing Industri');
                
                // Example
                $sheet->setCellValue('A2', 'PT. Sukses Kreatif Solusindo');
                $sheet->setCellValue('B2', 'Sudirman Central Business District (SCBD) Lot 10, Jakarta Selatan');
                $sheet->setCellValue('C2', '-6.223056');
                $sheet->setCellValue('D2', '106.809722');
                $sheet->setCellValue('E2', '100');
                $sheet->setCellValue('F2', 'Eko Prasetyo');
                $sheet->setCellValue('G2', '081299998888');
                break;

            case 'penempatan':
                $sheet->setCellValue('A1', 'NIS Siswa');
                $sheet->setCellValue('B1', 'Nama Mitra DUDI');
                $sheet->setCellValue('C1', 'NIP Guru Pembimbing');
                $sheet->setCellValue('D1', 'Tanggal Mulai (YYYY-MM-DD)');
                $sheet->setCellValue('E1', 'Tanggal Selesai (YYYY-MM-DD)');
                
                // Example
                $sheet->setCellValue('A2', '102911');
                $sheet->setCellValue('B2', 'PT. Sukses Kreatif Solusindo');
                $sheet->setCellValue('C2', '198503112010011002');
                $sheet->setCellValue('D2', '2026-07-01');
                $sheet->setCellValue('E2', '2026-12-31');
                break;
        }

        // Set response headers and output file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="template_import_' . $type . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
