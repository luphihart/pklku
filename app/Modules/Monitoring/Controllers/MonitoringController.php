<?php

namespace App\Modules\Monitoring\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Monitoring\Services\MonitoringService;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    protected $service;

    public function __construct(MonitoringService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $role = auth()->user()->role;
        $query = \App\Modules\PKL\Models\PenempatanPkl::with(['murid.kelas', 'dudi', 'guru'])
            ->where('status', 'aktif');

        if ($role === 'guru') {
            $guruId = auth()->user()->guru->id;
            $query->where('guru_id', $guruId);
        }

        $placements = $query->get();

        // Group placements by DUDI to display in sidebar list
        $dudiList = [];
        foreach ($placements as $p) {
            if ($p->dudi) {
                if (!isset($dudiList[$p->dudi_id])) {
                    $dudiList[$p->dudi_id] = [
                        'dudi' => $p->dudi,
                        'placements' => []
                    ];
                }
                $dudiList[$p->dudi_id]['placements'][] = $p;
            }
        }

        return view('monitoring::index', compact('placements', 'dudiList'));
    }
}
