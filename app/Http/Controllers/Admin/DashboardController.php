<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\DashboardStats;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $days = DashboardStats::period($request->query('days'));
        $stats = new DashboardStats($days);

        return view('admin.dashboard', [
            'days' => $days,
            'summary' => $stats->summary(),
            'chart' => $stats->perDay(),
            'pipeline' => $stats->pipeline(),
            'sources' => $stats->sources(),
            'campaigns' => $stats->campaigns(),
            'forms' => $stats->forms(),
            'journeys' => $stats->journeys(),
            'recent' => $stats->recent(),
            'waiting' => $stats->waiting(),
            'failed' => $stats->failedSyncs(),
            'health' => $stats->health(),
            'publishedPackages' => Package::published()->count(),
        ]);
    }
}
