<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Tampilkan daftar activity log.
     */
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        // Filter: log_name
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        // Filter: user (causer)
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        // Filter: date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter: search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('properties', 'like', "%{$search}%");
            });
        }

        $activities = $query->paginate(20)->withQueryString();

        // Statistik
        $logNames = Activity::select('log_name')
            ->distinct()
            ->whereNotNull('log_name')
            ->orderBy('log_name')
            ->pluck('log_name');

        $totalAll = Activity::count();
        $totalToday = Activity::whereDate('created_at', today())->count();
        $totalWeek = Activity::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])->count();

        return view('activity.index', compact(
            'activities',
            'logNames',
            'totalAll',
            'totalToday',
            'totalWeek'
        ));
    }

    /**
     * 🆕 Tampilkan detail satu log.
     */
    public function show(Activity $activity)
    {
        // Load relasi causer & subject kalau ada
        $activity->load(['causer', 'subject']);

        // Cari log sebelum & sesudah (untuk navigasi)
        $prev = Activity::where('id', '<', $activity->id)
            ->orderByDesc('id')
            ->first();

        $next = Activity::where('id', '>', $activity->id)
            ->orderBy('id')
            ->first();

        return view('activity.show', compact('activity', 'prev', 'next'));
    }

    /**
     * Hapus satu log — hanya admin.
     */
    public function destroy(Activity $activity)
    {
        $this->authorizeAdmin();

        $activity->delete();

        return redirect()->route('activity.index')
            ->with('success', 'Log berhasil dihapus.');
    }

    /**
     * Bersihkan semua log — hanya admin.
     */
    public function clear()
    {
        $this->authorizeAdmin();

        $count = Activity::count();
        Activity::truncate();

        return redirect()->route('activity.index')
            ->with('success', "Semua {$count} log berhasil dibersihkan.");
    }

    /**
     * 🔒 Cek apakah user yang login adalah admin.
     * Kalau bukan → 403 Forbidden.
     */
    private function authorizeAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa menghapus activity log.');
        }
    }
}
