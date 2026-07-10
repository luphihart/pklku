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

        return $query->paginate(15);
    }

    public function findMuridById(int $id)
    {
        return Murid::with(['user', 'kelas.jurusan'])->findOrFail($id);
    }

    public function createMurid(array $data)
    {
        return DB::transaction(function() use ($data) {
            // Create user first
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'password' => Hash::make($data['password'] ?? 'siswa123'),
                'role' => 'murid',
                'phone' => $data['phone'] ?? null,
            ]);

            // Create murid profile
            return Murid::create([
                'user_id' => $user->id,
                'nis' => $data['nis'],
                'nama' => $data['nama'],
                'kelas_id' => $data['kelas_id'],
            ]);
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
            
            $murid->delete();
            if ($user) {
                $user->delete();
            }
            return true;
        });
    }

    // Guru CRUD

    public function getAllGuru()
    {
        return Guru::with('user')->paginate(15);
    }

    public function findGuruById(int $id)
    {
        return Guru::with('user')->findOrFail($id);
    }

    public function createGuru(array $data)
    {
        return DB::transaction(function() use ($data) {
            // Create user first
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'password' => Hash::make($data['password'] ?? 'guru123'),
                'role' => 'guru',
                'phone' => $data['phone'] ?? null,
            ]);

            // Create guru profile
            return Guru::create([
                'user_id' => $user->id,
                'nip' => $data['nip'] ?? null,
                'nama' => $data['nama'],
            ]);
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

            $guru->delete();
            if ($user) {
                $user->delete();
            }
            return true;
        });
    }

    // Dudi CRUD

    public function getAllDudi()
    {
        return Dudi::paginate(15);
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
        $dudi = Dudi::findOrFail($id);
        return $dudi->delete();
    }
}
