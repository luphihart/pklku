<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Services\DashboardService;

class DashboardController extends Controller
{
    protected $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    /**
     * Display unified dashboard.
     */
    public function index()
    {
        $data = $this->service->getDashboardData();
        return view('dashboard::index', $data);
    }
}
