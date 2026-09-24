<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Knowledge;
use App\Models\PendingKnowledge; // ⬅️ TAMBAHKAN INI (sesuaikan nama model)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        // ============================================================
        // 1. STATISTIK UTAMA
        // ============================================================
        $totalChat = Chat::count();
        $totalKnowledge = Knowledge::count();
        $chatHariIni = Chat::whereDate('waktu', today())->count();
        $chatMingguIni = Chat::whereBetween('waktu', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])->count();

        $knowledgeWithFile = 0;
        if (Schema::hasColumn('knowledge', 'file_path')) {
            $knowledgeWithFile = Knowledge::whereNotNull('file_path')->count();
        }

        $chatPerHari = round(
            Chat::whereBetween('waktu', [
                now()->subDays(6)->startOfDay(),
                now()->endOfDay(),
            ])->count() / 7,
            1
        );

        // ============================================================
        // 2. TREN vs KEMARIN
        // ============================================================
        $chatKemarin = Chat::whereDate('waktu', now()->subDay())->count();
        $trendHariIni = $chatKemarin > 0
            ? round((($chatHariIni - $chatKemarin) / $chatKemarin) * 100, 1)
            : ($chatHariIni > 0 ? 100 : 0);

        // ============================================================
        // 3. GRAFIK 7 HARI
        // ============================================================
        $grafik = Chat::select(
            DB::raw('DATE(waktu) as tanggal'),
            DB::raw('COUNT(*) as total')
        )
            ->where('waktu', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->pluck('total', 'tanggal');

        $labels = [];
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d M');
            $data[] = $grafik[$tgl] ?? 0;
        }

        // ============================================================
        // 4. CHAT TERBARU
        // ============================================================
        $chatTerbaru = Chat::orderByDesc('id')->limit(8)->get();

        // ============================================================
        // 5. TOP PERTANYAAN
        // ============================================================
        $topPertanyaan = Chat::select('pesan', DB::raw('COUNT(*) as total'))
            ->groupBy('pesan')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // ============================================================
        // 6. JAM SIBUK
        // ============================================================
        $jamSibuk = Chat::select(
            DB::raw('HOUR(waktu) as jam'),
            DB::raw('COUNT(*) as total')
        )
            ->where('waktu', '>=', now()->subDays(30))
            ->groupBy('jam')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ============================================================
        // 7. KNOWLEDGE TIDAK TERPAKAI
        // ============================================================
        $knowledgeUnused = Knowledge::select('id', 'kata_kunci')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('chat')
                    ->whereColumn('chat.jawaban', 'knowledge.jawaban');
            })
            ->limit(5)
            ->get();

        // ============================================================
        // 8. DETEKSI KB vs AI
        // ============================================================
        if (Schema::hasColumn('chat', 'sumber')) {
            $chatDariKB = Chat::where('sumber', 'database')->count();
            $chatDariAI = Chat::where('sumber', 'ai')->count();
        } else {
            $chatDariKB = 0;
            $chatDariAI = 0;

            $knowledgeJawaban = Knowledge::pluck('jawaban')->toArray();
            $recentChats = Chat::orderByDesc('id')->limit(200)->get();

            foreach ($recentChats as $chat) {
                if (in_array($chat->jawaban, $knowledgeJawaban)) {
                    $chatDariKB++;
                } else {
                    $chatDariAI++;
                }
            }
        }

        $totalDijawab = $chatDariKB + $chatDariAI;
        $persenKB = $totalDijawab > 0 ? round(($chatDariKB / $totalDijawab) * 100, 1) : 0;
        $persenAI = $totalDijawab > 0 ? round(($chatDariAI / $totalDijawab) * 100, 1) : 0;

        // ============================================================
        // 9. TOP KNOWLEDGE
        // ============================================================
        $topKnowledge = DB::table('knowledge')
            ->join('chat', 'chat.jawaban', '=', 'knowledge.jawaban')
            ->select(
                'knowledge.id',
                'knowledge.kata_kunci',
                'knowledge.jawaban',
                DB::raw('COUNT(chat.id) as total')
            )
            ->groupBy('knowledge.id', 'knowledge.kata_kunci', 'knowledge.jawaban')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ============================================================
        // 10. STATISTIK PENDING KNOWLEDGE (AI yang sudah dipelajari)
        // ============================================================
        // Total per status
        $pendingTotal = PendingKnowledge::where('status', 'pending')->count();
        $pendingApproved = PendingKnowledge::where('status', 'approved')->count();
        $pendingRejected = PendingKnowledge::where('status', 'rejected')->count();

        // Total semua + persentase sudah dipelajari
        $pendingTotalAll = $pendingApproved + $pendingRejected + $pendingTotal;
        $persenApproved = $pendingTotalAll > 0
            ? round(($pendingApproved / $pendingTotalAll) * 100, 1)
            : 0;
        $persenPending = $pendingTotalAll > 0
            ? round(($pendingTotal / $pendingTotalAll) * 100, 1)
            : 0;
        $persenRejected = $pendingTotalAll > 0
            ? round(($pendingRejected / $pendingTotalAll) * 100, 1)
            : 0;

        // Pending & approved hari ini
        $pendingHariIni = PendingKnowledge::where('status', 'pending')
            ->whereDate('created_at', today())
            ->count();

        $approvedHariIni = PendingKnowledge::where('status', 'approved')
            ->whereDate('updated_at', today())
            ->count();

        // Top 5 pending yang paling sering ditanya (belum dipelajari)
        $topPending = PendingKnowledge::where('status', 'pending')
            ->orderByDesc('frequency')
            ->limit(5)
            ->get(['id', 'pesan_user', 'frequency', 'created_at']);

        // Tren 7 hari terakhir: berapa yang di-approve per hari
        $approvedPerHari = PendingKnowledge::where('status', 'approved')
            ->where('updated_at', '>=', now()->subDays(6)->startOfDay())
            ->select(
                DB::raw('DATE(updated_at) as tanggal'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->pluck('total', 'tanggal');

        $pendingLabels = [];
        $pendingData = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl = now()->subDays($i)->format('Y-m-d');
            $pendingLabels[] = now()->subDays($i)->format('d M');
            $pendingData[] = $approvedPerHari[$tgl] ?? 0;
        }

        return view('dashboard', compact(
            // Statistik utama
            'totalChat',
            'totalKnowledge',
            'chatHariIni',
            'chatMingguIni',
            'knowledgeWithFile',
            'chatPerHari',

            // Grafik
            'labels',
            'data',

            // List
            'chatTerbaru',
            'topPertanyaan',
            'topKnowledge',
            'jamSibuk',
            'knowledgeUnused',

            // Analisis
            'chatDariKB',
            'chatDariAI',
            'persenKB',
            'persenAI',
            'trendHariIni',
            'chatKemarin',

            // Pending Knowledge stats
            'pendingTotal',
            'pendingApproved',
            'pendingRejected',
            'pendingTotalAll',
            'persenApproved',
            'persenPending',
            'persenRejected',
            'pendingHariIni',
            'approvedHariIni',
            'topPending',
            'pendingLabels',
            'pendingData',
        ));
    }
}
