<?php

namespace App\Modules\MasterData\Repositories;

use App\Models\User;
use App\Modules\MasterData\Models\Murid;
use App\Modules\MasterData\Models\Guru;
use App\Modules\MasterData\Models\Dudi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MasterDataRepository implements MasterDataRepositoryInterface
{
    // Murid CRUD

    public function getAllMurid(array $filters = [])
    {
        $query = Murid::with(['user', 'kelas.jurusan']);

        if (!empty($filters['kelas_id'])) {
            $query->where('kelas_id', $filters['kelas_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? ($filters['sort_field'] ?? 'nama');
        $order = strtolower($filters['order'] ?? ($filters['sort'] ?? 'asc'));
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'asc';
        }

        if ($sortBy === 'nis') {
            $query->orderBy('nis', $order);
        } else {
            $query->orderBy('nama', $order);
        }

        return $query->paginate(15)->appends(array_filter($filters));
    }

    public function findMuridById(int $id)
    {
        return Murid::with(['user', 'kelas.jurusan'])->findOrFail($id);
    }

    public function createMurid(array $data)
    {
        return DB::transaction(function() use ($data) {
            // 1. Check if there is a soft-deleted user with this email
            $user = User::withTrashed()->where('email', $data['email'])->first();
            if ($user) {
                if ($user->trashed()) {
                    $user->restore();
                }
                $user->update([
                    'name' => $data['nama'],
                    'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                    'password' => Hash::make($data['password'] ?? 'siswa123'),
                    'role' => 'murid',
                    'phone' => $data['phone'] ?? null,
                ]);
            } else {
                // Create user first
                $user = User::create([
                    'name' => $data['nama'],
                    'email' => $data['email'],
                    'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                    'password' => Hash::make($data['password'] ?? 'siswa123'),
                    'role' => 'murid',
                    'phone' => $data['phone'] ?? null,
                ]);
            }

            // 2. Check if there is a soft-deleted murid with this NIS or user_id
            $murid = Murid::withTrashed()
                ->where(function($q) use ($data, $user) {
                    $q->where('nis', $data['nis'])
                      ->orWhere('user_id', $user->id);
                })
                ->first();

            if ($murid) {
                if ($murid->trashed()) {
                    $murid->restore();
                }
                $murid->update([
                    'user_id' => $user->id,
                    'nis' => $data['nis'],
                    'nama' => $data['nama'],
                    'kelas_id' => $data['kelas_id'],
                ]);
                return $murid;
            } else {
                return Murid::create([
                    'user_id' => $user->id,
                    'nis' => $data['nis'],
                    'nama' => $data['nama'],
                    'kelas_id' => $data['kelas_id'],
                ]);
            }
        });
    }

    public function updateMurid(int $id, array $data)
    {
        return DB::transaction(function() use ($id, $data) {
            $murid = Murid::findOrFail($id);
            
            // Update user table
            $userData = [
                'name' => $data['nama'],
                'email' => $data['email'],
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            ];
            if (!empty($data['phone'])) {
                $userData['phone'] = $data['phone'];
            } else {
                $userData['phone'] = null; // reset if cleared
            }
            if (!empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }
            $murid->user->update($userData);

            // Update murid table
            return $murid->update([
                'nis' => $data['nis'],
                'nama' => $data['nama'],
                'kelas_id' => $data['kelas_id'],
            ]);
        });
    }

    public function deleteMurid(int $id)
    {
        return DB::transaction(function() use ($id) {
            $murid = Murid::findOrFail($id);
            $user = $murid->user;

            // Soft-delete semua penempatan PKL terkait murid ini
            // agar relasi tidak menjadi null orphan di halaman Laporan/Monitoring
            \App\Modules\PKL\Models\PenempatanPkl::where('murid_id', $id)->update(['status' => 'dibatalkan']);
            
            $murid->delete();
            if ($user) {
                $user->delete();
            }
            return true;
        });
    }

    // Guru CRUD

    public function getAllGuru(array $filters = [])
    {
        $query = Guru::with('user');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? ($filters['sort_field'] ?? 'nama');
        $order = strtolower($filters['order'] ?? ($filters['sort'] ?? 'asc'));
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'asc';
        }

        if ($sortBy === 'nip') {
            $query->orderBy('nip', $order);
        } else {
            $query->orderBy('nama', $order);
        }

        return $query->paginate(15)->appends(array_filter($filters));
    }

    public function findGuruById(int $id)
    {
        return Guru::with('user')->findOrFail($id);
    }

    public function createGuru(array $data)
    {
        return DB::transaction(function() use ($data) {
            // 1. Check if there is a soft-deleted user with this email
            $user = User::withTrashed()->where('email', $data['email'])->first();
            if ($user) {
                if ($user->trashed()) {
                    $user->restore();
                }
                $user->update([
                    'name' => $data['nama'],
                    'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                    'password' => Hash::make($data['password'] ?? 'guru123'),
                    'role' => 'guru',
                    'phone' => $data['phone'] ?? null,
                ]);
            } else {
                // Create user first
                $user = User::create([
                    'name' => $data['nama'],
                    'email' => $data['email'],
                    'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                    'password' => Hash::make($data['password'] ?? 'guru123'),
                    'role' => 'guru',
                    'phone' => $data['phone'] ?? null,
                ]);
            }

            // 2. Check if there is a soft-deleted guru with this NIP or user_id
            $guru = Guru::withTrashed()
                ->where(function($q) use ($data, $user) {
                    if (!empty($data['nip'])) {
                        $q->where('nip', $data['nip'])
                          ->orWhere('user_id', $user->id);
                    } else {
                        $q->where('user_id', $user->id);
                    }
                })
                ->first();

            if ($guru) {
                if ($guru->trashed()) {
                    $guru->restore();
                }
                $guru->update([
                    'user_id' => $user->id,
                    'nip' => $data['nip'] ?? null,
                    'nama' => $data['nama'],
                ]);
                return $guru;
            } else {
                return Guru::create([
                    'user_id' => $user->id,
                    'nip' => $data['nip'] ?? null,
                    'nama' => $data['nama'],
                ]);
            }
        });
    }

    public function updateGuru(int $id, array $data)
    {
        return DB::transaction(function() use ($id, $data) {
            $guru = Guru::findOrFail($id);

            // Update user
            $userData = [
                'name' => $data['nama'],
                'email' => $data['email'],
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            ];
            if (!empty($data['phone'])) {
                $userData['phone'] = $data['phone'];
            } else {
                $userData['phone'] = null; // reset if cleared
            }
            if (!empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }
            $guru->user->update($userData);

            // Update guru
            return $guru->update([
                'nip' => $data['nip'] ?? null,
                'nama' => $data['nama'],
            ]);
        });
    }

    public function deleteGuru(int $id)
    {
        return DB::transaction(function() use ($id) {
            $guru = Guru::findOrFail($id);
            $user = $guru->user;

            // Hapus relasi guru_id di PenempatanPkl agar tidak menjadi null orphan
            // Set ke null sehingga penempatan tidak crash saat diakses
            \App\Modules\PKL\Models\PenempatanPkl::where('guru_id', $id)->update(['guru_id' => null]);

            $guru->delete();
            if ($user) {
                $user->delete();
            }
            return true;
        });
    }

    // Dudi CRUD

    public function getAllDudi(array $filters = [])
    {
        $query = Dudi::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('pic_nama', 'like', "%{$search}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? ($filters['sort_field'] ?? 'nama');
        $order = strtolower($filters['order'] ?? ($filters['sort'] ?? 'asc'));
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'asc';
        }

        if ($sortBy === 'alamat') {
            $query->orderBy('alamat', $order);
        } else {
            $query->orderBy('nama', $order);
        }

        return $query->paginate(15)->appends(array_filter($filters));
    }

    public function findDudiById(int $id)
    {
        return Dudi::findOrFail($id);
    }

    public function createDudi(array $data)
    {
        return Dudi::create($data);
    }

    public function updateDudi(int $id, array $data)
    {
        $dudi = Dudi::findOrFail($id);
        return $dudi->update($data);
    }

    public function deleteDudi(int $id)
    {
        return DB::transaction(function() use ($id) {
            $dudi = Dudi::findOrFail($id);

            // Batalkan semua penempatan PKL yang terkait DUDI ini
            // agar relasi tidak menjadi null orphan di halaman Presensi/Laporan
            \App\Modules\PKL\Models\PenempatanPkl::where('dudi_id', $id)->update(['status' => 'dibatalkan']);

            return $dudi->delete();
        });
    }
}
