<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Package;
use App\Models\TourType;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $destination = $request->query('destination');

        $packages = Package::published()->with(['tourType', 'destination'])
            ->when($type, fn ($q) => $q->whereHas('tourType', fn ($t) => $t->where('slug', $type)))
            ->when($destination, fn ($q) => $q->whereHas('destination', fn ($d) => $d->where('slug', $destination)))
            ->orderByDesc('is_featured')->orderBy('sort')->get();

        return view('pages.packages', [
            'packages' => $packages,
            'tourTypes' => TourType::active()->get(),
            'destinations' => Destination::active()->get(),
            'activeType' => $type,
            'activeDestination' => $destination,
        ]);
    }

    public function show(string $slug)
    {
        $package = Package::published()->with(['tourType', 'destination', 'images'])->where('slug', $slug)->firstOrFail();

        $related = Package::published()->where('id', '!=', $package->id)
            ->when($package->tour_type_id, fn ($q) => $q->where('tour_type_id', $package->tour_type_id))
            ->take(3)->get();

        return view('pages.package', compact('package', 'related'));
    }
}
