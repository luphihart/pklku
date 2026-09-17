<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\Models\Dudi;
use App\Modules\MasterData\Models\PembimbingIndustri;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PembimbingIndustriController extends Controller
{
    /**
     * Store new pembimbing industri + create linked User account.
     */
    public function store(Request $request, int $dudiId)
    {
        $dudi = Dudi::findOrFail($dudiId);

        $request->validate([
            'nama'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:100|unique:users,email',
        ], [
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        DB::transaction(function () use ($request, $dudi) {
            // 1. Buat akun User
            $user = User::create([
                'name'     => $request->nama,
                'email'    => $request->email,
                'password' => Hash::make('dudi123'),
                'role'     => 'industri',
                'phone'    => $request->phone,
            ]);

            // 2. Buat record PembimbingIndustri yang terhubung
            PembimbingIndustri::create([
                'dudi_id' => $dudi->id,
                'user_id' => $user->id,
                'nama'    => $request->nama,
                'phone'   => $request->phone,
                'email'   => $request->email,
            ]);
        });

        return redirect()->route('dudi.index')
            ->with('success', "Akun pembimbing industri untuk DUDI \"{$dudi->nama}\" berhasil dibuat. Password default: dudi123");
    }

    /**
     * Update pembimbing industri data & linked user.
     */
    public function update(Request $request, int $dudiId, int $id)
    {
        $pembimbing = PembimbingIndustri::with('user')->where('dudi_id', $dudiId)->findOrFail($id);

        $request->validate([
            'nama'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:100|unique:users,email,' . ($pembimbing->user_id ?? 'NULL'),
        ], [
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        DB::transaction(function () use ($request, $pembimbing) {
            // Update pembimbing record
            $pembimbing->update([
                'nama'  => $request->nama,
                'phone' => $request->phone,
                'email' => $request->email,
            ]);

            // Update linked user if exists
            if ($pembimbing->user) {
                $pembimbing->user->update([
                    'name'  => $request->nama,
                    'email' => $request->email,
                    'phone' => $request->phone,
                ]);
            }
        });

        return redirect()->route('dudi.index')
            ->with('success', 'Data pembimbing industri berhasil diperbarui.');
    }

    /**
     * Reset password pembimbing to dudi123.
     */
    public function resetPassword(int $dudiId, int $id)
    {
        $pembimbing = PembimbingIndustri::with('user')->where('dudi_id', $dudiId)->findOrFail($id);

        if ($pembimbing->user) {
            $pembimbing->user->update(['password' => Hash::make('dudi123')]);
        }

        return redirect()->route('dudi.index')
            ->with('success', "Password pembimbing \"{$pembimbing->nama}\" berhasil direset ke dudi123.");
    }

    /**
     * Delete pembimbing industri & linked user account.
     */
    public function destroy(int $dudiId, int $id)
    {
        $pembimbing = PembimbingIndustri::with('user')->where('dudi_id', $dudiId)->findOrFail($id);

        DB::transaction(function () use ($pembimbing) {
            if ($pembimbing->user) {
                $pembimbing->user->delete(); // soft delete
            }
            $pembimbing->delete(); // soft delete
        });

        return redirect()->route('dudi.index')
            ->with('success', 'Akun pembimbing industri berhasil dihapus.');
    }
}
