<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\Models\HariLibur;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HariLiburController extends Controller
{
    /**
     * Display a listing of holidays with optional year filter.
     */
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $search = $request->input('search');

        $query = HariLibur::query();

        if ($year) {
            $query->where(function ($q) use ($year) {
                $q->whereYear('tanggal_mulai', $year)
                  ->orWhereYear('tanggal_selesai', $year);
            });
        }

        if ($search) {
            $query->where('nama', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
        }

        $holidays = $query->orderBy('tanggal_mulai', 'asc')->paginate(20)->withQueryString();

        // Get available years for filtering
        $availableYears = HariLibur::selectRaw('YEAR(tanggal_mulai) as year')
            ->distinct()
            ->pluck('year')
            ->toArray();

        if (!in_array((int)date('Y'), $availableYears)) {
            $availableYears[] = (int)date('Y');
        }
        if (!in_array((int)date('Y') + 1, $availableYears)) {
            $availableYears[] = (int)date('Y') + 1;
        }
        rsort($availableYears);

        return view('masterdata::hari_libur.index', compact('holidays', 'year', 'search', 'availableYears'));
    }

    /**
     * Store a newly created holiday in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string',
            'is_nasional' => 'nullable|boolean',
        ]);

        HariLibur::create([
            'nama' => $request->nama,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai ?: $request->tanggal_mulai,
            'keterangan' => $request->keterangan,
            'is_nasional' => $request->has('is_nasional') ? (bool)$request->is_nasional : true,
        ]);

        return redirect()->route('hari-libur.index', ['year' => date('Y', strtotime($request->tanggal_mulai))])
            ->with('success', 'Hari libur berhasil ditambahkan.');
    }

    /**
     * Update the specified holiday in storage.
     */
    public function update(Request $request, int $id)
    {
        $holiday = HariLibur::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string',
            'is_nasional' => 'nullable|boolean',
        ]);

        $holiday->update([
            'nama' => $request->nama,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai ?: $request->tanggal_mulai,
            'keterangan' => $request->keterangan,
            'is_nasional' => $request->has('is_nasional') ? (bool)$request->is_nasional : true,
        ]);

        return redirect()->route('hari-libur.index', ['year' => date('Y', strtotime($request->tanggal_mulai))])
            ->with('success', 'Hari libur berhasil diperbarui.');
    }

    /**
     * Remove the specified holiday from storage.
     */
    public function destroy(int $id)
    {
        $holiday = HariLibur::findOrFail($id);
        $year = date('Y', strtotime($holiday->tanggal_mulai));
        $holiday->delete();

        return redirect()->route('hari-libur.index', ['year' => $year])
            ->with('success', 'Hari libur berhasil dihapus.');
    }

    /**
     * Synchronize / auto-populate standard Indonesian national holidays for a given year.
     */
    public function syncNational(Request $request)
    {
        $year = (int)$request->input('year', date('Y'));

        // Preset calendar of common Indonesian national holidays
        $nationalHolidaysByYear = [
            2026 => [
                ['nama' => 'Tahun Baru 2026 Masehi', 'tanggal_mulai' => '2026-01-01', 'tanggal_selesai' => '2026-01-01', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Isra Mi\'raj Nabi Muhammad SAW', 'tanggal_mulai' => '2026-01-16', 'tanggal_selesai' => '2026-01-16', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Tahun Baru Imlek 2577 Kongzili', 'tanggal_mulai' => '2026-02-17', 'tanggal_selesai' => '2026-02-17', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Suci Nyepi Tahun Baru Saka 1948', 'tanggal_mulai' => '2026-03-19', 'tanggal_selesai' => '2026-03-19', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Raya Idul Fitri 1447 Hijriah', 'tanggal_mulai' => '2026-03-20', 'tanggal_selesai' => '2026-03-23', 'keterangan' => 'Hari Libur Nasional & Cuti Bersama'],
                ['nama' => 'Wafat Isa Al Masih', 'tanggal_mulai' => '2026-04-03', 'tanggal_selesai' => '2026-04-03', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Buruh Internasional', 'tanggal_mulai' => '2026-05-01', 'tanggal_selesai' => '2026-05-01', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Kenaikan Yesus Kristus', 'tanggal_mulai' => '2026-05-14', 'tanggal_selesai' => '2026-05-14', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Raya Idul Adha 1447 Hijriah', 'tanggal_mulai' => '2026-05-27', 'tanggal_selesai' => '2026-05-27', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Raya Waisak 2570 BE', 'tanggal_mulai' => '2026-05-31', 'tanggal_selesai' => '2026-05-31', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Lahir Pancasila', 'tanggal_mulai' => '2026-06-01', 'tanggal_selesai' => '2026-06-01', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Tahun Baru Islam 1448 Hijriah', 'tanggal_mulai' => '2026-06-16', 'tanggal_selesai' => '2026-06-16', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Kemerdekaan Republik Indonesia ke-81', 'tanggal_mulai' => '2026-08-17', 'tanggal_selesai' => '2026-08-17', 'keterangan' => 'Hari Libur Nasional HUT RI'],
                ['nama' => 'Maulid Nabi Muhammad SAW', 'tanggal_mulai' => '2026-08-25', 'tanggal_selesai' => '2026-08-25', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Raya Natal', 'tanggal_mulai' => '2026-12-25', 'tanggal_selesai' => '2026-12-25', 'keterangan' => 'Hari Libur Nasional'],
            ],
            2027 => [
                ['nama' => 'Tahun Baru 2027 Masehi', 'tanggal_mulai' => '2027-01-01', 'tanggal_selesai' => '2027-01-01', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Isra Mi\'raj Nabi Muhammad SAW', 'tanggal_mulai' => '2027-01-06', 'tanggal_selesai' => '2027-01-06', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Tahun Baru Imlek 2578 Kongzili', 'tanggal_mulai' => '2027-02-06', 'tanggal_selesai' => '2027-02-06', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Suci Nyepi Tahun Baru Saka 1949', 'tanggal_mulai' => '2027-03-09', 'tanggal_selesai' => '2027-03-09', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Raya Idul Fitri 1448 Hijriah', 'tanggal_mulai' => '2027-03-10', 'tanggal_selesai' => '2027-03-12', 'keterangan' => 'Hari Libur Nasional & Cuti Bersama'],
                ['nama' => 'Wafat Yesus Kristus', 'tanggal_mulai' => '2027-03-26', 'tanggal_selesai' => '2027-03-26', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Buruh Internasional', 'tanggal_mulai' => '2027-05-01', 'tanggal_selesai' => '2027-05-01', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Kenaikan Yesus Kristus', 'tanggal_mulai' => '2027-05-06', 'tanggal_selesai' => '2027-05-06', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Raya Idul Adha 1448 Hijriah', 'tanggal_mulai' => '2027-05-16', 'tanggal_selesai' => '2027-05-16', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Raya Waisak 2571 BE', 'tanggal_mulai' => '2027-05-20', 'tanggal_selesai' => '2027-05-20', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Lahir Pancasila', 'tanggal_mulai' => '2027-06-01', 'tanggal_selesai' => '2027-06-01', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Tahun Baru Islam 1449 Hijriah', 'tanggal_mulai' => '2027-06-06', 'tanggal_selesai' => '2027-06-06', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Kemerdekaan Republik Indonesia ke-82', 'tanggal_mulai' => '2027-08-17', 'tanggal_selesai' => '2027-08-17', 'keterangan' => 'Hari Libur Nasional HUT RI'],
                ['nama' => 'Maulid Nabi Muhammad SAW', 'tanggal_mulai' => '2027-08-15', 'tanggal_selesai' => '2027-08-15', 'keterangan' => 'Hari Libur Nasional'],
                ['nama' => 'Hari Raya Natal', 'tanggal_mulai' => '2027-12-25', 'tanggal_selesai' => '2027-12-25', 'keterangan' => 'Hari Libur Nasional'],
            ],
        ];

        $presets = $nationalHolidaysByYear[$year] ?? [
            ['nama' => 'Tahun Baru ' . $year . ' Masehi', 'tanggal_mulai' => "{$year}-01-01", 'tanggal_selesai' => "{$year}-01-01", 'keterangan' => 'Hari Libur Nasional'],
            ['nama' => 'Hari Buruh Internasional', 'tanggal_mulai' => "{$year}-05-01", 'tanggal_selesai' => "{$year}-05-01", 'keterangan' => 'Hari Libur Nasional'],
            ['nama' => 'Hari Lahir Pancasila', 'tanggal_mulai' => "{$year}-06-01", 'tanggal_selesai' => "{$year}-06-01", 'keterangan' => 'Hari Libur Nasional'],
            ['nama' => 'Hari Kemerdekaan Republik Indonesia', 'tanggal_mulai' => "{$year}-08-17", 'tanggal_selesai' => "{$year}-08-17", 'keterangan' => 'Hari Libur Nasional'],
            ['nama' => 'Hari Raya Natal', 'tanggal_mulai' => "{$year}-12-25", 'tanggal_selesai' => "{$year}-12-25", 'keterangan' => 'Hari Libur Nasional'],
        ];

        $count = 0;
        foreach ($presets as $item) {
            $exists = HariLibur::where('tanggal_mulai', $item['tanggal_mulai'])
                ->where('nama', $item['nama'])
                ->exists();

            if (!$exists) {
                HariLibur::create([
                    'nama' => $item['nama'],
                    'tanggal_mulai' => $item['tanggal_mulai'],
                    'tanggal_selesai' => $item['tanggal_selesai'],
                    'keterangan' => $item['keterangan'],
                    'is_nasional' => true,
                ]);
                $count++;
            }
        }

        return redirect()->route('hari-libur.index', ['year' => $year])
            ->with('success', "Berhasil mensinkronisasi {$count} hari libur nasional untuk tahun {$year}.");
    }
}
