<?php

namespace App\Http\Controllers;

use App\Models\App;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    /**
     * Halaman utama portal — auto-deteksi browser.
     */
    public function index(Request $request)
    {
        $slides = $this->getSlides();
        $view = $this->isLegacyBrowser($request)
            ? 'portal-legacy'
            : 'portal';

        return view($view, compact('slides'));
    }

    /**
     * Paksa tampil versi legacy (untuk user yang pilih manual).
     */
    public function legacy()
    {
        $slides = $this->getSlides();

        return view('portal-legacy', compact('slides'));
    }

    /**
     * Handle klik aplikasi.
     */
    public function click($id)
    {
        $app = App::findOrFail($id);
        $app->increment('clicks');

        return redirect()->away($app->url);
    }

    /**
     * Query slides — dipakai oleh index() & legacy().
     */
    private function getSlides()
    {
        return App::where('is_active', true)
            ->orderBy('slide')
            ->orderBy('clicks', 'desc')
            ->get()
            ->groupBy('slide');
    }

    /**
     * Deteksi browser lama.
     * Return true kalau: Firefox < 60, Chrome < 60, Safari < 11, IE.
     */
    private function isLegacyBrowser(Request $request): bool
    {
        $ua = $request->header('User-Agent', '');
        if (preg_match('/Firefox\/(\d+)/', $ua, $m)) {
            if ((int) $m[1] < 60)
                return true;
        }
        if (preg_match('/Chrome\/(\d+)/', $ua, $m)) {
            if ((int) $m[1] < 60 && strpos($ua, 'Edg') === false) {
                return true;
            }
        }
        if (preg_match('/Version\/(\d+).*Safari/', $ua, $m)) {
            if ((int) $m[1] < 11)
                return true;
        }
        if (preg_match('/MSIE|Trident/', $ua)) {
            return true;
        }

        return false;
    }
}
