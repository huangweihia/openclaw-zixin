<?php

namespace App\Http\Controllers;

use App\Models\PointPackage;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardPointsController extends Controller
{
    public function index(): View
    {
        $packages = Schema::hasTable('point_packages')
            ? PointPackage::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()
            : collect();

        return view('dashboard-points', [
            'packages' => $packages,
            'boostCost' => \App\Services\UserPostBoostService::pointsCost(),
        ]);
    }
}
