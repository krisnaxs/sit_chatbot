<?php

namespace App\Http\Controllers;

use App\Models\Asset;

class AssetPublicController extends Controller
{
    public function show(string $serial)
    {
        // Decode & sanitize
        $serial = trim(urldecode($serial));

        // Guard: SN kosong atau tidak wajar
        if (empty($serial) || strlen($serial) > 100) {
            abort(404, 'Serial number tidak valid.');
        }

        $asset = Asset::with([
            'category',
            'currentUser.department',
            'currentLocation',
            'ownership.vendor',
            'maintenances' => fn($q) => $q->latest()->limit(5),
        ])
            ->where('serial_number', $serial)
            ->firstOrFail();

        return view('assets.public', compact('asset'));
    }
}
