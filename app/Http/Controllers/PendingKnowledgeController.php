<?php

namespace App\Http\Controllers;

use App\Models\Knowledge;
use App\Models\PendingKnowledge;
use Illuminate\Http\Request;

class PendingKnowledgeController extends Controller
{
    /**
     * Tampilkan daftar pending knowledge.
     */
    public function index(Request $request)
    {
        $query = PendingKnowledge::where('status', 'pending')
            ->orderByDesc('frequency')
            ->orderByDesc('id');

        // Filter: search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pesan_user', 'like', "%{$search}%")
                    ->orWhere('jawaban_ai', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(20)->withQueryString();

        // Statistik
        $totalPending = PendingKnowledge::where('status', 'pending')->count();
        $totalToday = PendingKnowledge::where('status', 'pending')
            ->whereDate('created_at', today())->count();
        $totalApproved = PendingKnowledge::where('status', 'approved')->count();
        $totalRejected = PendingKnowledge::where('status', 'rejected')->count();

        return view('knowledge.pending', compact(
            'items',
            'totalPending',
            'totalToday',
            'totalApproved',
            'totalRejected'
        ));
    }

    /**
     * Approve — pindahkan ke knowledge.
     */
    public function approve(Request $request, PendingKnowledge $pending)
    {
        $data = $request->validate([
            'kata_kunci' => ['required', 'string', 'max:255'],
            'jawaban' => ['required', 'string'],
        ]);

        // Simpan ke knowledge
        Knowledge::create([
            'kata_kunci' => $data['kata_kunci'],
            'jawaban' => $data['jawaban'],
        ]);

        // Update status pending
        $pending->status = 'approved';
        $pending->save();

        return redirect()
            ->route('knowledge.pending')
            ->with('success', 'Knowledge berhasil ditambahkan dari pending.');
    }

    /**
     * Reject — hapus dari pending.
     */
    public function reject(PendingKnowledge $pending)
    {
        $pending->status = 'rejected';
        $pending->save();

        return redirect()
            ->route('knowledge.pending')
            ->with('success', 'Pending knowledge ditolak.');
    }

    /**
     * Bersihkan semua pending (yang sudah di-reject & approved).
     */
    public function clear()
    {
        $count = PendingKnowledge::whereIn('status', ['approved', 'rejected'])->count();
        PendingKnowledge::whereIn('status', ['approved', 'rejected'])->delete();

        return redirect()
            ->route('knowledge.pending')
            ->with('success', "{$count} pending lama berhasil dibersihkan.");
    }
}
