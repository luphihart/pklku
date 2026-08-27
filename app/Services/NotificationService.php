<?php

namespace App\Services;

use App\Models\User;
use App\Modules\Jurnal\Models\Jurnal;
use App\Modules\MasterData\Models\Murid;
use App\Modules\Pengumuman\Models\Pengumuman;
use App\Modules\Penilaian\Models\PenilaianPkl;
use App\Modules\PKL\Models\PenempatanPkl;
use App\Modules\Presensi\Models\IzinSakit;
use App\Modules\Setting\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Get all active notifications for the logged-in user.
     *
     * @param User|null $user
     * @return Collection
     */
    public function getNotificationsForUser(?User $user = null): Collection
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return collect();
        }

        $notifications = collect();
        $today = Carbon::today();
        $now = Carbon::now();

        // ==========================================
        // 1. Pengumuman Baru (Last 7 days)
        // ==========================================
        try {
            $announcements = Pengumuman::where(function ($q) use ($user) {
                    $q->where('target_role', 'semua')
                      ->orWhere('target_role', $user->role)
                      ->orWhere(function ($sub) use ($user) {
                          $sub->where('target_role', 'kustom')
                              ->whereHas('penerima', function ($p) use ($user) {
                                  $p->where('user_id', $user->id);
                              });
                      });
                })
                ->where('created_at', '>=', $now->copy()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            foreach ($announcements as $ann) {
                $notifications->push([
                    'id' => 'pengumuman_' . $ann->id,
                    'type' => 'pengumuman',
                    'title' => '📢 ' . $ann->judul,
                    'message' => Str::limit(strip_tags($ann->isi), 75),
                    'url' => route('pengumuman.index'),
                    'time' => $ann->created_at->diffForHumans(),
                    'created_at' => $ann->created_at,
                    'badge_bg' => 'bg-primary-light text-primary',
                    'icon' => 'announcement',
                ]);
            }
        } catch (\Throwable $e) {}

        // ==========================================
        // 2. Ulang Tahun Hari Ini
        // ==========================================
        try {
            // (a) Self birthday
            if ($user->tanggal_lahir && $user->tanggal_lahir->month === $today->month && $user->tanggal_lahir->day === $today->day) {
                $notifications->push([
                    'id' => 'ultah_self_' . $user->id,
                    'type' => 'ultah',
                    'title' => '🎉 Selamat Ulang Tahun!',
                    'message' => 'Selamat ulang tahun untukmu! Semoga selalu diberikan kesehatan, kelancaran, & kesuksesan dalam studi dan PKL.',
                    'url' => route('profile'),
                    'time' => 'Hari ini',
                    'created_at' => $today->copy()->setTime(0, 0, 1),
                    'badge_bg' => 'bg-danger-light text-danger',
                    'icon' => 'cake',
                ]);
            }

            // (b) Birthday peers / students depending on role
            if ($user->role === 'guru') {
                $guruId = $user->guru?->id;
                if ($guruId) {
                    $bdayMurids = Murid::with('kelas')
                        ->whereHas('penempatanAktif', function ($q) use ($guruId) {
                            $q->where('guru_id', $guruId);
                        })
                        ->whereHas('user', function ($q) use ($today) {
                            $q->whereMonth('tanggal_lahir', $today->month)
                              ->whereDay('tanggal_lahir', $today->day);
                        })
                        ->get();

                    foreach ($bdayMurids as $bm) {
                        $notifications->push([
                            'id' => 'ultah_murid_' . $bm->id,
                            'type' => 'ultah',
                            'title' => '🎂 Ulang Tahun: ' . $bm->nama,
                            'message' => 'Siswa bimbingan Anda (' . ($bm->kelas?->nama ?? 'Siswa') . ') berulang tahun hari ini.',
                            'url' => route('penempatan.index'),
                            'time' => 'Hari ini',
                            'created_at' => $today->copy()->setTime(0, 0, 2),
                            'badge_bg' => 'bg-danger-light text-danger',
                            'icon' => 'cake',
                        ]);
                    }
                }
            } elseif ($user->role === 'murid') {
                $murid = $user->murid;
                if ($murid && $murid->kelas_id) {
                    $bdayFriends = Murid::where('kelas_id', $murid->kelas_id)
                        ->where('id', '!=', $murid->id)
                        ->whereHas('user', function ($q) use ($today) {
                            $q->whereMonth('tanggal_lahir', $today->month)
                              ->whereDay('tanggal_lahir', $today->day);
                        })
                        ->get();

                    foreach ($bdayFriends as $bf) {
                        $notifications->push([
                            'id' => 'ultah_teman_' . $bf->id,
                            'type' => 'ultah',
                            'title' => '🎂 Ulang Tahun: ' . $bf->nama,
                            'message' => 'Teman sekelas Anda berulang tahun hari ini.',
                            'url' => route('dashboard'),
                            'time' => 'Hari ini',
                            'created_at' => $today->copy()->setTime(0, 0, 2),
                            'badge_bg' => 'bg-danger-light text-danger',
                            'icon' => 'cake',
                        ]);
                    }
                }
            } elseif ($user->role === 'admin') {
                $bdayUsers = User::where('id', '!=', $user->id)
                    ->whereMonth('tanggal_lahir', $today->month)
                    ->whereDay('tanggal_lahir', $today->day)
                    ->take(5)
                    ->get();

                foreach ($bdayUsers as $bu) {
                    $notifications->push([
                        'id' => 'ultah_user_' . $bu->id,
                        'type' => 'ultah',
                        'title' => '🎂 Ulang Tahun: ' . $bu->name,
                        'message' => ucfirst($bu->role) . ' sekolah berulang tahun hari ini.',
                        'url' => $bu->role === 'guru' ? route('guru.index') : route('murid.index'),
                        'time' => 'Hari ini',
                        'created_at' => $today->copy()->setTime(0, 0, 2),
                        'badge_bg' => 'bg-danger-light text-danger',
                        'icon' => 'cake',
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        // ==========================================
        // 3. Jurnal PKL
        // ==========================================
        try {
            if ($user->role === 'murid') {
                $murid = $user->murid;
                $placement = $murid ? $murid->penempatanAktif : null;
                if ($placement) {
                    // Check if today is working day (not placement holiday)
                    $isHoliday = $placement->isPlacementHoliday($today->format('Y-m-d'));
                    if (!$isHoliday) {
                        $hasJournal = Jurnal::where('penempatan_pkl_id', $placement->id)
                            ->whereDate('tanggal', $today->format('Y-m-d'))
                            ->exists();

                        if (!$hasJournal) {
                            $notifications->push([
                                'id' => 'jurnal_reminder_' . $today->format('Ymd'),
                                'type' => 'jurnal',
                                'title' => '📝 Pengingat Jurnal Harian',
                                'message' => 'Anda belum mengisi catatan jurnal harian PKL untuk hari ini (' . $today->translatedFormat('d F Y') . ').',
                                'url' => route('jurnal.index'),
                                'time' => 'Hari ini',
                                'created_at' => $today->copy()->setTime(12, 0, 0),
                                'badge_bg' => 'bg-warning-light text-warning',
                                'icon' => 'pencil',
                            ]);
                        }
                    }
                }
            } elseif ($user->role === 'guru') {
                $guruId = $user->guru?->id;
                if ($guruId) {
                    $pendingJurnalCount = Jurnal::whereHas('penempatanPkl', function ($q) use ($guruId) {
                            $q->where('guru_id', $guruId)->where('status', 'aktif');
                        })
                        ->where('status_verifikasi', 'menunggu')
                        ->count();

                    if ($pendingJurnalCount > 0) {
                        $notifications->push([
                            'id' => 'jurnal_pending_guru',
                            'type' => 'jurnal',
                            'title' => '📝 Verifikasi Jurnal PKL',
                            'message' => "Terdapat {$pendingJurnalCount} jurnal siswa bimbingan yang menunggu verifikasi Anda.",
                            'url' => route('jurnal.index'),
                            'time' => 'Perlu tindakan',
                            'created_at' => $now,
                            'badge_bg' => 'bg-warning-light text-warning',
                            'icon' => 'pencil',
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {}

        // ==========================================
        // 4. Pengajuan Izin / Sakit
        // ==========================================
        try {
            if ($user->role === 'guru' || $user->role === 'admin') {
                $query = IzinSakit::with(['penempatanPkl.murid'])
                    ->where('status_approval', 'menunggu');

                if ($user->role === 'guru') {
                    $guruId = $user->guru?->id ?: -1;
                    $query->whereHas('penempatanPkl', fn($q) => $q->where('guru_id', $guruId));
                }

                $pendingIzinCount = $query->count();
                if ($pendingIzinCount > 0) {
                    $notifications->push([
                        'id' => 'izin_pending_approval',
                        'type' => 'izin',
                        'title' => '📋 Pengajuan Izin Siswa',
                        'message' => "Terdapat {$pendingIzinCount} pengajuan izin/sakit siswa yang menunggu persetujuan.",
                        'url' => route('presensi.izin.index'),
                        'time' => 'Menunggu persetujuan',
                        'created_at' => $now,
                        'badge_bg' => 'bg-info-light text-info',
                        'icon' => 'clipboard',
                    ]);
                }
            } elseif ($user->role === 'murid') {
                $murid = $user->murid;
                $placement = $murid ? $murid->penempatanAktif : null;
                if ($placement) {
                    $recentResolvedIzin = IzinSakit::where('penempatan_pkl_id', $placement->id)
                        ->whereIn('status_approval', ['disetujui', 'ditolak'])
                        ->where('updated_at', '>=', $now->copy()->subDays(3))
                        ->orderBy('updated_at', 'desc')
                        ->get();

                    foreach ($recentResolvedIzin as $iz) {
                        $statusBadge = $iz->status_approval === 'disetujui' ? 'Disetujui ✅' : 'Ditolak ❌';
                        $notifications->push([
                            'id' => 'izin_resolved_' . $iz->id,
                            'type' => 'izin',
                            'title' => '📋 Status Izin ' . ucfirst($iz->tipe),
                            'message' => "Pengajuan {$iz->tipe} Anda untuk tanggal " . Carbon::parse($iz->tanggal_mulai)->translatedFormat('d M Y') . " telah {$statusBadge}.",
                            'url' => route('presensi.izin.index'),
                            'time' => $iz->updated_at->diffForHumans(),
                            'created_at' => $iz->updated_at,
                            'badge_bg' => $iz->status_approval === 'disetujui' ? 'bg-success-light text-success' : 'bg-danger-light text-danger',
                            'icon' => 'clipboard',
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {}

        // ==========================================
        // 5. Penilaian PKL
        // ==========================================
        try {
            $isMasaPenilaianOpen = Setting::where('key', 'masa_penilaian')->value('value') === 'buka';

            if ($user->role === 'murid') {
                $murid = $user->murid;
                $placement = $murid ? $murid->penempatanAktif : null;
                if ($placement) {
                    $eval = PenilaianPkl::where('penempatan_pkl_id', $placement->id)->first();
                    
                    if ($isMasaPenilaianOpen && (!$eval || in_array($eval->status_nilai_industri, ['draft', null]))) {
                        $notifications->push([
                            'id' => 'penilaian_open_murid',
                            'type' => 'penilaian',
                            'title' => '🏆 Masa Penilaian PKL Dibuka',
                            'message' => 'Masa pengisian nilai PKL sedang dibuka. Segera input nilai lembar fisik DUDI Anda.',
                            'url' => route('penilaian.index'),
                            'time' => 'Batas waktu aktif',
                            'created_at' => $now,
                            'badge_bg' => 'bg-success-light text-success',
                            'icon' => 'star',
                        ]);
                    } elseif ($eval && $eval->status_nilai_industri === 'diverifikasi' && $eval->nilai_akhir > 0) {
                        $notifications->push([
                            'id' => 'penilaian_selesai_murid',
                            'type' => 'penilaian',
                            'title' => '🎓 Rapor Nilai PKL Terbit',
                            'message' => 'Nilai akhir PKL Anda telah disahkan. Klik untuk melihat rincian & mengunduh rapor PDF.',
                            'url' => route('penilaian.index'),
                            'time' => $eval->updated_at ? $eval->updated_at->diffForHumans() : 'Tersedia',
                            'created_at' => $eval->updated_at ?? $now,
                            'badge_bg' => 'bg-success-light text-success',
                            'icon' => 'star',
                        ]);
                    }
                }
            } elseif ($user->role === 'guru') {
                $guruId = $user->guru?->id;
                if ($guruId) {
                    $pendingNilaiCount = PenilaianPkl::whereHas('penempatanPkl', function ($q) use ($guruId) {
                            $q->where('guru_id', $guruId)->where('status', 'aktif');
                        })
                        ->where('status_nilai_industri', 'diajukan')
                        ->count();

                    if ($pendingNilaiCount > 0) {
                        $notifications->push([
                            'id' => 'penilaian_pending_guru',
                            'type' => 'penilaian',
                            'title' => '🏆 Verifikasi Nilai DUDI',
                            'message' => "Terdapat {$pendingNilaiCount} siswa bimbingan yang telah mengirimkan nilai DUDI untuk diverifikasi.",
                            'url' => route('penilaian.index'),
                            'time' => 'Menunggu verifikasi',
                            'created_at' => $now,
                            'badge_bg' => 'bg-success-light text-success',
                            'icon' => 'star',
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {}

        // Sort notifications by created_at desc
        return $notifications->sortByDesc('created_at')->values();
    }
}
