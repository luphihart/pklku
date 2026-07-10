<?php

namespace App\Modules\MasterData\Services;

use App\Modules\MasterData\Repositories\MasterDataRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class MasterDataService
{
    protected $repo;

    public function __construct(MasterDataRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    // Murid Actions
    public function listMurid(array $filters = []) { return $this->repo->getAllMurid($filters); }
    public function getMurid(int $id) { return $this->repo->findMuridById($id); }
    
    public function saveMurid(array $data)
    {
        $murid = $this->repo->createMurid($data);
        $this->logActivity("Menambahkan murid baru: " . $murid->nama);
        return $murid;
    }

    public function editMurid(int $id, array $data)
    {
        $result = $this->repo->updateMurid($id, $data);
        $this->logActivity("Mengubah data murid dengan ID: " . $id);
        return $result;
    }

    public function removeMurid(int $id)
    {
        $result = $this->repo->deleteMurid($id);
        $this->logActivity("Menghapus murid dengan ID: " . $id);
        return $result;
    }

    // Guru Actions
    public function listGuru() { return $this->repo->getAllGuru(); }
    public function getGuru(int $id) { return $this->repo->findGuruById($id); }

    public function saveGuru(array $data)
    {
        $guru = $this->repo->createGuru($data);
        $this->logActivity("Menambahkan guru baru: " . $guru->nama);
        return $guru;
    }

    public function editGuru(int $id, array $data)
    {
        $result = $this->repo->updateGuru($id, $data);
        $this->logActivity("Mengubah data guru dengan ID: " . $id);
        return $result;
    }

    public function removeGuru(int $id)
    {
        $result = $this->repo->deleteGuru($id);
        $this->logActivity("Menghapus guru dengan ID: " . $id);
        return $result;
    }

    // Dudi Actions
    public function listDudi() { return $this->repo->getAllDudi(); }
    public function getDudi(int $id) { return $this->repo->findDudiById($id); }

    public function saveDudi(array $data)
    {
        $dudi = $this->repo->createDudi($data);
        $this->logActivity("Menambahkan mitra DUDI baru: " . $dudi->nama);
        return $dudi;
    }

    public function editDudi(int $id, array $data)
    {
        $result = $this->repo->updateDudi($id, $data);
        $this->logActivity("Mengubah data mitra DUDI dengan ID: " . $id);
        return $result;
    }

    public function removeDudi(int $id)
    {
        $result = $this->repo->deleteDudi($id);
        $this->logActivity("Menghapus mitra DUDI dengan ID: " . $id);
        return $result;
    }

    /**
     * Audit log helper.
     */
    private function logActivity(string $aktivitas)
    {
        try {
            \App\Modules\System\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'aktivitas' => $aktivitas,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Unknown',
                'payload' => null,
            ]);
        } catch (\Throwable $e) {
            // Ignore
        }
    }
}
