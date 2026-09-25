<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\App;
use Illuminate\Support\Facades\Storage;

class AppController extends Controller
{
    /**
     * Tampilkan daftar aplikasi — SEMUA ROLE.
     */
    public function index()
    {
        $apps = App::orderBy('slide')->orderBy('urutan')->get();
        return view('apps.index', compact('apps'));
    }

    /**
     * Form tambah aplikasi — admin & support.
     */
    public function create()
    {
        $this->authorizeSupport();  // 🔒 admin & support

        return view('apps.form', [
            'app' => new App(),
            'action' => route('apps.store'),
            'method' => 'POST'
        ]);
    }

    /**
     * Simpan aplikasi baru — admin & support.
     */
    public function store(Request $request)
    {
        $this->authorizeSupport();  // 🔒 admin & support

        $request->validate([
            'nama' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'gambar' => 'nullable|image|max:2048',
            'slide' => 'required|string|max:100',
            'urutan' => 'nullable|integer',
            'is_active' => 'nullable'
        ]);

        $gambarPath = null;
        $file = $request->file('gambar');

        if (
            $file instanceof \Illuminate\Http\UploadedFile
            && $file->isValid()
            && $file->getSize() > 0
            && $file->getRealPath() !== false
        ) {
            $gambarPath = $file->store('apps', 'public');
        }

        $isActive = $request->has('is_active');

        App::create([
            'nama' => $request->nama,
            'url' => $request->url,
            'gambar' => $gambarPath,
            'slide' => $request->slide,
            'urutan' => $request->urutan ?? 1,
            'is_active' => $isActive,
        ]);

        // ✅ Log otomatis dari middleware LogUserActivity

        return redirect()->route('apps.index')->with('success', 'Aplikasi berhasil ditambahkan!');
    }

    /**
     * Form edit aplikasi — HANYA ADMIN.
     */
    public function edit(App $app)
    {
        $this->authorizeAdmin();  // 🔒 admin saja

        return view('apps.form', [
            'app' => $app,
            'action' => route('apps.update', $app),
            'method' => 'PUT'
        ]);
    }

    /**
     * Update aplikasi — HANYA ADMIN.
     */
    public function update(Request $request, App $app)
    {
        $this->authorizeAdmin();  // 🔒 admin saja

        $request->validate([
            'nama' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'gambar' => 'nullable|image|max:2048',
            'slide' => 'required|string|max:100',
            'urutan' => 'nullable|integer',
            'is_active' => 'nullable'
        ]);

        $file = $request->file('gambar');

        if (
            $file instanceof \Illuminate\Http\UploadedFile
            && $file->isValid()
            && $file->getSize() > 0
            && $file->getRealPath() !== false
        ) {
            if ($app->gambar) {
                Storage::disk('public')->delete($app->gambar);
            }

            $app->gambar = $file->store('apps', 'public');
        }

        $app->nama = $request->nama;
        $app->url = $request->url;
        $app->slide = $request->slide;
        $app->urutan = $request->urutan ?? 1;
        $app->is_active = $request->has('is_active');
        $app->save();

        // ✅ Log otomatis dari middleware LogUserActivity

        return redirect()->route('apps.index')->with('success', 'Aplikasi berhasil diperbarui!');
    }

    /**
     * Hapus aplikasi — HANYA ADMIN.
     */
    public function destroy(App $app)
    {
        $this->authorizeAdmin();  // 🔒 admin saja

        if ($app->gambar) {
            Storage::disk('public')->delete($app->gambar);
        }

        $app->delete();

        // ✅ Log otomatis dari middleware LogUserActivity

        return redirect()->route('apps.index')->with('success', 'Aplikasi berhasil dihapus!');
    }

    // ============================================================
    // AUTHORIZATION HELPERS
    // ============================================================

    /**
     * 🔒 Hanya admin.
     */
    private function authorizeAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa mengakses halaman ini.');
        }
    }

    /**
     * 🔒 Admin & Support.
     */
    private function authorizeSupport(): void
    {
        if (!auth()->check() || !auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403, 'Hanya admin & support yang bisa menambah aplikasi.');
        }
    }
}
