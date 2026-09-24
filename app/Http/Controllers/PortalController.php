<?php

namespace App\Http\Controllers;

use App\Models\App;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function index()
    {
        $slides = App::where('is_active', true)
            ->orderBy('slide')
            ->orderBy('clicks', 'desc')
            ->get()
            ->groupBy('slide');

        return view('portal', compact('slides'));
    }

    public function click($id)
    {
        $app = App::findOrFail($id);

        // hitung klik
        $app->increment('clicks');

        return redirect()->away($app->url);
    }
}
