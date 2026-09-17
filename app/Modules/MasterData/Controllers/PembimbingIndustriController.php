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
    public function store(Request $request, $dudi_id = null)
    {
        $dudiId = $dudi_id ?? $request->route('dudi_id') ?? $request->route('dudi');
        $dudi = Dudi::findOrFail($dudiId);

        $request->validate([
            'nama'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:100|unique:users,email',
        ], [
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        try {
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
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error store pembimbing industri: ' . $e->getMessage());
            return redirect()->route('dudi.index')
                ->with('error', 'Gagal membuat akun pembimbing: ' . $e->getMessage());
        }
    }

    /**
     * Update pembimbing industri data & linked user.
     */
    public function update(Request $request, $dudi_id = null, $id = null)
    {
        $dudiId = $dudi_id ?? $request->route('dudi_id') ?? $request->route('dudi');
        $targetId = $id ?? $request->route('id') ?? $request->route('pembimbing');
        $pembimbing = PembimbingIndustri::with('user')->where('dudi_id', $dudiId)->findOrFail($targetId);

        $request->validate([
            'nama'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:100|unique:users,email,' . ($pembimbing->user_id ?? 'NULL'),
        ], [
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        try {
            DB::transaction(function () use ($request, $pembimbing) {
                $pembimbing->update([
                    'nama'  => $request->nama,
                    'phone' => $request->phone,
                    'email' => $request->email,
                ]);

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
        } catch (\Throwable $e) {
            return redirect()->route('dudi.index')
                ->with('error', 'Gagal memperbarui data pembimbing: ' . $e->getMessage());
        }
    }

    /**
     * Reset password pembimbing to dudi123.
     */
    public function resetPassword(Request $request, $dudi_id = null, $id = null)
    {
        $dudiId = $dudi_id ?? $request->route('dudi_id') ?? $request->route('dudi');
        $targetId = $id ?? $request->route('id') ?? $request->route('pembimbing');
        $pembimbing = PembimbingIndustri::with('user')->where('dudi_id', $dudiId)->findOrFail($targetId);

        try {
            if ($pembimbing->user) {
                $pembimbing->user->update(['password' => Hash::make('dudi123')]);
            }

            return redirect()->route('dudi.index')
                ->with('success', "Password pembimbing \"{$pembimbing->nama}\" berhasil direset ke dudi123.");
        } catch (\Throwable $e) {
            return redirect()->route('dudi.index')
                ->with('error', 'Gagal mereset password: ' . $e->getMessage());
        }
    }

    /**
     * Delete pembimbing industri & linked user account.
     */
    public function destroy(Request $request, $dudi_id = null, $id = null)
    {
        $dudiId = $dudi_id ?? $request->route('dudi_id') ?? $request->route('dudi');
        $targetId = $id ?? $request->route('id') ?? $request->route('pembimbing');
        $pembimbing = PembimbingIndustri::with('user')->where('dudi_id', $dudiId)->findOrFail($targetId);

        try {
            DB::transaction(function () use ($pembimbing) {
                if ($pembimbing->user) {
                    $pembimbing->user->delete(); // soft delete
                }
                $pembimbing->delete(); // soft delete
            });

            return redirect()->route('dudi.index')
                ->with('success', 'Akun pembimbing industri berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->route('dudi.index')
                ->with('error', 'Gagal menghapus akun: ' . $e->getMessage());
        }
    }
}
