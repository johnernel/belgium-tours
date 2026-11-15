<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Package;
use App\Services\GhlClient;

class DashboardController extends Controller
{
    public function __invoke(GhlClient $ghl)
    {
        return view('admin.dashboard', [
            'weekCount' => Enquiry::where('created_at', '>=', now()->subDays(7))->count(),
            'newCount' => Enquiry::where('status', 'new')->count(),
            'totalCount' => Enquiry::count(),
            'recent' => Enquiry::latest()->take(8)->get(),
            'publishedPackages' => Package::published()->count(),
            'ghlOn' => $ghl->enabled(),
            'ghlFailed' => Enquiry::where('ghl_status', 'failed')->count(),
        ]);
    }
}
