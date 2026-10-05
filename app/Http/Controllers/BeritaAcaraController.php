<?php

namespace App\Http\Controllers;

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

        // 🆕 Statistik ringkas
        $stats = [
            'total' => BeritaAcara::count(),
            'serah_terima' => BeritaAcara::where('jenis', 'serah_terima')->count(),
            'pengembalian' => BeritaAcara::where('jenis', 'pengembalian')->count(),
            'bulan_ini' => BeritaAcara::whereMonth('tanggal_ba', now()->month)
                ->whereYear('tanggal_ba', now()->year)
                ->count(),
        ];

        return view('berita-acara.index', compact('items', 'stats'));
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
     * 🆕 Regenerate PDF (kalau ada perubahan data).
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
     * 🆕 Hapus berita acara (soft delete).
     */
    public function destroy(BeritaAcara $beritaAcara)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa menghapus berita acara.');
        }

        // Hapus file PDF
        if ($beritaAcara->pdf_path && Storage::disk('public')->exists($beritaAcara->pdf_path)) {
            Storage::disk('public')->delete($beritaAcara->pdf_path);
        }

        $beritaAcara->delete();

        return redirect()
            ->route('siam.berita-acara.index')
            ->with('success', 'Berita acara berhasil dihapus.');
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
