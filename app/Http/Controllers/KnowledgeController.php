<?php

namespace App\Http\Controllers;

use App\Models\Knowledge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KnowledgeController extends Controller
{
    /**
     * MIME types yang diizinkan untuk upload
     */
    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/svg+xml',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
        'application/rtf',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/zip',
        'application/x-rar-compressed',
        'application/vnd.rar',
        'application/x-7z-compressed',
    ];

    /**
     * Tampilkan daftar knowledge — SEMUA ROLE.
     */
    public function index()
    {
        $items = Knowledge::orderByDesc('id')->paginate(10);
        return view('knowledge.index', compact('items'));
    }

    /**
     * Form tambah knowledge — HANYA ADMIN & SUPPORT.
     */
    public function create()
    {
        $this->authorizeEditor();  // 🔒
        return view('knowledge.create');
    }

    /**
     * Simpan knowledge baru — HANYA ADMIN & SUPPORT.
     */
    public function store(Request $request)
    {
        $this->authorizeEditor();  // 🔒

        $data = $request->validate([
            'kata_kunci' => ['required', 'string', 'max:255'],
            'jawaban' => ['required', 'string'],
            'file' => [
                'nullable',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    if (!in_array($value->getMimeType(), self::ALLOWED_MIMES)) {
                        $fail('Jenis file tidak diizinkan. File: ' . $value->getClientOriginalName());
                    }
                },
            ],
        ], [
            'kata_kunci.required' => 'Kata kunci wajib diisi.',
            'jawaban.required' => 'Jawaban wajib diisi.',
            'file.max' => 'File maksimal 10 MB.',
        ]);

        $fileData = $this->handleUpload($request);

        Knowledge::create([
            'kata_kunci' => $data['kata_kunci'],
            'jawaban' => $data['jawaban'],
            'file_path' => $fileData['file_path'],
            'file_name' => $fileData['file_name'],
            'file_type' => $fileData['file_type'],
            'file_size' => $fileData['file_size'],
        ]);

        return redirect()
            ->route('knowledge.index')
            ->with('success', 'Knowledge berhasil ditambahkan.');
    }

    /**
     * Detail (redirect ke edit).
     */
    public function show(Knowledge $knowledge)
    {
        return redirect()->route('knowledge.edit', $knowledge);
    }

    /**
     * Form edit knowledge — HANYA ADMIN & SUPPORT.
     */
    public function edit(Knowledge $knowledge)
    {
        $this->authorizeEditor();  // 🔒
        return view('knowledge.edit', compact('knowledge'));
    }

    /**
     * Update knowledge — HANYA ADMIN & SUPPORT.
     */
    public function update(Request $request, Knowledge $knowledge)
    {
        $this->authorizeEditor();  // 🔒

        $data = $request->validate([
            'kata_kunci' => ['required', 'string', 'max:255'],
            'jawaban' => ['required', 'string'],
            'file' => [
                'nullable',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    if (!in_array($value->getMimeType(), self::ALLOWED_MIMES)) {
                        $fail('Jenis file tidak diizinkan. File: ' . $value->getClientOriginalName());
                    }
                },
            ],
        ], [
            'kata_kunci.required' => 'Kata kunci wajib diisi.',
            'jawaban.required' => 'Jawaban wajib diisi.',
            'file.max' => 'File maksimal 10 MB.',
        ]);

        // Hapus file jika user centang "hapus file"
        if ($request->boolean('remove_file')) {
            if ($knowledge->file_path) {
                Storage::disk('public')->delete($knowledge->file_path);
            }
            $knowledge->file_path = null;
            $knowledge->file_name = null;
            $knowledge->file_type = null;
            $knowledge->file_size = null;
        }

        // Upload file baru
        if ($request->hasFile('file')) {
            if ($knowledge->file_path) {
                Storage::disk('public')->delete($knowledge->file_path);
            }

            $fileData = $this->handleUpload($request);
            $knowledge->file_path = $fileData['file_path'];
            $knowledge->file_name = $fileData['file_name'];
            $knowledge->file_type = $fileData['file_type'];
            $knowledge->file_size = $fileData['file_size'];
        }

        $knowledge->kata_kunci = $data['kata_kunci'];
        $knowledge->jawaban = $data['jawaban'];
        $knowledge->save();

        return redirect()
            ->route('knowledge.index')
            ->with('success', 'Knowledge berhasil diperbarui.');
    }

    /**
     * Hapus knowledge — HANYA ADMIN.
     */
    public function destroy(Knowledge $knowledge)
    {
        $this->authorizeAdmin();  // 🔒 hanya admin

        if ($knowledge->file_path) {
            Storage::disk('public')->delete($knowledge->file_path);
        }

        $knowledge->delete();

        return redirect()
            ->route('knowledge.index')
            ->with('success', 'Knowledge berhasil dihapus.');
    }

    /**
     * Handle upload file
     */
    private function handleUpload(Request $request): array
    {
        if (!$request->hasFile('file')) {
            return ['file_path' => null, 'file_name' => null, 'file_type' => null, 'file_size' => null];
        }

        $uploaded = $request->file('file');

        return [
            'file_path' => $uploaded->store('knowledge', 'public'),
            'file_name' => $uploaded->getClientOriginalName(),
            'file_type' => $uploaded->getMimeType(),
            'file_size' => $uploaded->getSize(),
        ];
    }

    // ============================================================
    // AUTHORIZATION HELPERS
    // ============================================================

    /**
     * 🔒 Hanya admin & support.
     */
    private function authorizeEditor(): void
    {
        if (!auth()->check() || !auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403, 'Hanya admin & support yang bisa mengelola knowledge.');
        }
    }

    /**
     * 🔒 Hanya admin.
     */
    private function authorizeAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa menghapus knowledge.');
        }
    }
}
