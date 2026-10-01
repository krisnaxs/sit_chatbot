<?php

namespace App\Http\Controllers;

use App\Models\Knowledge;
use App\Models\KnowledgeAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
     * Ukuran maksimal per file (dalam KB) — 10 MB
     */
    private const MAX_FILE_SIZE_KB = 512000;


    public function index(Request $request)
    {
        $query = Knowledge::withCount('attachments')
            ->with('attachments');    // ← TAMBAHKAN INI

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kata_kunci', 'like', "%{$search}%")
                    ->orWhere('jawaban', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhereHas('attachments', function ($sub) use ($search) {
                        $sub->where('file_name', 'like', "%{$search}%");
                    });
            });
        }

        $items = $query->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('knowledge.index', compact('items'));
    }

    /**
     * Form tambah knowledge — HANYA ADMIN & SUPPORT.
     */
    public function create()
    {
        $this->authorizeEditor();
        return view('knowledge.create');
    }

    /**
     * Simpan knowledge baru + banyak lampiran.
     */
    public function store(Request $request)
    {
        $this->authorizeEditor();

        $data = $request->validate([
            'kata_kunci' => ['required', 'string', 'max:255'],
            'jawaban' => ['required', 'string'],
            'files' => ['nullable', 'array'],
            'files.*' => [
                'file',
                'max:' . self::MAX_FILE_SIZE_KB,
                function ($attribute, $value, $fail) {
                    if (!in_array($value->getMimeType(), self::ALLOWED_MIMES)) {
                        $fail('Jenis file tidak diizinkan: ' . $value->getClientOriginalName());
                    }
                },
            ],
        ], [
            'kata_kunci.required' => 'Kata kunci wajib diisi.',
            'jawaban.required' => 'Jawaban wajib diisi.',
            'files.*.max' => 'Setiap file maksimal 10 MB.',
            'files.*.file' => 'Lampiran harus berupa file valid.',
        ]);

        DB::beginTransaction();

        try {
            $knowledge = Knowledge::create([
                'kata_kunci' => $data['kata_kunci'],
                'jawaban' => $data['jawaban'],
            ]);
            $uploadedCount = 0;
            $failedFiles = [];

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    if (!$file->isValid()) {
                        $failedFiles[] = $file->getClientOriginalName();
                        continue;
                    }

                    try {
                        $path = $this->storeFile($file, $knowledge->id);

                        KnowledgeAttachment::create([
                            'knowledge_id' => $knowledge->id,
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'file_type' => $file->getMimeType(),
                            'file_size' => $file->getSize(),
                        ]);

                        $uploadedCount++;
                    } catch (\Throwable $e) {
                        report($e);
                        $failedFiles[] = $file->getClientOriginalName();
                    }
                }
            }
            $this->syncLegacyColumns($knowledge);

            DB::commit();

            return redirect()
                ->route('knowledge.index')
                ->with('success', $this->buildSuccessMessage(
                    'Knowledge berhasil ditambahkan.',
                    $uploadedCount,
                    $failedFiles
                ));

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
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
        $this->authorizeEditor();
        $knowledge->load('attachments');
        return view('knowledge.edit', compact('knowledge'));
    }

    /**
     * Update knowledge + tambah / hapus lampiran.
     */
    public function update(Request $request, Knowledge $knowledge)
    {
        $this->authorizeEditor();

        $data = $request->validate([
            'kata_kunci' => ['required', 'string', 'max:255'],
            'jawaban' => ['required', 'string'],

            'files' => ['nullable', 'array'],
            'files.*' => [
                'file',
                'max:' . self::MAX_FILE_SIZE_KB,
                function ($attribute, $value, $fail) {
                    if (!in_array($value->getMimeType(), self::ALLOWED_MIMES)) {
                        $fail('Jenis file tidak diizinkan: ' . $value->getClientOriginalName());
                    }
                },
            ],
            'remove_attachments' => ['nullable', 'array'],
            'remove_attachments.*' => ['integer'],
        ], [
            'files.*.max' => 'Setiap file maksimal 10 MB.',
            'files.*.file' => 'Lampiran harus berupa file valid.',
        ]);

        DB::beginTransaction();

        try {
            if (!empty($data['remove_attachments'])) {
                $toDelete = KnowledgeAttachment::where('knowledge_id', $knowledge->id)
                    ->whereIn('id', $data['remove_attachments'])
                    ->get();

                foreach ($toDelete as $att) {
                    $this->deleteFile($att->file_path);
                    $att->delete();
                }
            }
            $uploadedCount = 0;
            $failedFiles = [];

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    if (!$file->isValid()) {
                        $failedFiles[] = $file->getClientOriginalName();
                        continue;
                    }

                    try {
                        $path = $this->storeFile($file, $knowledge->id);

                        KnowledgeAttachment::create([
                            'knowledge_id' => $knowledge->id,
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'file_type' => $file->getMimeType(),
                            'file_size' => $file->getSize(),
                        ]);

                        $uploadedCount++;
                    } catch (\Throwable $e) {
                        report($e);
                        $failedFiles[] = $file->getClientOriginalName();
                    }
                }
            }
            $knowledge->kata_kunci = $data['kata_kunci'];
            $knowledge->jawaban = $data['jawaban'];
            $knowledge->save();
            $this->syncLegacyColumns($knowledge);

            DB::commit();

            $message = 'Knowledge berhasil diperbarui.';
            if ($uploadedCount > 0) {
                $message .= " {$uploadedCount} lampiran baru diunggah.";
            }
            if (!empty($failedFiles)) {
                $message .= ' ' . count($failedFiles) . ' file gagal: '
                    . implode(', ', array_slice($failedFiles, 0, 3))
                    . (count($failedFiles) > 3 ? '...' : '');
            }

            return redirect()
                ->route('knowledge.index')
                ->with('success', $message);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    /**
     * Hapus knowledge — HANYA ADMIN.
     */
    public function destroy(Knowledge $knowledge)
    {
        $this->authorizeAdmin();

        DB::beginTransaction();

        try {
            foreach ($knowledge->attachments as $att) {
                $this->deleteFile($att->file_path);
            }
            Storage::disk('public')->deleteDirectory('knowledge/' . $knowledge->id);
            $knowledge->delete();

            DB::commit();

            return redirect()
                ->route('knowledge.index')
                ->with('success', 'Knowledge berhasil dihapus.');

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    /**
     * Hapus 1 lampiran saja (dipakai di form edit / tombol hapus per-file).
     */
    public function destroyAttachment(KnowledgeAttachment $attachment)
    {
        $this->authorizeEditor();

        DB::beginTransaction();

        try {
            $knowledge = $attachment->knowledge;
            $this->deleteFile($attachment->file_path);
            $attachment->delete();
            if ($knowledge) {
                $this->syncLegacyColumns($knowledge);
            }

            DB::commit();

            return back()->with('success', 'Lampiran berhasil dihapus.');

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->with('error', 'Gagal menghapus lampiran: ' . $e->getMessage());
        }
    }

    /**
     * Simpan file fisik dengan nama unik.
     * Return: path relatif (contoh: "knowledge/12/namafile_ab12cd34.pdf")
     */
    private function storeFile($file, int $knowledgeId): string
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeName = Str::slug($baseName) ?: 'file';
        $fileName = $safeName . '_' . Str::random(8) . '.' . $extension;

        return $file->storeAs('knowledge/' . $knowledgeId, $fileName, 'public');
    }

    /**
     * Hapus file fisik dari storage (kalau ada).
     */
    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Sinkronkan kolom lama (file_path, file_name, file_type, file_size)
     * di tabel knowledge berdasarkan lampiran PERTAMA.
     * Kalau tidak ada lampiran, set null semua.
     */
    private function syncLegacyColumns(Knowledge $knowledge): void
    {
        $first = $knowledge->attachments()->first();

        $knowledge->update([
            'file_path' => $first->file_path ?? null,
            'file_name' => $first->file_name ?? null,
            'file_type' => $first->file_type ?? null,
            'file_size' => $first->file_size ?? null,
        ]);
    }

    /**
     * Build pesan sukses dengan info jumlah file berhasil & gagal.
     */
    private function buildSuccessMessage(string $base, int $uploaded, array $failed): string
    {
        $msg = $base;

        if ($uploaded > 0) {
            $msg .= " {$uploaded} lampiran diunggah.";
        }

        if (!empty($failed)) {
            $msg .= ' ' . count($failed) . ' file gagal: '
                . implode(', ', array_slice($failed, 0, 3))
                . (count($failed) > 3 ? '...' : '');
        }

        return $msg;
    }

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
