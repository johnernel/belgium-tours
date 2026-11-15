<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnquiryRequest;
use App\Services\EnquiryService;

class EnquiryController extends Controller
{
    /** One endpoint for every form. JSON for the Alpine forms, redirect for the no-JS fallback. */
    public function store(EnquiryRequest $request, EnquiryService $service)
    {
        $service->create($request->validated(), $request);

        $redirect = route('thank-you');

        return $request->expectsJson()
            ? response()->json(['redirect' => $redirect])
            : redirect($redirect);
    }
}
