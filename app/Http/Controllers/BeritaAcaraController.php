<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\BeritaAcara;
use App\Models\User;
use App\Services\BeritaAcaraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BeritaAcaraController extends Controller
{
    public function __construct(
        protected BeritaAcaraService $service
    ) {
    }

    /**
     * Daftar berita acara dengan filter.
     */
    public function index(Request $request)
    {
        $query = BeritaAcara::with(['asset.category', 'pihakPertama', 'pihakKedua', 'createdBy']);

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('tanggal_ba', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('tanggal_ba', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nomor_ba', 'like', "%{$s}%")
                    ->orWhere('serial_number', 'like', "%{$s}%")
                    ->orWhere('merk', 'like', "%{$s}%")
                    ->orWhere('model', 'like', "%{$s}%")
                    ->orWhereHas('asset', fn($qa) => $qa->where('serial_number', 'like', "%{$s}%"))
                    ->orWhereHas('pihakPertama', fn($qu) => $qu->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('pihakKedua', fn($qu) => $qu->where('name', 'like', "%{$s}%"));
            });
        }

        $items = $query->orderByDesc('tanggal_ba')
            ->orderByDesc('id')
            ->paginate($request->get('per_page', 25))
            ->withQueryString();

        // Statistik ringkas
        $stats = [
            'total' => BeritaAcara::count(),
            'serah_terima' => BeritaAcara::where('jenis', 'serah_terima')->count(),
            'pengembalian' => BeritaAcara::where('jenis', 'pengembalian')->count(),
            'bulan_ini' => BeritaAcara::whereMonth('tanggal_ba', now()->month)
                ->whereYear('tanggal_ba', now()->year)
                ->count(),
        ];

        // Asset options untuk modal picker
        $assetOptions = Asset::with('category')
            ->withCount('assignments')
            ->whereIn('status', ['available', 'in_use', 'loaned'])
            ->orderBy('serial_number')
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'asset_code' => $a->asset_code,
                    'brand' => $a->brand,
                    'model' => $a->model,
                    'serial_number' => $a->serial_number,
                    'hostname' => $a->hostname,
                    'category' => $a->category?->name ?? '-',
                    'status_label' => $a->status_label ?? ucfirst($a->status),
                    'assignment_count' => $a->assignments_count ?? 0,
                    'url' => route('siam.berita-acara.create-from-asset', [
                        $a,
                        'jenis' => 'serah_terima',
                    ]),
                ];
            })
            ->values()
            ->toArray();

        return view('berita-acara.index', compact('items', 'stats', 'assetOptions'));
    }

    /**
     * Detail berita acara.
     */
    public function show(BeritaAcara $beritaAcara)
    {
        $beritaAcara->load([
            'asset.category',
            'asset.currentUser',
            'assignment.user',
            'loan.user',
            'pihakPertama',
            'pihakKedua',
            'createdBy',
        ]);

        return view('berita-acara.show', compact('beritaAcara'));
    }

    /**
     * Form buat BA manual dari halaman asset show.
     * Query params: jenis (serah_terima|pengembalian), assignment_id (opsional)
     */
    public function createFromAsset(Request $request, Asset $asset)
    {
        $jenis = $request->get('jenis', 'serah_terima');
        if (!in_array($jenis, ['serah_terima', 'pengembalian'])) {
            $jenis = 'serah_terima';
        }

        // Ambil semua assignment dari asset ini
        $assignments = AssetAssignment::with(['user', 'location', 'department', 'assignedBy'])
            ->where('asset_id', $asset->id)
            ->orderByDesc('assigned_at')
            ->get();

        // Filter assignment yang eligible untuk jenis BA ini
        $eligibleAssignments = $assignments->filter(function ($a) use ($jenis) {
            if ($jenis === 'serah_terima') {
                return true; // semua assignment bisa dibuat BAST
            }
            // BAP hanya untuk yang sudah returned
            return $a->returned_at !== null;
        });

        // Cek BA existing per assignment
        $existingBa = [];
        foreach ($assignments as $a) {
            $existingBa[$a->id] = [
                'serah_terima' => $this->service->getBeritaAcara($a, 'serah_terima'),
                'pengembalian' => $this->service->getBeritaAcara($a, 'pengembalian'),
            ];
        }

        // Pilih assignment default
        $selectedAssignmentId = $request->get('assignment_id');
        if (!$selectedAssignmentId && $eligibleAssignments->isNotEmpty()) {
            // Prioritas: assignment yang belum punya BA jenis ini
            $withoutBa = $eligibleAssignments->first(function ($a) use ($jenis, $existingBa) {
                return empty($existingBa[$a->id][$jenis]);
            });
            $selectedAssignmentId = $withoutBa?->id ?? $eligibleAssignments->first()->id;
        }

        // Pejabat penandatangan (admin & support)
        $penandatangan = User::active()
            ->whereIn('role', ['admin', 'support'])
            ->orderBy('name')
            ->get();

        return view('berita-acara.create-from-asset', compact(
            'asset',
            'assignments',
            'eligibleAssignments',
            'existingBa',
            'jenis',
            'selectedAssignmentId',
            'penandatangan'
        ));
    }

    /**
     * Simpan BA manual yang dibuat dari halaman asset.
     */
    public function storeFromAsset(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'assignment_id' => 'required|exists:asset_assignments,id',
            'jenis' => 'required|in:serah_terima,pengembalian',
            'pihak_pertama_id' => 'required|exists:users,id',
            'pihak_pertama_jabatan' => 'nullable|string|max:100',
            'pihak_pertama_nip' => 'nullable|string|max:30',
            'tempat_ba' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ], [
            'assignment_id.required' => 'Record assignment wajib dipilih.',
            'assignment_id.exists' => 'Record assignment tidak valid.',
            'jenis.required' => 'Jenis berita acara wajib dipilih.',
            'jenis.in' => 'Jenis berita acara tidak valid.',
            'pihak_pertama_id.required' => 'Pihak pertama wajib dipilih.',
            'pihak_pertama_id.exists' => 'Pihak pertama tidak valid.',
        ]);

        $assignment = AssetAssignment::with(['asset.category', 'user'])
            ->findOrFail($validated['assignment_id']);

        // Pastikan assignment milik asset ini
        if ($assignment->asset_id !== $asset->id) {
            return back()->withInput()
                ->with('error', 'Assignment tidak sesuai dengan aset.');
        }

        $pihakPertama = User::findOrFail($validated['pihak_pertama_id']);

        if (!in_array($pihakPertama->role, ['admin', 'support'])) {
            return back()->withInput()
                ->with('error', 'Pihak Pertama harus Admin atau Support.');
        }

        // Cek duplikat: sudah ada BA jenis ini untuk assignment ini?
        $existing = $this->service->getBeritaAcara($assignment, $validated['jenis']);
        if ($existing) {
            return redirect()
                ->route('siam.berita-acara.show', $existing)
                ->with('info', "Berita Acara {$existing->nomor_ba} sudah ada untuk assignment ini.");
        }

        try {
            $ba = $this->service->generateFromAssignment(
                $assignment,
                $pihakPertama,
                $validated['jenis'],
                [
                    'pihak_pertama_jabatan' => $validated['pihak_pertama_jabatan'] ?? null,
                    'pihak_pertama_nip' => $validated['pihak_pertama_nip'] ?? null,
                    'tempat_ba' => $validated['tempat_ba'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            return redirect()
                ->route('siam.berita-acara.show', $ba)
                ->with('success', "Berita Acara {$ba->nomor_ba} berhasil dibuat.");
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()
                ->with('error', 'Gagal membuat berita acara: ' . $e->getMessage());
        }
    }

    /**
     * Download PDF.
     */
    public function download(BeritaAcara $beritaAcara)
    {
        $this->ensurePdfExists($beritaAcara);

        return Storage::disk('public')->download(
            $beritaAcara->pdf_path,
            "{$beritaAcara->nomor_ba}.pdf"
        );
    }

    /**
     * Preview PDF di browser (inline).
     */
    public function preview(BeritaAcara $beritaAcara)
    {
        $this->ensurePdfExists($beritaAcara);

        return response()->file(
            Storage::disk('public')->path($beritaAcara->pdf_path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $beritaAcara->nomor_ba . '.pdf"',
            ]
        );
    }

    /**
     * Regenerate PDF (kalau ada perubahan data).
     */
    public function regenerate(BeritaAcara $beritaAcara)
    {
        try {
            $this->service->generatePdf($beritaAcara);

            return back()->with('success', 'PDF berhasil di-regenerate.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal regenerate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Hapus berita acara PERMANEN (hard delete).
     */
    public function destroy(BeritaAcara $beritaAcara)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa menghapus berita acara.');
        }

        // Hapus file PDF dulu
        if ($beritaAcara->pdf_path && Storage::disk('public')->exists($beritaAcara->pdf_path)) {
            Storage::disk('public')->delete($beritaAcara->pdf_path);
        }

        //HARD DELETE
        $beritaAcara->forceDelete();

        return redirect()
            ->route('siam.berita-acara.index')
            ->with('success', 'Berita acara berhasil dihapus permanen.');
    }

    /**
     * Helper: pastikan PDF ada, generate kalau belum.
     */
    protected function ensurePdfExists(BeritaAcara $beritaAcara): void
    {
        if (!$beritaAcara->pdf_path || !Storage::disk('public')->exists($beritaAcara->pdf_path)) {
            $this->service->generatePdf($beritaAcara);
            $beritaAcara->refresh();
        }
    }
}
