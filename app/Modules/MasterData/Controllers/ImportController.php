<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\Services\ExcelImportService;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    protected $importService;

    public function __construct(ExcelImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Download template.
     */
    public function downloadTemplate(string $type)
    {
        $validTypes = ['murid', 'guru', 'dudi', 'penempatan'];
        if (!in_array($type, $validTypes)) {
            abort(404);
        }

        $this->importService->downloadTemplate($type);
    }

    /**
     * Handle Excel file import.
     */
    public function import(Request $request, string $type)
    {
        $validTypes = ['murid', 'guru', 'dudi', 'penempatan'];
        if (!in_array($type, $validTypes)) {
            abort(404);
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:5120', // Max 5MB
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx atau .xls).',
            'file.max' => 'Ukuran file maksimal adalah 5MB.',
        ]);

        try {
            $file = $request->file('file');
            
            switch ($type) {
                case 'murid':
                    $this->importService->importMurid($file->getRealPath());
                    break;
                case 'guru':
                    $this->importService->importGuru($file->getRealPath());
                    break;
                case 'dudi':
                    $this->importService->importDudi($file->getRealPath());
                    break;
                case 'penempatan':
                    $this->importService->importPenempatan($file->getRealPath());
                    break;
            }

            return back()->with('success', "Data " . ucfirst($type) . " berhasil diimpor massal!");
        } catch (\Throwable $e) {
            // Transaction is automatically rolled back if Exception thrown in DB::transaction
            return back()->with('error', "Gagal Impor! " . $e->getMessage());
        }
    }
}
