<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\MasterData\Models\Murid;
use App\Modules\MasterData\Models\Guru;
use App\Modules\MasterData\Models\Dudi;
use App\Modules\MasterData\Models\Kelas;
use App\Modules\MasterData\Models\Jurusan;
use App\Modules\MasterData\Models\TahunAjaran;
use App\Modules\Penilaian\Models\IndikatorPenilaian;
use App\Modules\PKL\Models\PenempatanPkl;
use App\Modules\Setting\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NilaiExcelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure settings exist
        Setting::updateOrCreate(['key' => 'bobot_nilai_guru'], ['value' => '50']);
        Setting::updateOrCreate(['key' => 'bobot_nilai_industri'], ['value' => '50']);

        // Seed Tujuan Pembelajaran
        $tp = \App\Modules\Penilaian\Models\TujuanPembelajaran::updateOrCreate(
            ['id' => 1],
            ['nomor' => '1', 'nama' => 'Menerapkan soft skills']
        );

        // Ensure indicators exist
        IndikatorPenilaian::updateOrCreate(['id' => 1], [
            'nama' => 'Disiplin',
            'tipe' => 'guru',
            'tujuan_pembelajaran_id' => $tp->id,
            'nomor_urut' => '1.1'
        ]);
        IndikatorPenilaian::updateOrCreate(['id' => 2], [
            'nama' => 'Kerja Sama',
            'tipe' => 'industri',
            'tujuan_pembelajaran_id' => $tp->id,
            'nomor_urut' => '1.2'
        ]);
    }

    public function test_admin_can_download_template()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('penilaian.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_guru_can_download_template()
    {
        $user = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create([
            'user_id' => $user->id,
            'nip' => '123456789',
            'nama' => 'Guru Test',
        ]);

        $response = $this->actingAs($user)
            ->get(route('penilaian.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_import_nilai()
    {
        // 1. Setup mock data
        $tahun = TahunAjaran::create(['tahun' => '2025/2026', 'semester' => 'ganjil', 'status' => 'aktif']);
        $jurusan = Jurusan::create(['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak']);
        $kelas = Kelas::create(['nama' => 'XII RPL 1', 'jurusan_id' => $jurusan->id]);
        
        $muridUser = User::factory()->create(['role' => 'murid']);
        $murid = Murid::create([
            'nis' => '17553',
            'nama' => 'Abdul Test',
            'kelas_id' => $kelas->id,
            'user_id' => $muridUser->id
        ]);

        $dudi = Dudi::create([
            'nama' => 'PT. Coding Indonesia',
            'alamat' => 'Yogyakarta',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'radius_meter' => 50,
            'pic_nama' => 'PIC Budi',
            'pic_phone' => '08123456789',
        ]);

        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create([
            'nip' => '12345',
            'nama' => 'Budi Guru',
            'user_id' => $guruUser->id
        ]);

        $placement = PenempatanPkl::create([
            'murid_id' => $murid->id,
            'dudi_id' => $dudi->id,
            'guru_id' => $guru->id,
            'tahun_ajaran_id' => $tahun->id,
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'aktif'
        ]);

        // 2. Create Excel file programmatically
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Nilai');

        // Headers
        $sheet->setCellValue('A1', 'NIS');
        $sheet->setCellValue('B1', 'Nama Murid');
        $sheet->setCellValue('C1', 'Kelas');
        $sheet->setCellValue('D1', 'DUDI');
        $sheet->setCellValue('E1', 'Guru: Disiplin');
        $sheet->setCellValue('F1', 'DUDI: Kerja Sama');
        $sheet->setCellValue('G1', 'Keterangan TP 1: Menerapkan soft skills');
        $sheet->setCellValue('H1', 'Catatan');

        // Student row
        $sheet->setCellValue('A2', '17553');
        $sheet->setCellValue('B2', 'Abdul Test');
        $sheet->setCellValue('C2', 'XII RPL 1');
        $sheet->setCellValue('D2', 'PT. Coding Indonesia');
        $sheet->setCellValue('E2', '90');
        $sheet->setCellValue('F2', '85');
        $sheet->setCellValue('G2', 'Kinerja sangat baik');
        $sheet->setCellValue('H2', 'Sangat baik');

        // Hidden metadata sheet
        $metaSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'metadata');
        $spreadsheet->addSheet($metaSheet);
        $metaSheet->setCellValue('A1', '1'); // Guru indicator ID
        $metaSheet->setCellValue('A2', '2'); // Industri indicator ID
        $metaSheet->setCellValue('A3', '1'); // Tujuan Pembelajaran ID

        $tempPath = tempnam(sys_get_temp_dir(), 'test_nilai_import');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        // 3. Perform import via NilaiImportService
        $importService = app(\App\Modules\Penilaian\Services\NilaiImportService::class);
        $result = $importService->importNilai($tempPath, 'guru', $guru->id);

        unlink($tempPath);

        // 4. Assertions
        $this->assertEquals(1, $result['success']);
        $this->assertEmpty($result['errors']);

        // Verify database records
        $this->assertDatabaseHas('penilaian_pkl', [
            'penempatan_pkl_id' => $placement->id,
            'rata_nilai_guru' => 90.0,
            'rata_nilai_industri' => 85.0,
            'nilai_akhir' => 87.5,
            'predikat' => 'B',
            'catatan' => 'Sangat baik',
        ]);

        $evaluation = \App\Modules\Penilaian\Models\PenilaianPkl::where('penempatan_pkl_id', $placement->id)->first();
        $this->assertEquals(['1' => 'Kinerja sangat baik'], $evaluation->keterangan_tp_json);
    }

    public function test_profile_update_and_birthday_banner()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'phone' => '111',
            'tanggal_lahir' => null,
        ]);

        // 1. Test updating phone and tanggal_lahir
        $response = $this->actingAs($user)
            ->post(route('profile.update'), [
                'phone' => '081234567890',
                'tanggal_lahir' => '2000-07-10',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => '081234567890',
            'tanggal_lahir' => '2000-07-10 00:00:00', // SQLite casted as datetime/date
        ]);

        // 2. Set the birthday to exactly today (day and month)
        $user->refresh();
        $user->update([
            'tanggal_lahir' => \Carbon\Carbon::now()->format('Y-m-d')
        ]);

        // 3. Visit dashboard and assert birthday banner is present
        $response = $this->actingAs($user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Selamat Ulang Tahun');
        $response->assertSee('Semoga panjang umur, sehat selalu');
    }

    public function test_master_data_birthday_fields()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $jurusan = Jurusan::create(['kode' => 'TJKT', 'nama' => 'Teknik Jaringan Komputer']);
        $kelas = Kelas::create(['nama' => 'XII TJKT 1', 'jurusan_id' => $jurusan->id]);

        // 1. Create student with tanggal_lahir
        $response = $this->actingAs($admin)
            ->post(route('murid.store'), [
                'nama' => 'Student Birthday Test',
                'email' => 'studentbday@school.id',
                'nis' => '99988877',
                'kelas_id' => $kelas->id,
                'phone' => '08122334455',
                'tanggal_lahir' => '2008-05-15',
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('murid.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'studentbday@school.id',
            'tanggal_lahir' => '2008-05-15 00:00:00',
        ]);

        // 2. Create teacher with tanggal_lahir
        $response = $this->actingAs($admin)
            ->post(route('guru.store'), [
                'nama' => 'Teacher Birthday Test',
                'email' => 'teacherbday@school.id',
                'nip' => '198005152010011002',
                'phone' => '08122334466',
                'tanggal_lahir' => '1980-05-15',
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('guru.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'teacherbday@school.id',
            'tanggal_lahir' => '1980-05-15 00:00:00',
        ]);
    }

    public function test_master_data_excel_import_with_birthday()
    {
        $jurusan = Jurusan::create(['kode' => 'TJKT2', 'nama' => 'Teknik Jaringan Komputer 2']);
        $kelas = Kelas::create(['nama' => 'XII TJKT 2', 'jurusan_id' => $jurusan->id]);

        // 1. Mock a student Excel file
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nama Lengkap');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1', 'No. Telp (WhatsApp)');
        $sheet->setCellValue('D1', 'NIS (Nomor Induk Siswa)');
        $sheet->setCellValue('E1', 'Nama Kelas');
        $sheet->setCellValue('F1', 'Password Default (Opsional)');
        $sheet->setCellValue('G1', 'Tanggal Lahir (YYYY-MM-DD)');

        $sheet->setCellValue('A2', 'Excel Student');
        $sheet->setCellValue('B2', 'excelstudent@school.id');
        $sheet->setCellValue('C2', '08123456789');
        $sheet->setCellValue('D2', '99988811');
        $sheet->setCellValue('E2', 'XII TJKT 2');
        $sheet->setCellValue('F2', 'siswa123');
        $sheet->setCellValue('G2', '2008-11-20');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_import_murid');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        // Run Import
        $importService = new \App\Modules\MasterData\Services\ExcelImportService();
        $importService->importMurid($tempPath);

        unlink($tempPath);

        // Assert student was imported with tanggal_lahir
        $this->assertDatabaseHas('users', [
            'email' => 'excelstudent@school.id',
            'tanggal_lahir' => '2008-11-20 00:00:00',
        ]);
    }

    public function test_admin_can_create_manual_attendance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tahun = TahunAjaran::create(['tahun' => '2025/2026', 'semester' => 'ganjil', 'status' => 'aktif']);
        $jurusan = Jurusan::create(['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak']);
        $kelas = Kelas::create(['nama' => 'XII RPL 1', 'jurusan_id' => $jurusan->id]);
        
        $muridUser = User::factory()->create(['role' => 'murid']);
        $murid = Murid::create(['nis' => '17559', 'nama' => 'Manual Murid', 'kelas_id' => $kelas->id, 'user_id' => $muridUser->id]);
        
        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $guruUser->id, 'nip' => '8888', 'nama' => 'Manual Guru']);
        
        $dudi = Dudi::create([
            'nama' => 'PT. Manual Tech', 'alamat' => 'City', 'latitude' => 0.0, 'longitude' => 0.0, 'radius_meter' => 50,
            'pic_nama' => 'PIC Tech', 'pic_phone' => '0812'
        ]);

        $placement = PenempatanPkl::create([
            'murid_id' => $murid->id,
            'dudi_id' => $dudi->id,
            'guru_id' => $guru->id,
            'tahun_ajaran_id' => $tahun->id,
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'status' => 'aktif'
        ]);

        $response = $this->actingAs($admin)
            ->post(route('presensi.store_manual'), [
                'penempatan_pkl_id' => $placement->id,
                'tanggal' => '2025-07-10',
                'jam_masuk' => '07:15',
                'status_masuk' => 'tepat_waktu',
                'jam_pulang' => '16:00',
                'status_pulang' => 'tepat_waktu',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('presensi', [
            'penempatan_pkl_id' => $placement->id,
            'tanggal' => '2025-07-10',
            'jam_masuk' => '07:15:00',
            'jam_pulang' => '16:00:00',
            'status_masuk' => 'tepat_waktu',
            'status_pulang' => 'tepat_waktu',
        ]);
    }

    public function test_admin_can_update_manual_attendance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tahun = TahunAjaran::create(['tahun' => '2025/2026', 'semester' => 'ganjil', 'status' => 'aktif']);
        $jurusan = Jurusan::create(['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak']);
        $kelas = Kelas::create(['nama' => 'XII RPL 1', 'jurusan_id' => $jurusan->id]);
        
        $muridUser = User::factory()->create(['role' => 'murid']);
        $murid = Murid::create(['nis' => '17560', 'nama' => 'Manual Murid 2', 'kelas_id' => $kelas->id, 'user_id' => $muridUser->id]);
        
        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $guruUser->id, 'nip' => '8889', 'nama' => 'Manual Guru 2']);
        
        $dudi = Dudi::create([
            'nama' => 'PT. Manual Tech 2', 'alamat' => 'City', 'latitude' => 0.0, 'longitude' => 0.0, 'radius_meter' => 50,
            'pic_nama' => 'PIC Tech', 'pic_phone' => '0812'
        ]);

        $placement = PenempatanPkl::create([
            'murid_id' => $murid->id,
            'dudi_id' => $dudi->id,
            'guru_id' => $guru->id,
            'tahun_ajaran_id' => $tahun->id,
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'status' => 'aktif'
        ]);

        $presensi = \App\Modules\Presensi\Models\Presensi::create([
            'penempatan_pkl_id' => $placement->id,
            'tanggal' => '2025-07-10',
            'jam_masuk' => '07:45:00',
            'status_masuk' => 'terlambat',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('presensi.update_manual', $presensi->id), [
                'jam_masuk' => '07:05',
                'status_masuk' => 'tepat_waktu',
                'jam_pulang' => '16:05',
                'status_pulang' => 'tepat_waktu',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('presensi', [
            'id' => $presensi->id,
            'jam_masuk' => '07:05:00',
            'jam_pulang' => '16:05:00',
            'status_masuk' => 'tepat_waktu',
            'status_pulang' => 'tepat_waktu',
        ]);
    }

    public function test_student_and_guru_cannot_create_or_update_manual_attendance()
    {
        $student = User::factory()->create(['role' => 'murid']);
        $guru = User::factory()->create(['role' => 'guru']);
        
        $response1 = $this->actingAs($student)
            ->post(route('presensi.store_manual'), [
                'penempatan_pkl_id' => 1,
                'tanggal' => '2025-07-10',
                'jam_masuk' => '07:15',
                'status_masuk' => 'tepat_waktu',
            ]);

        $response1->assertStatus(403); // Forbidden

        $response2 = $this->actingAs($guru)
            ->post(route('presensi.store_manual'), [
                'penempatan_pkl_id' => 1,
                'tanggal' => '2025-07-10',
                'jam_masuk' => '07:15',
                'status_masuk' => 'tepat_waktu',
            ]);

        $response2->assertStatus(403); // Forbidden
    }

    public function test_manual_attendance_checkin_only()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tahun = TahunAjaran::create(['tahun' => '2025/2026', 'semester' => 'ganjil', 'status' => 'aktif']);
        $jurusan = Jurusan::create(['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak']);
        $kelas = Kelas::create(['nama' => 'XII RPL 1', 'jurusan_id' => $jurusan->id]);
        
        $muridUser = User::factory()->create(['role' => 'murid']);
        $murid = Murid::create(['nis' => '17561', 'nama' => 'Manual Murid 3', 'kelas_id' => $kelas->id, 'user_id' => $muridUser->id]);
        
        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $guruUser->id, 'nip' => '8890', 'nama' => 'Manual Guru 3']);
        
        $dudi = Dudi::create([
            'nama' => 'PT. Manual Tech 3', 'alamat' => 'City', 'latitude' => 0.0, 'longitude' => 0.0, 'radius_meter' => 50,
            'pic_nama' => 'PIC Tech', 'pic_phone' => '0812'
        ]);

        $placement = PenempatanPkl::create([
            'murid_id' => $murid->id,
            'dudi_id' => $dudi->id,
            'guru_id' => $guru->id,
            'tahun_ajaran_id' => $tahun->id,
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'status' => 'aktif'
        ]);

        $response = $this->actingAs($admin)
            ->post(route('presensi.store_manual'), [
                'penempatan_pkl_id' => $placement->id,
                'tanggal' => '2025-07-10',
                'jam_masuk' => '07:15',
                'status_masuk' => 'tepat_waktu',
                'jam_pulang' => '',
                'status_pulang' => '',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('presensi', [
            'penempatan_pkl_id' => $placement->id,
            'tanggal' => '2025-07-10',
            'jam_masuk' => '07:15:00',
            'jam_pulang' => null,
            'status_masuk' => 'tepat_waktu',
            'status_pulang' => null,
        ]);
    }

    public function test_manual_attendance_checkout_only()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tahun = TahunAjaran::create(['tahun' => '2025/2026', 'semester' => 'ganjil', 'status' => 'aktif']);
        $jurusan = Jurusan::create(['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak']);
        $kelas = Kelas::create(['nama' => 'XII RPL 1', 'jurusan_id' => $jurusan->id]);
        
        $muridUser = User::factory()->create(['role' => 'murid']);
        $murid = Murid::create(['nis' => '17562', 'nama' => 'Manual Murid 4', 'kelas_id' => $kelas->id, 'user_id' => $muridUser->id]);
        
        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $guruUser->id, 'nip' => '8891', 'nama' => 'Manual Guru 4']);
        
        $dudi = Dudi::create([
            'nama' => 'PT. Manual Tech 4', 'alamat' => 'City', 'latitude' => 0.0, 'longitude' => 0.0, 'radius_meter' => 50,
            'pic_nama' => 'PIC Tech', 'pic_phone' => '0812'
        ]);

        $placement = PenempatanPkl::create([
            'murid_id' => $murid->id,
            'dudi_id' => $dudi->id,
            'guru_id' => $guru->id,
            'tahun_ajaran_id' => $tahun->id,
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'status' => 'aktif'
        ]);

        $response = $this->actingAs($admin)
            ->post(route('presensi.store_manual'), [
                'penempatan_pkl_id' => $placement->id,
                'tanggal' => '2025-07-10',
                'jam_masuk' => '',
                'status_masuk' => '',
                'jam_pulang' => '16:00',
                'status_pulang' => 'tepat_waktu',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('presensi', [
            'penempatan_pkl_id' => $placement->id,
            'tanggal' => '2025-07-10',
            'jam_masuk' => null,
            'jam_pulang' => '16:00:00',
            'status_masuk' => null,
            'status_pulang' => 'tepat_waktu',
        ]);
    }

    public function test_manual_attendance_fails_if_both_empty()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)
            ->from(route('presensi.index'))
            ->post(route('presensi.store_manual'), [
                'penempatan_pkl_id' => 1,
                'tanggal' => '2025-07-10',
                'jam_masuk' => '',
                'status_masuk' => '',
                'jam_pulang' => '',
                'status_pulang' => '',
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHasErrors(['jam_masuk', 'jam_pulang']);
    }

    public function test_admin_can_wipe_database_with_correct_confirmation()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Mock SystemService to bypass SQLite in-transaction migrate:fresh limits
        $this->mock(\App\Modules\System\Services\SystemService::class, function ($mock) {
            $mock->shouldReceive('wipeDatabase')->once()->andReturnNull();
        });

        $response = $this->actingAs($admin)
            ->post(route('system.wipe_db'), [
                'confirmation_word' => 'KOSONGKAN',
            ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
    }

    public function test_admin_cannot_wipe_database_with_incorrect_confirmation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tahun = TahunAjaran::create(['tahun' => '2025/2026', 'semester' => 'ganjil', 'status' => 'aktif']);
        $jurusan = Jurusan::create(['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak']);

        $response = $this->actingAs($admin)
            ->post(route('system.wipe_db'), [
                'confirmation_word' => 'WRONG_WORD',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('jurusan', ['kode' => 'RPL']);
    }

    public function test_guru_and_student_cannot_wipe_database()
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $response = $this->actingAs($guru)
            ->post(route('system.wipe_db'), [
                'confirmation_word' => 'KOSONGKAN',
            ]);

        $response->assertStatus(403);
    }

    public function test_download_nilai_pdf_with_null_scores()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tahun = TahunAjaran::create(['tahun' => '2025/2026', 'semester' => 'ganjil', 'status' => 'aktif']);
        $jurusan = Jurusan::create(['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak']);
        $kelas = Kelas::create(['nama' => 'XII RPL 1', 'jurusan_id' => $jurusan->id]);
        
        $muridUser = User::factory()->create(['role' => 'murid']);
        $murid = Murid::create(['nis' => '17565', 'nama' => 'Nilai Murid PDF', 'kelas_id' => $kelas->id, 'user_id' => $muridUser->id]);
        
        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $guruUser->id, 'nip' => '9999', 'nama' => 'Nilai Guru PDF']);
        
        $dudi = Dudi::create([
            'nama' => 'PT. Nilai Tech', 'alamat' => 'City', 'latitude' => 0.0, 'longitude' => 0.0, 'radius_meter' => 50,
            'pic_nama' => 'PIC Tech', 'pic_phone' => '0812'
        ]);

        $placement = PenempatanPkl::create([
            'murid_id' => $murid->id,
            'dudi_id' => $dudi->id,
            'guru_id' => $guru->id,
            'tahun_ajaran_id' => $tahun->id,
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'status' => 'aktif'
        ]);

        // Create PenilaianPkl with null average/final scores
        $penilaian = \App\Modules\Penilaian\Models\PenilaianPkl::create([
            'penempatan_pkl_id' => $placement->id,
            'nilai_guru_json' => [],
            'nilai_industri_json' => [],
            'keterangan_tp_json' => [],
            'rata_nilai_guru' => null,      // Null average
            'rata_nilai_industri' => null,  // Null average
            'nilai_akhir' => null,          // Null final score
            'catatan' => 'Good performance',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('laporan.nilai_pdf', $placement->id));

        $response->assertStatus(200);
    }

    public function test_admin_can_update_placement()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tahun = TahunAjaran::create(['tahun' => '2025/2026', 'semester' => 'ganjil', 'status' => 'aktif']);
        $jurusan = Jurusan::create(['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak']);
        $kelas = Kelas::create(['nama' => 'XII RPL 1', 'jurusan_id' => $jurusan->id]);
        
        $muridUser = User::factory()->create(['role' => 'murid']);
        $murid = Murid::create(['nis' => '17553', 'nama' => 'Abdul Test', 'kelas_id' => $kelas->id, 'user_id' => $muridUser->id]);

        $dudi1 = Dudi::create(['nama' => 'PT. Coding Indonesia', 'alamat' => 'Yogyakarta', 'latitude' => 0.0, 'longitude' => 0.0, 'radius_meter' => 50, 'pic_nama' => 'PIC Budi', 'pic_phone' => '08123456789']);
        $dudi2 = Dudi::create(['nama' => 'PT. Baru Indonesia', 'alamat' => 'Jakarta', 'latitude' => 0.0, 'longitude' => 0.0, 'radius_meter' => 50, 'pic_nama' => 'PIC Candra', 'pic_phone' => '08987654321']);

        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru1 = Guru::create(['user_id' => $guruUser->id, 'nip' => '9999', 'nama' => 'Guru Lama']);
        $guruUser2 = User::factory()->create(['role' => 'guru']);
        $guru2 = Guru::create(['user_id' => $guruUser2->id, 'nip' => '8888', 'nama' => 'Guru Baru']);

        $placement = PenempatanPkl::create([
            'murid_id' => $murid->id,
            'dudi_id' => $dudi1->id,
            'guru_id' => $guru1->id,
            'tahun_ajaran_id' => $tahun->id,
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'status' => 'aktif'
        ]);

        $response = $this->actingAs($admin)
            ->put(route('penempatan.update', $placement->id), [
                'dudi_id' => $dudi2->id,
                'guru_id' => $guru2->id,
                'tanggal_mulai' => '2025-08-01',
                'tanggal_selesai' => '2025-11-30',
            ]);

        $response->assertRedirect(route('penempatan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('penempatan_pkl', [
            'id' => $placement->id,
            'dudi_id' => $dudi2->id,
            'guru_id' => $guru2->id,
            'tanggal_mulai' => '2025-08-01',
            'tanggal_selesai' => '2025-11-30',
        ]);
    }

    public function test_admin_can_bulk_delete_placements()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tahun = TahunAjaran::create(['tahun' => '2025/2026', 'semester' => 'ganjil', 'status' => 'aktif']);
        $jurusan = Jurusan::create(['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak']);
        $kelas = Kelas::create(['nama' => 'XII RPL 1', 'jurusan_id' => $jurusan->id]);
        
        $m1 = Murid::create(['nis' => '1', 'nama' => 'M1', 'kelas_id' => $kelas->id, 'user_id' => User::factory()->create(['role' => 'murid'])->id]);
        $m2 = Murid::create(['nis' => '2', 'nama' => 'M2', 'kelas_id' => $kelas->id, 'user_id' => User::factory()->create(['role' => 'murid'])->id]);

        $dudi = Dudi::create(['nama' => 'PT. Code', 'alamat' => 'City', 'latitude' => 0.0, 'longitude' => 0.0, 'radius_meter' => 50, 'pic_nama' => 'B', 'pic_phone' => '1']);
        $guru = Guru::create(['user_id' => User::factory()->create(['role' => 'guru'])->id, 'nip' => '1', 'nama' => 'G']);

        $p1 = PenempatanPkl::create(['murid_id' => $m1->id, 'dudi_id' => $dudi->id, 'guru_id' => $guru->id, 'tahun_ajaran_id' => $tahun->id, 'tanggal_mulai' => '2025-07-01', 'tanggal_selesai' => '2025-12-31', 'status' => 'aktif']);
        $p2 = PenempatanPkl::create(['murid_id' => $m2->id, 'dudi_id' => $dudi->id, 'guru_id' => $guru->id, 'tahun_ajaran_id' => $tahun->id, 'tanggal_mulai' => '2025-07-01', 'tanggal_selesai' => '2025-12-31', 'status' => 'aktif']);

        $response = $this->actingAs($admin)
            ->post(route('penempatan.destroy_bulk'), [
                'ids' => [$p1->id, $p2->id]
            ]);

        $response->assertRedirect(route('penempatan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('penempatan_pkl', ['id' => $p1->id]);
        $this->assertDatabaseMissing('penempatan_pkl', ['id' => $p2->id]);
    }

    public function test_kunjungan_pdf_export()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $guruUser->id, 'nip' => '12345678', 'nama' => 'Pak Guru Budi']);

        $tahun = TahunAjaran::create(['tahun' => '2025/2026', 'semester' => 'ganjil', 'status' => 'aktif']);
        $jurusan = Jurusan::create(['nama' => 'RPL', 'kode' => 'RPL', 'singkatan' => 'RPL']);
        $kelas = Kelas::create(['nama' => 'XII RPL 1', 'jurusan_id' => $jurusan->id]);
        $murid = Murid::create(['nis' => '123', 'nama' => 'Ahmad', 'kelas_id' => $kelas->id, 'user_id' => User::factory()->create(['role' => 'murid'])->id]);

        $dudi = Dudi::create(['nama' => 'PT. Sukses', 'alamat' => 'Jakarta', 'latitude' => 0.0, 'longitude' => 0.0, 'radius_meter' => 50, 'pic_nama' => 'B', 'pic_phone' => '1']);

        $placement = PenempatanPkl::create(['murid_id' => $murid->id, 'dudi_id' => $dudi->id, 'guru_id' => $guru->id, 'tahun_ajaran_id' => $tahun->id, 'tanggal_mulai' => '2025-07-01', 'tanggal_selesai' => '2025-12-31', 'status' => 'aktif']);

        $kunjungan = \App\Modules\PKL\Models\KunjunganMonitoring::create([
            'penempatan_pkl_id' => $placement->id,
            'tanggal' => '2025-08-01',
            'jenis_kunjungan' => 'Monitoring Berkala',
            'deskripsi_kunjungan' => 'Semua berjalan lancar.',
            'foto_kunjungan' => null
        ]);

        // Admin can export
        $responseAdmin = $this->actingAs($admin)
            ->get(route('kunjungan.export_pdf'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertHeader('Content-Type', 'application/pdf');

        // Guru can export
        $responseGuru = $this->actingAs($guruUser)
            ->get(route('kunjungan.export_pdf'));
        $responseGuru->assertStatus(200);
        $responseGuru->assertHeader('Content-Type', 'application/pdf');
    }
}
