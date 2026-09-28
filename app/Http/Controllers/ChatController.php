<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Knowledge;
use App\Models\PendingKnowledge;
use App\Services\AutoLearningService;
use App\Services\ChatMemoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private const STOPWORDS = [
        'yang',
        'dan',
        'atau',
        'di',
        'ke',
        'dari',
        'untuk',
        'pada',
        'dengan',
        'adalah',
        'itu',
        'ini',
        'saya',
        'kamu',
        'anda',
        'apa',
        'siapa',
        'bagaimana',
        'kapan',
        'dimana',
        'kenapa',
        'mengapa',
        'apakah',
        'dong',
        'sih',
        'ya',
        'kah',
        'lah',
        'kok',
        'gimana',
        'gini',
        'gitu',
        'kak',
        'min',
        'bang',
        'pak',
        'bu',
        'mas',
        'mbak',
        'bro',
        'gan',
        'tolong',
        'mohon',
        'bisa',
        'boleh',
        'mau',
        'ingin',
        'pengen',
        'coba',
        'saja',
        'aja',
        'juga',
        'sudah',
        'udah',
        'belum',
        'lagi',
        'versi',
        'nya',
        'tuh',
        'deh',
        'kami',
        'kita',
        'mereka',
    ];

    private const QUESTION_WORDS = [
        'berapa',
        'apa',
        'siapa',
        'kapan',
        'dimana',
        'mana',
        'jenis',
        'tipe',
        'type',
        'kategori',
        'merk',
        'merek',
        'brand',
        'terbanyak',
        'paling',
        'top',
        'tertinggi',
        'terbesar',
        'terendah',
        'tersedikit',
        'statistik',
        'summary',
        'rekap',
        'total',
        'jumlah',
        'nilai',
        'harga',
        'distribusi',
    ];

    private const FOLLOWUP_KEYWORDS = [
        'lanjut',
        'selanjutnya',
        'next',
        'sisanya',
        'berikutnya',
        'yang lainnya',
        'yang lain',
        'lainnya',
        'yang itu',
        'yang tadi',
        'yang td',
        'yang barusan',
        'detailnya',
        'hak milik',
        'milik',
        'sewa',
        'owned',
        'leased',
        'nya',
        'sn',
        'serial',
        'hostname',
        'kode aset',
        'asset code',
        'detail',
        'info',
        'lengkap',
        'jelaskan',
        'dia',
        'beliau',
        'orang itu',
        'itu',
        'tadi',
    ];

    private const CONTEXT_TTL = 30;

    public function index(Request $request)
    {
        $sessionId = $request->session()->getId();

        $history = Chat::where('session_id', $sessionId)
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        return view('chat.index', compact('history'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'pesan' => ['required', 'string', 'max:1000'],
        ]);

        $pesan = trim($request->input('pesan'));
        $sessionId = $request->session()->getId();

        $memory = new ChatMemoryService($sessionId);

        $followUp = $this->handleFollowUp($pesan, $request, $memory);

        if ($followUp) {
            [$jawaban, $sumber] = $followUp;
            $file = null;
        } else {
            [$jawaban, $sumber, $file] = $this->cariJawaban($pesan, $memory);
        }

        if ($sumber === 'ai' && strlen($jawaban) > 30) {
            $this->logPendingKnowledge($pesan, $jawaban);
        }

        $chat = Chat::create([
            'session_id' => $sessionId,
            'pesan' => $pesan,
            'jawaban' => $jawaban,
            'sumber' => $sumber,
            'waktu' => now(),
            'file_path' => $file['path'] ?? null,
            'file_name' => $file['name'] ?? null,
            'file_type' => $file['type'] ?? null,
            'file_size' => $file['size'] ?? null,
        ]);

        $fileResponse = null;
        if ($chat->file_path) {
            $fileResponse = [
                'url' => asset('storage/' . $chat->file_path),
                'name' => $chat->file_name,
                'type' => $chat->file_type,
                'size' => $chat->file_size,
                'category' => Knowledge::detectCategory($chat->file_type),
            ];
        }

        return response()->json([
            'status' => 'ok',
            'pesan' => $chat->pesan,
            'jawaban' => $chat->jawaban,
            'waktu' => $chat->waktu,
            'sumber' => $sumber,
            'file' => $fileResponse,
        ]);
    }

    private function handleFollowUp(string $pesan, Request $request, ChatMemoryService $memory): ?array
    {
        $lower = Str::lower(trim($pesan));
        $field = $this->detectFieldQuery($lower);
        if ($field) {
            $lastAsset = $memory->recall('asset');
            if ($lastAsset && isset($lastAsset['asset_id'])) {
                return $this->answerAssetField((int) $lastAsset['asset_id'], $field, $memory);
            }
        }
        $ref = $this->resolveReference($lower, $memory);
        if ($ref) {
            if (isset($ref['user_id']) && $this->matchAny($lower, ['aset', 'pegang', 'punya', 'pakai'])) {
                return $this->answerUserAssets((int) $ref['user_id'], $memory);
            }
            if (isset($ref['asset_id']) && $this->matchAny($lower, ['detail', 'info', 'lengkap'])) {
                return $this->answerAssetField((int) $ref['asset_id'], 'full', $memory);
            }
        }
        foreach (self::QUESTION_WORDS as $qw) {
            if (str_contains($lower, $qw)) {
                if ($field) {
                    break;
                }
                return null;
            }
        }
        $isFollowUp = false;
        foreach (self::FOLLOWUP_KEYWORDS as $kw) {
            if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $lower)) {
                $isFollowUp = true;
                break;
            }
        }

        if (!$isFollowUp) {
            return null;
        }
        $ctx = $memory->recall();

        if (!$ctx) {
            return null;
        }

        return $this->executeFollowUp($ctx, $request, $memory);
    }

    private function executeFollowUp(array $ctx, Request $request, ChatMemoryService $memory): ?array
    {
        $type = $ctx['_type'] ?? $ctx['type'] ?? null;
        $offset = $ctx['offset'] ?? 0;
        $limit = 10;

        return match ($type) {
            'asset_by_status',
            'list_asset_by_status' => $this->followUpAssetByStatus($ctx, $offset, $limit, $memory),
            'asset_by_ownership' => $this->followUpAssetByOwnership($ctx, $offset, $limit, $memory),
            'low_stock_consumable' => $this->followUpLowStockConsumable($ctx, $offset, $limit, $memory),
            'overdue_loans' => $this->followUpOverdueLoans($ctx, $offset, $limit, $memory),
            'active_loans' => $this->followUpActiveLoans($ctx, $offset, $limit, $memory),
            'active_assignments' => $this->followUpActiveAssignments($ctx, $offset, $limit, $memory),
            default => null,
        };
    }

    private function detectFieldQuery(string $lower): ?string
    {
        $normalized = preg_replace('/nya\b/i', '', $lower);
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));

        $fieldMap = [
            'serial_number' => ['sn', 'serial', 'nomor seri', 'serial number', 'no seri', 'noseri', 's/n', 'kode seri'],
            'hostname' => ['hostname', 'host name', 'nama komputer', 'nama pc', 'nama laptop', 'nama device', 'nama perangkat', 'pc name', 'nama host'],
            'asset_code' => ['asset code', 'kode aset', 'kode barang', 'kode inventaris', 'no aset', 'no asset', 'barcode', 'id aset', 'id asset', 'nomor aset', 'kd aset'],
            'brand' => ['brand', 'merek', 'merk', 'vendor barang', 'pembuat', 'pabrikan', 'manufaktur'],
            'model' => ['model', 'tipe', 'type', 'seri laptop', 'seri pc', 'varian'],
            'status' => ['status', 'kondisi', 'keadaan', 'posisi aset', 'aktif atau tidak', 'bisa dipakai', 'ready ga', 'ready gak'],
            'ownership' => ['hak kepemilikan', 'kepemilikan', 'milik', 'sewa', 'rental', 'status kepemilikan', 'punya siapa', 'hak milik'],
            'location' => ['lokasi', 'ruangan', 'ruang', 'tempat', 'posisi', 'ditaruh mana', 'ada di mana', 'disimpan di mana', 'site', 'cabang', 'lantai'],
            'user' => ['pemegang', 'pengguna', 'user', 'pic', 'penanggung jawab', 'siapa yang bawa', 'dipakai siapa', 'yang pegang', 'dipegang siapa', 'karyawan mana'],
            'spec' => ['spek', 'spesifikasi', 'spec', 'ram', 'prosesor', 'processor', 'ssd', 'harddisk', 'hdd', 'vga', 'jeroan', 'dapur pacu'],
            'os' => ['os', 'sistem operasi', 'windows', 'linux', 'mac', 'macos', 'ubuntu', 'operating system', 'win 10', 'win 11'],
            'garansi' => ['garansi', 'warranty', 'masa garansi', 'expired garansi', 'garansi sampai kapan', 'abis garansi'],
            'tanggal_beli' => ['tanggal beli', 'tanggal pembelian', 'kapan dibeli', 'tgl beli', 'tahun beli', 'kapan pengadaan', 'nota beli', 'tgl pengadaan'],
            'full' => ['detail lengkap', 'semua info', 'info lengkap', 'full detail', 'semuanya', 'tampilkan semua', 'all info', 'selengkapnya', 'profil aset'],
            'assigned_at' => ['sejak kapan', 'semenjak kapan', 'semenjak', 'dari kapan', 'kapan dipegang', 'mulai kapan', 'kapan di-assign', 'kapan diassign', 'kapan dipegang', 'sejak dipegang', 'dari dipegang', 'kapan mulai', 'sejak kapan dipegang', 'semenjak kapan dipegang', 'tgl serah terima', 'kapan dikasih ke', 'mulai pakai'],
            'returned_at' => ['kapan dikembalikan', 'kapan selesai', 'kapan return', 'kapan kembali', 'tgl pengembalian', 'kapan dibalikin', 'dibalikin kapan'],
            'riwayat' => ['riwayat lengkap', 'riwayat aset', 'history lengkap', 'history aset', 'rekam jejak', 'log aset', 'jurnal aset'],
            'semua_pemegang' => ['siapa saja yang pernah pegang', 'siapa aja yang pernah pegang', 'pernah dipegang siapa', 'daftar pemegang', 'semua pemegang', 'mantan pemegang', 'siapa aja usernya', 'list user', 'user terdahulu', 'siapa aja yang pernah pakai'],
            'durasi' => ['berapa lama dipegang', 'berapa lama dipakai', 'sudah berapa lama dipegang', 'lama dipegang', 'durasi pakai', 'lama pemakaian', 'berapa bulan dipakai', 'berapa tahun dipakai'],
            'loan_info' => ['kapan dipinjam', 'sedang dipinjam siapa', 'siapa yang minjam', 'siapa yang meminjam', 'dipinjam ke siapa', 'status pinjam', 'lagi dipinjam', 'peminjam'],
            'loan_history' => ['riwayat peminjaman', 'riwayat pinjam', 'history peminjaman', 'pernah dipinjam siapa', 'log pinjam', 'daftar peminjam'],
            'maintenance_last' => ['kapan terakhir diperbaiki', 'terakhir servis', 'terakhir maintenance', 'kapan terakhir rusak', 'terakhir diservis', 'kapan terakhir diperbaiki', 'kapan terakhir oprek'],
            'maintenance_count' => ['berapa kali rusak', 'berapa kali diperbaiki', 'berapa kali maintenance', 'jumlah perbaikan', 'frekuensi rusak', 'sering rusak ga', 'berapa kali masuk servis'],
            'maintenance_history' => ['riwayat perbaikan', 'riwayat maintenance', 'riwayat servis', 'history perbaikan', 'log servis', 'catatan perbaikan', 'pernah rusak apa aja'],
            'maintenance_cost' => ['biaya perbaikan', 'biaya servis', 'biaya maintenance', 'total biaya perbaikan', 'habis biaya berapa', 'biaya rusak', 'ongkos servis', 'pengeluaran maintenance'],
            'maintenance_tech' => ['siapa teknisi', 'teknisi yang perbaiki', 'siapa yang servis', 'siapa mekaniknya', 'vendor servisnya siapa', 'servis di mana'],
            'movement' => ['riwayat movement', 'riwayat perpindahan', 'pernah dipindah', 'riwayat pindah', 'mutasi aset', 'riwayat mutasi', 'pindah dari mana', 'log perpindahan'],
            'umur' => ['umur aset', 'umur laptop', 'berapa umur', 'usia aset', 'sudah berapa tahun', 'laptop tahun berapa', 'sudah tua belum'],
            'nilai_buku' => ['nilai buku', 'harga sekarang', 'nilai saat ini', 'nilai jual', 'penyusutan', 'depresiasi', 'sisa harga', 'harga pasaran', 'valuasi'],
            'bandingkan' => ['bandingkan dengan', 'bedanya dengan', 'perbandingan dengan', 'komparasi', 'bagusan mana', 'vs', 'mending mana'],
            'konsumable_stock' => ['stok konsumable', 'stok consumable', 'stok barang habis pakai', 'sisa stok', 'stok sisa berapa', 'masih ada berapa', 'ketersediaan barang', 'stok gudang'],
            'konsumable_last' => ['terakhir dipakai siapa', 'terakhir pakai', 'terakhir keluar', 'siapa yang terakhir ambil', 'terakhir diambil siapa', 'pengambilan terakhir'],
            'konsumable_history' => ['riwayat konsumable', 'riwayat consumable', 'riwayat transaksi konsumable', 'log konsumable', 'catatan keluar masuk barang', 'mutasi konsumable'],
            'vendor_assets' => ['aset dari vendor', 'vendor punya aset', 'aset vendor', 'vendor mana', 'beli di vendor mana', 'toko mana', 'distributor mana', 'supplier mana', 'dibeli dari'],
            'vendor_contact' => ['kontak vendor', 'telepon vendor', 'phone vendor', 'nomor vendor', 'no telp vendor', 'email vendor', 'hubungi vendor', 'cp vendor', 'contact person vendor'],
        ];


        foreach ($fieldMap as $field => $keywords) {
            foreach ($keywords as $kw) {
                if (
                    preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $lower) ||
                    preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $normalized)
                ) {
                    return $field;
                }
            }
        }

        return null;
    }

    private function answerAssetField(int $assetId, string $field, ChatMemoryService $memory): array
    {
        $asset = \App\Models\Asset::with(['category', 'currentUser', 'currentLocation'])
            ->find($assetId);

        if (!$asset) {
            return ["Aset tidak ditemukan.", 'database'];
        }
        $memory->remember('asset', [
            'asset_id' => $asset->id,
            'serial_number' => $asset->serial_number,
            'hostname' => $asset->hostname,
        ], 30, "Aset {$asset->serial_number}");

        if ($field === 'full') {
            return [$this->formatFullDetail($asset), 'database'];
        }

        $header = "🔍 **{$asset->serial_number}**";
        if ($asset->hostname) {
            $header .= " ({$asset->hostname})";
        }
        $header .= "\n\n";

        $jawaban = match ($field) {
            'serial_number' => $header . "**Serial Number:** `{$asset->serial_number}`",
            'hostname' => $header . "**Hostname:** " . ($asset->hostname ? "`{$asset->hostname}`" : '*tidak ada*'),
            'asset_code' => $header . "**Asset Code:** " . ($asset->asset_code ? "`{$asset->asset_code}`" : '*tidak ada*'),
            'brand' => $header . "**Brand:** {$asset->brand}",
            'model' => $header . "**Model:** {$asset->model}",
            'status' => $header . "**Status:** {$asset->status}",
            'ownership' => $header . "**Hak Kepemilikan:** " . ($asset->ownership_type === 'owned' ? '🟢 Hak Milik' : '🟠 Sewa'),
            'location' => $header . "**Lokasi:** " . ($asset->currentLocation?->full_name ?? '-'),
            'user' => $header . "**Pemegang:** " . ($asset->currentUser?->name ?? '-'),
            'spec' => $header . "**Spesifikasi:**\n" . $this->formatSpec($asset->specification),
            'os' => $header . "**OS:** " . ($asset->os ?? '-') . "\n**Lisensi:** " . ($asset->os_license ?? '-'),
            'garansi' => $header . "**Garansi:** " . ($asset->warranty_expire?->format('d M Y') ?? '-'),
            'tanggal_beli' => $header . "**Tanggal Pembelian:** " . ($asset->purchase_date?->format('d M Y') ?? '-'),
            'assigned_at' => $header . $this->formatAssignedInfo($asset),
            'returned_at' => $header . $this->formatReturnedInfo($asset),
            'riwayat' => $header . $this->formatFullHistory($asset),
            'semua_pemegang' => $header . $this->formatAllHolders($asset),
            'durasi' => $header . $this->formatHoldingDuration($asset),
            'loan_info' => $header . $this->formatLoanInfo($asset),
            'loan_history' => $header . $this->formatLoanHistory($asset),
            'maintenance_last' => $header . $this->formatLastMaintenance($asset),
            'maintenance_count' => $header . $this->formatMaintenanceCount($asset),
            'maintenance_history' => $header . $this->formatMaintenanceHistory($asset),
            'maintenance_cost' => $header . $this->formatMaintenanceCost($asset),
            'maintenance_tech' => $header . $this->formatMaintenanceTechnician($asset),
            'movement' => $header . $this->formatMovementHistory($asset),
            'umur' => $header . $this->formatAssetAge($asset),
            'nilai_buku' => $header . $this->formatBookValue($asset),

            default => $header . "Field **{$field}** tidak dikenali.",
        };

        return [$jawaban, 'database'];
    }

    private function formatSpec($spec): string
    {
        if (!$spec)
            return '-';
        if (is_string($spec))
            $spec = json_decode($spec, true);
        if (!is_array($spec) || empty($spec))
            return '-';

        $lines = [];
        foreach ($spec as $k => $v) {
            $lines[] = "  • " . ucfirst($k) . ": {$v}";
        }
        return implode("\n", $lines);
    }

    private function formatFullDetail($asset): string
    {
        $jawaban = "🔍 **Detail Lengkap: {$asset->serial_number}**";
        if ($asset->hostname) {
            $jawaban .= " ({$asset->hostname})";
        }
        $jawaban .= "\n\n";

        $jawaban .= "**📋 Identitas**\n"
            . "• Serial Number: `{$asset->serial_number}`\n";
        if ($asset->hostname) {
            $jawaban .= "• Hostname: `{$asset->hostname}`\n";
        }
        if ($asset->asset_code) {
            $jawaban .= "• Asset Code: `{$asset->asset_code}`\n";
        }

        $jawaban .= "\n**💻 Hardware**\n"
            . "• Brand: {$asset->brand}\n"
            . "• Model: {$asset->model}\n"
            . "• Kategori: " . ($asset->category?->name ?? '-') . "\n";

        if ($asset->specification) {
            $spec = is_string($asset->specification) ? json_decode($asset->specification, true) : $asset->specification;
            if (is_array($spec)) {
                foreach ($spec as $k => $v) {
                    $jawaban .= "• " . ucfirst($k) . ": {$v}\n";
                }
            }
        }

        $jawaban .= "\n**📊 Status**\n"
            . "• Status: {$asset->status}\n"
            . "• Hak Kepemilikan: " . ($asset->ownership_type === 'owned' ? '🟢 Hak Milik' : '🟠 Sewa') . "\n"
            . "• Pemegang: " . ($asset->currentUser?->name ?? '-') . "\n"
            . "• Lokasi: " . ($asset->currentLocation?->full_name ?? '-') . "\n";

        if ($asset->purchase_date) {
            $jawaban .= "\n**📅 Pembelian**\n"
                . "• Tanggal: {$asset->purchase_date->format('d M Y')}\n";
            if ($asset->purchase_price) {
                $jawaban .= "• Harga: Rp " . number_format($asset->purchase_price, 0, ',', '.') . "\n";
            }
            if ($asset->warranty_expire) {
                $jawaban .= "• Garansi: {$asset->warranty_expire->format('d M Y')}\n";
            }
        }
        $jawaban .= "\n" . $this->formatAssignedInfo($asset);

        return $jawaban;
    }

    private function formatAssignedInfo($asset): string
    {
        $assignment = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->with('user', 'assignedBy')
            ->latest('assigned_at')
            ->first();

        if (!$assignment) {
            return "**📅 Info Assignment**\nAset ini sedang **tidak dipegang siapa pun**.";
        }

        $userName = $assignment->user?->name ?? 'tidak diketahui';
        $assignedAt = $assignment->assigned_at?->format('d M Y') ?? '-';
        $daysAgo = $assignment->assigned_at?->diffForHumans() ?? '-';

        $jawaban = "**📅 Info Assignment**\n"
            . "• Dipinjamkan ke: **{$userName}**\n"
            . "• Sejak: **{$assignedAt}** ({$daysAgo})\n";

        if ($assignment->assignedBy) {
            $jawaban .= "• Di-assign oleh: {$assignment->assignedBy->name}\n";
        }

        if ($assignment->condition_on_assign) {
            $jawaban .= "• Kondisi saat assign: {$assignment->condition_on_assign}%\n";
        }

        if ($assignment->notes) {
            $jawaban .= "• Catatan: {$assignment->notes}\n";
        }

        return $jawaban;
    }

    private function formatReturnedInfo($asset): string
    {
        $assignment = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->whereNotNull('returned_at')
            ->with('user')
            ->latest('returned_at')
            ->first();

        if (!$assignment) {
            return "**📅 Info Pengembalian**\nAset ini **belum pernah dikembalikan**.";
        }

        $userName = $assignment->user?->name ?? 'tidak diketahui';
        $returnedAt = $assignment->returned_at?->format('d M Y') ?? '-';
        $daysAgo = $assignment->returned_at?->diffForHumans() ?? '-';

        $jawaban = "**📅 Info Pengembalian**\n"
            . "• Dikembalikan oleh: **{$userName}**\n"
            . "• Pada: **{$returnedAt}** ({$daysAgo})\n";

        if ($assignment->condition_on_return) {
            $jawaban .= "• Kondisi saat kembali: {$assignment->condition_on_return}%\n";
        }

        return $jawaban;
    }

    private function formatFullHistory($asset): string
    {
        $jawaban = "**📜 Riwayat Lengkap Aset**\n\n";
        $assignments = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->with('user')
            ->orderByDesc('assigned_at')
            ->get();

        if ($assignments->isNotEmpty()) {
            $jawaban .= "**👤 Serah Terima ({$assignments->count()})**\n";
            foreach ($assignments->take(5) as $a) {
                $status = $a->returned_at ? '✅ Kembali' : '🔵 Aktif';
                $jawaban .= "• {$a->user?->name} — {$a->assigned_at?->format('d M Y')} ({$status})\n";
            }
            if ($assignments->count() > 5) {
                $jawaban .= "• ... dan " . ($assignments->count() - 5) . " lainnya\n";
            }
            $jawaban .= "\n";
        }
        $loans = \App\Models\AssetLoan::where('asset_id', $asset->id)
            ->with('user')
            ->orderByDesc('loan_date')
            ->get();

        if ($loans->isNotEmpty()) {
            $jawaban .= "**📤 Peminjaman ({$loans->count()})**\n";
            foreach ($loans->take(3) as $l) {
                $jawaban .= "• {$l->user?->name} — {$l->loan_date?->format('d M Y')} ({$l->status})\n";
            }
            if ($loans->count() > 3) {
                $jawaban .= "• ... dan " . ($loans->count() - 3) . " lainnya\n";
            }
            $jawaban .= "\n";
        }
        $maintenances = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->orderByDesc('start_date')
            ->get();

        if ($maintenances->isNotEmpty()) {
            $jawaban .= "**🔧 Perbaikan ({$maintenances->count()})**\n";
            foreach ($maintenances->take(3) as $m) {
                $jawaban .= "• {$m->type} — {$m->start_date?->format('d M Y')} ({$m->status})\n";
            }
            if ($maintenances->count() > 3) {
                $jawaban .= "• ... dan " . ($maintenances->count() - 3) . " lainnya\n";
            }
            $jawaban .= "\n";
        }

        if ($assignments->isEmpty() && $loans->isEmpty() && $maintenances->isEmpty()) {
            $jawaban .= "_Belum ada riwayat untuk aset ini._";
        }

        return $jawaban;
    }

    private function formatAllHolders($asset): string
    {
        $assignments = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->with('user')
            ->orderByDesc('assigned_at')
            ->get();

        if ($assignments->isEmpty()) {
            return "Aset ini **belum pernah di-assign** ke user mana pun.";
        }

        $jawaban = "**👥 Semua Pemegang Aset** ({$assignments->count()}x):\n\n";
        foreach ($assignments as $i => $a) {
            $status = $a->returned_at
                ? "✅ Kembali: {$a->returned_at->format('d M Y')}"
                : "🔵 **Sedang dipegang**";
            $jawaban .= ($i + 1) . ". **{$a->user?->name}**\n"
                . "   • Assign: {$a->assigned_at?->format('d M Y')}\n"
                . "   • {$status}\n";

            if ($a->returned_at && $a->assigned_at) {
                $duration = $a->assigned_at->diffInDays($a->returned_at);
                $jawaban .= "   • Durasi: {$duration} hari\n";
            }
            $jawaban .= "\n";
        }

        return trim($jawaban);
    }

    private function formatHoldingDuration($asset): string
    {
        $assignment = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->with('user')
            ->latest('assigned_at')
            ->first();

        if (!$assignment) {
            return "Aset ini sedang **tidak dipegang siapa pun**.";
        }

        $days = $assignment->assigned_at?->diffInDays(now()) ?? 0;
        $userName = $assignment->user?->name ?? '-';
        $assignedAt = $assignment->assigned_at?->format('d M Y') ?? '-';

        $jawaban = "**⏱️ Durasi Pemakaian**\n"
            . "• Dipegang oleh: **{$userName}**\n"
            . "• Sejak: {$assignedAt}\n"
            . "• Sudah: **{$days} hari**";

        if ($days > 365) {
            $years = floor($days / 365);
            $months = floor(($days % 365) / 30);
            $jawaban .= " ({$years} tahun {$months} bulan)";
        } elseif ($days > 30) {
            $months = floor($days / 30);
            $jawaban .= " ({$months} bulan)";
        }

        return $jawaban;
    }

    private function formatLoanInfo($asset): string
    {
        $loan = \App\Models\AssetLoan::where('asset_id', $asset->id)
            ->whereIn('status', ['borrowed', 'approved', 'overdue'])
            ->with('user')
            ->latest('loan_date')
            ->first();

        if (!$loan) {
            return "Aset ini **sedang tidak dipinjam** siapa pun.";
        }

        $userName = $loan->user?->name ?? '-';
        $loanDate = $loan->loan_date?->format('d M Y') ?? '-';
        $dueDate = $loan->due_date?->format('d M Y') ?? '-';
        $daysLeft = $loan->due_date?->diffInDays(now()) ?? 0;
        $isOverdue = $loan->due_date && $loan->due_date->isPast();

        $jawaban = "**📤 Info Peminjaman**\n"
            . "• Dipinjam oleh: **{$userName}**\n"
            . "• Tanggal pinjam: {$loanDate}\n"
            . "• Jatuh tempo: **{$dueDate}**\n";

        if ($isOverdue) {
            $jawaban .= "• ⚠️ **Terlambat {$daysLeft} hari!**\n";
        } else {
            $jawaban .= "• Sisa waktu: **{$daysLeft} hari**\n";
        }

        if ($loan->purpose) {
            $jawaban .= "• Keperluan: {$loan->purpose}\n";
        }

        return $jawaban;
    }

    private function formatLoanHistory($asset): string
    {
        $loans = \App\Models\AssetLoan::where('asset_id', $asset->id)
            ->with('user')
            ->orderByDesc('loan_date')
            ->get();

        if ($loans->isEmpty()) {
            return "Aset ini **belum pernah dipinjam**.";
        }

        $jawaban = "**📤 Riwayat Peminjaman** ({$loans->count()}x):\n\n";
        foreach ($loans as $i => $l) {
            $status = match ($l->status) {
                'returned' => '✅ Kembali',
                'borrowed' => '🔵 Dipinjam',
                'overdue' => '🔴 Terlambat',
                'approved' => '🟡 Disetujui',
                default => $l->status,
            };

            $jawaban .= ($i + 1) . ". **{$l->user?->name}**\n"
                . "   • Pinjam: {$l->loan_date?->format('d M Y')}\n";

            if ($l->returned_at) {
                $jawaban .= "   • Kembali: {$l->returned_at->format('d M Y')}\n";
            } else {
                $jawaban .= "   • Jatuh tempo: {$l->due_date?->format('d M Y')}\n";
            }

            $jawaban .= "   • Status: {$status}\n\n";
        }

        return trim($jawaban);
    }

    private function formatLastMaintenance($asset): string
    {
        $m = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->latest('start_date')
            ->first();

        if (!$m) {
            return "Aset ini **belum pernah diperbaiki**.";
        }

        $jawaban = "**🔧 Perbaikan Terakhir**\n"
            . "• Tanggal: **{$m->start_date?->format('d M Y')}**\n"
            . "• Jenis: {$m->type}\n"
            . "• Masalah: {$m->issue}\n"
            . "• Status: {$m->status}\n";

        if ($m->technician) {
            $jawaban .= "• Teknisi: {$m->technician}\n";
        }
        if ($m->cost) {
            $jawaban .= "• Biaya: Rp " . number_format($m->cost, 0, ',', '.') . "\n";
        }

        return $jawaban;
    }

    private function formatMaintenanceCount($asset): string
    {
        $total = \App\Models\AssetMaintenance::where('asset_id', $asset->id)->count();
        $done = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->where('status', 'done')->count();
        $ongoing = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->whereIn('status', ['open', 'in_progress'])->count();

        $byType = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $jawaban = "**🔧 Jumlah Perbaikan**\n"
            . "• Total: **{$total}x**\n"
            . "• Selesai: {$done}x\n"
            . "• Sedang berjalan: {$ongoing}x\n";

        if ($byType->isNotEmpty()) {
            $jawaban .= "\n**Per Jenis:**\n";
            foreach ($byType as $type => $count) {
                $jawaban .= "• {$type}: {$count}x\n";
            }
        }

        return $jawaban;
    }

    private function formatMaintenanceHistory($asset): string
    {
        $maintenances = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->with('vendor')
            ->orderByDesc('start_date')
            ->get();

        if ($maintenances->isEmpty()) {
            return "Aset ini **belum pernah diperbaiki**.";
        }

        $jawaban = "**🔧 Riwayat Perbaikan** ({$maintenances->count()}x):\n\n";
        foreach ($maintenances as $i => $m) {
            $jawaban .= ($i + 1) . ". **{$m->issue}**\n"
                . "   • Tanggal: {$m->start_date?->format('d M Y')}\n"
                . "   • Jenis: {$m->type}\n"
                . "   • Status: {$m->status}\n";

            if ($m->action) {
                $jawaban .= "   • Tindakan: {$m->action}\n";
            }
            if ($m->technician) {
                $jawaban .= "   • Teknisi: {$m->technician}\n";
            }
            if ($m->cost) {
                $jawaban .= "   • Biaya: Rp " . number_format($m->cost, 0, ',', '.') . "\n";
            }
            if ($m->vendor) {
                $jawaban .= "   • Vendor: {$m->vendor->name}\n";
            }
            $jawaban .= "\n";
        }

        return trim($jawaban);
    }

    private function formatMaintenanceCost($asset): string
    {
        $total = \App\Models\AssetMaintenance::where('asset_id', $asset->id)->sum('cost') ?? 0;
        $count = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->whereNotNull('cost')->where('cost', '>', 0)->count();

        $jawaban = "**💰 Biaya Perbaikan**\n"
            . "• Total biaya: **Rp " . number_format($total, 0, ',', '.') . "**\n"
            . "• Jumlah perbaikan berbiaya: {$count}x\n";

        if ($count > 0) {
            $avg = $total / $count;
            $jawaban .= "• Rata-rata: Rp " . number_format($avg, 0, ',', '.') . "\n";
        }

        return $jawaban;
    }

    private function formatMaintenanceTechnician($asset): string
    {
        $techs = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->whereNotNull('technician')
            ->select('technician', \DB::raw('COUNT(*) as total'))
            ->groupBy('technician')
            ->orderByDesc('total')
            ->get();

        if ($techs->isEmpty()) {
            return "Belum ada data teknisi untuk aset ini.";
        }

        $jawaban = "**👨‍🔧 Teknisi yang Pernah Perbaiki:**\n";
        foreach ($techs as $i => $t) {
            $jawaban .= ($i + 1) . ". **{$t->technician}** — {$t->total}x\n";
        }

        return trim($jawaban);
    }

    private function formatMovementHistory($asset): string
    {
        $movements = \App\Models\AssetMovement::where('asset_id', $asset->id)
            ->with(['fromLocation', 'toLocation', 'movable'])
            ->orderByDesc('moved_at')
            ->get();

        if ($movements->isEmpty()) {
            return "Aset ini **belum pernah dipindahkan**.";
        }

        $jawaban = "**🚚 Riwayat Perpindahan** ({$movements->count()}x):\n\n";
        foreach ($movements as $i => $m) {
            $from = $m->fromLocation?->full_name ?? '-';
            $to = $m->toLocation?->full_name ?? '-';

            $jawaban .= ($i + 1) . ". **{$m->type}**\n"
                . "   • Tanggal: {$m->moved_at?->format('d M Y')}\n";

            if ($from !== '-' || $to !== '-') {
                $jawaban .= "   • Dari: {$from}\n"
                    . "   • Ke: {$to}\n";
            }

            if ($m->notes) {
                $jawaban .= "   • Catatan: {$m->notes}\n";
            }
            $jawaban .= "\n";
        }

        return trim($jawaban);
    }

    private function formatAssetAge($asset): string
    {
        if (!$asset->purchase_date) {
            return "Tanggal pembelian aset ini **tidak tercatat**.";
        }

        $days = $asset->purchase_date->diffInDays(now());
        $years = floor($days / 365);
        $months = floor(($days % 365) / 30);

        $jawaban = "**🎂 Umur Aset**\n"
            . "• Tanggal beli: {$asset->purchase_date->format('d M Y')}\n"
            . "• Umur: **{$years} tahun {$months} bulan** ({$days} hari)\n";

        if ($asset->warranty_expire) {
            $sisaGaransi = $asset->warranty_expire->diffInDays(now(), false);
            if ($sisaGaransi > 0) {
                $jawaban .= "• Garansi sisa: **{$sisaGaransi} hari**\n";
            } else {
                $jawaban .= "• Garansi: **sudah habis** (" . abs($sisaGaransi) . " hari lalu)\n";
            }
        }

        return $jawaban;
    }

    private function formatBookValue($asset): string
    {
        if (!$asset->purchase_price || !$asset->purchase_date) {
            return "Data harga atau tanggal pembelian aset ini **tidak lengkap**.";
        }

        $price = $asset->purchase_price;
        $years = $asset->purchase_date->diffInYears(now());
        $depresiasi = min(0.8 * $years, 0.9); // max 90% depresiasi
        $currentValue = $price * (1 - $depresiasi);

        $jawaban = "**💵 Nilai Buku Aset**\n"
            . "• Harga beli: Rp " . number_format($price, 0, ',', '.') . "\n"
            . "• Umur: {$years} tahun\n"
            . "• Depresiasi: " . round($depresiasi * 100) . "%\n"
            . "• Nilai sekarang: **Rp " . number_format($currentValue, 0, ',', '.') . "**\n\n"
            . "_Estimasi berdasarkan depresiasi 20%/tahun._";

        return $jawaban;
    }

    private function resolveReference(string $lower, ChatMemoryService $memory): ?array
    {
        if (preg_match('/\b(dia|beliau|orang itu)\b/', $lower)) {
            return $memory->recall('user');
        }

        if (preg_match('/\b(yang tadi|yang td|yang barusan|yang itu)\b/', $lower)) {
            return $memory->recall();
        }

        return null;
    }

    private function answerUserAssets(int $userId, ChatMemoryService $memory): array
    {
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return ["User tidak ditemukan.", 'database'];
        }

        $assets = $user->currentAssets()->with('category')->get();

        if ($assets->isEmpty()) {
            return ["User **{$user->name}** sedang tidak memegang aset.", 'database'];
        }

        $memory->remember('user', [
            'user_id' => $user->id,
            'name' => $user->name,
            'position' => $user->position,
        ], 30, "User {$user->name}");

        if ($assets->count() === 1) {
            $a = $assets->first();
            $memory->remember('asset', [
                'asset_id' => $a->id,
                'serial_number' => $a->serial_number,
                'hostname' => $a->hostname,
            ], 30, "Aset {$a->serial_number}");
        }

        $jawaban = "👤 **{$user->name}** memegang **{$assets->count()}** aset:\n\n";
        foreach ($assets as $a) {
            $jawaban .= "• **{$a->serial_number}**";
            if ($a->hostname) {
                $jawaban .= " ({$a->hostname})";
            }
            $jawaban .= "\n  {$a->brand} {$a->model}\n";
            if ($a->category) {
                $jawaban .= "  Kategori: {$a->category->name}\n";
            }
            $jawaban .= "\n";
        }

        if ($assets->count() > 1) {
            $jawaban .= "_Ketik \"detail yang pertama\" untuk info lengkap._";
        }

        return [trim($jawaban), 'database'];
    }

    private function followUpAssetByStatus(array $ctx, int $offset, int $limit, ChatMemoryService $memory): array
    {
        $status = $ctx['status'] ?? null;
        $category = $ctx['category'] ?? null;
        $brand = $ctx['brand'] ?? null;

        $q = \App\Models\Asset::with('category');

        if ($status)
            $q->where('status', $status);
        if ($category) {
            $q->where(function ($x) use ($category) {
                $x->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                    ->orWhere('model', 'like', "%{$category}%");
            });
        }
        if ($brand)
            $q->where('brand', 'like', "%{$brand}%");

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $memory->forget('asset_by_status');
            return ["Tidak ada data lagi. Total: **{$total} aset**.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan **{$start}-{$end}** dari **{$total}** aset:\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $memory->remember('asset_by_status', $ctx, 30);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: **{$sisa}** aset. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $memory->forget('asset_by_status');
        }

        return [$jawaban, 'database'];
    }

    private function followUpAssetByOwnership(array $ctx, int $offset, int $limit, ChatMemoryService $memory): array
    {
        $ownership = $ctx['ownership'] ?? null;
        $category = $ctx['category'] ?? null;
        $brand = $ctx['brand'] ?? null;

        $q = \App\Models\Asset::with('category');

        if ($ownership)
            $q->where('ownership_type', $ownership);
        if ($category) {
            $q->where(function ($x) use ($category) {
                $x->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                    ->orWhere('model', 'like', "%{$category}%");
            });
        }
        if ($brand)
            $q->where('brand', 'like', "%{$brand}%");

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $memory->forget('asset_by_ownership');
            return ["Tidak ada data lagi. Total: **{$total} aset**.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan **{$start}-{$end}** dari **{$total}** aset:\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $memory->remember('asset_by_ownership', $ctx, 30);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: **{$sisa}** aset. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $memory->forget('asset_by_ownership');
        }

        return [$jawaban, 'database'];
    }

    private function followUpLowStockConsumable(array $ctx, int $offset, int $limit, ChatMemoryService $memory): array
    {
        $q = \App\Models\Consumable::whereColumn('stock_available', '<=', 'stock_minimum')
            ->orderBy('stock_available');

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $memory->forget('low_stock_consumable');
            return ["Tidak ada data lagi. Total: **{$total} konsumable**.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan **{$start}-{$end}** dari **{$total} konsumable** stok rendah:\n\n";
        foreach ($items as $c) {
            $jawaban .= "• {$c->name}: {$c->stock_available}/{$c->stock_minimum} {$c->unit}\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $memory->remember('low_stock_consumable', $ctx, 30);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: **{$sisa}**. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $memory->forget('low_stock_consumable');
        }

        return [$jawaban, 'database'];
    }

    private function followUpOverdueLoans(array $ctx, int $offset, int $limit, ChatMemoryService $memory): array
    {
        $q = \App\Models\AssetLoan::where('status', 'borrowed')
            ->whereDate('due_date', '<', today())
            ->with(['asset', 'user'])
            ->orderBy('due_date');

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $memory->forget('overdue_loans');
            return ["Tidak ada data lagi. Total: **{$total} peminjaman terlambat**.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan **{$start}-{$end}** dari **{$total}** peminjaman terlambat:\n\n";
        foreach ($items as $l) {
            $jawaban .= "• {$l->asset?->serial_number} — {$l->user?->name}";
            $jawaban .= " (jatuh tempo {$l->due_date?->diffForHumans()})\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $memory->remember('overdue_loans', $ctx, 30);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: **{$sisa}**. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $memory->forget('overdue_loans');
        }

        return [$jawaban, 'database'];
    }

    private function followUpActiveLoans(array $ctx, int $offset, int $limit, ChatMemoryService $memory): array
    {
        $q = \App\Models\AssetLoan::where('status', 'borrowed')
            ->with(['asset', 'user'])
            ->orderByDesc('loan_date');

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $memory->forget('active_loans');
            return ["Tidak ada data lagi. Total: **{$total} peminjaman aktif**.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan **{$start}-{$end}** dari **{$total}** peminjaman aktif:\n\n";
        foreach ($items as $l) {
            $jawaban .= "• {$l->asset?->serial_number} — {$l->user?->name}";
            if ($l->due_date)
                $jawaban .= " (jatuh tempo {$l->due_date->format('d M Y')})";
            $jawaban .= "\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $memory->remember('active_loans', $ctx, 30);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: **{$sisa}**. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $memory->forget('active_loans');
        }

        return [$jawaban, 'database'];
    }

    private function followUpActiveAssignments(array $ctx, int $offset, int $limit, ChatMemoryService $memory): array
    {
        $q = \App\Models\AssetAssignment::whereNull('returned_at')
            ->with(['asset', 'user'])
            ->orderByDesc('assigned_at');

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $memory->forget('active_assignments');
            return ["Tidak ada data lagi. Total: **{$total} serah terima aktif**.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan **{$start}-{$end}** dari **{$total}** serah terima aktif:\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->asset?->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
            $jawaban .= " — {$a->user?->name}\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $memory->remember('active_assignments', $ctx, 30);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: **{$sisa}**. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $memory->forget('active_assignments');
        }

        return [$jawaban, 'database'];
    }

    private function logPendingKnowledge(string $pesan, string $jawaban): void
    {
        $pesanNorm = Str::lower(trim($pesan));

        $existing = PendingKnowledge::whereRaw('LOWER(pesan_user) = ?', [$pesanNorm])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            $existing->increment('frequency');
            $existing->jawaban_ai = $jawaban;

            if ($existing->frequency >= 5 && strlen($jawaban) > 50) {
                $existsInKnowledge = Knowledge::whereRaw('LOWER(kata_kunci) = ?', [$pesanNorm])->exists();

                if (!$existsInKnowledge) {
                    Knowledge::create([
                        'kata_kunci' => Str::limit($pesan, 255),
                        'jawaban' => $jawaban,
                    ]);

                    $existing->status = 'approved';

                    Log::info('Auto-approved pending knowledge', [
                        'pesan' => $pesan,
                        'frequency' => $existing->frequency,
                    ]);
                }
            }

            $existing->save();
        } else {
            PendingKnowledge::create([
                'pesan_user' => $pesan,
                'jawaban_ai' => $jawaban,
                'frequency' => 1,
                'status' => 'pending',
            ]);
        }
    }

    private function cariJawaban(string $pesan, ChatMemoryService $memory): array
    {
        $dbAnswer = app(\App\Services\Query\QueryRouter::class)->tryAnswer($pesan);
        if ($dbAnswer) {
            if (isset($dbAnswer[2]) && is_array($dbAnswer[2])) {
                session()->put('last_query_context', $dbAnswer[2]);
                $type = $dbAnswer[2]['type'] ?? 'query';
                $memory->remember($type, $dbAnswer[2], 30);
            }
            return [$dbAnswer[0], 'database', null];
        }
        if (preg_match('/\b([A-Z]{2,}[-_][A-Z0-9]{2,}(?:[-_][A-Z0-9]+)*)\b/i', $pesan, $m)) {
            $identifier = strtoupper($m[1]);

            $blacklist = ['nya', 'ini', 'itu', 'apa', 'siapa', 'mana', 'berapa', 'yang'];
            if (!in_array(strtolower($identifier), $blacklist, true)) {
                $asset = \App\Models\Asset::with(['category', 'currentUser', 'currentLocation'])
                    ->where('serial_number', 'like', "%{$identifier}%")
                    ->orWhere('hostname', 'like', "%{$identifier}%")
                    ->orWhere('asset_code', 'like', "%{$identifier}%")
                    ->first();

                if ($asset) {
                    $memory->remember('asset', [
                        'asset_id' => $asset->id,
                        'serial_number' => $asset->serial_number,
                        'hostname' => $asset->hostname,
                    ], 30, "Aset {$asset->serial_number}");

                    $jawaban = "🔍 **{$asset->serial_number}**";
                    if ($asset->hostname) {
                        $jawaban .= " ({$asset->hostname})";
                    }
                    $jawaban .= "\n\n"
                        . "• Brand/Model: {$asset->brand} {$asset->model}\n"
                        . "• Kategori: " . ($asset->category?->name ?? '-') . "\n"
                        . "• Status: {$asset->status}\n"
                        . "• Hak Kepemilikan: " . ($asset->ownership_type === 'owned' ? '🟢 Hak Milik' : '🟠 Sewa') . "\n";

                    if ($asset->currentUser) {
                        $jawaban .= "\n👤 **Pemegang saat ini:** {$asset->currentUser->name}";
                        if ($asset->currentUser->position) {
                            $jawaban .= " ({$asset->currentUser->position})";
                        }
                    } else {
                        $jawaban .= "\n👤 **Pemegang saat ini:** *tidak ada* (aset tersedia)";
                    }

                    if ($asset->currentLocation) {
                        $jawaban .= "\n📍 **Lokasi:** {$asset->currentLocation->full_name}";
                    }

                    return [$jawaban, 'database', null];
                }
            }
        }
        $learning = app(AutoLearningService::class);
        $learned = $learning->findAnswer($pesan);

        if ($learned) {
            $learned->increment('frequency');
            return [$learned->answer, 'learned', null];
        }
        $pesanBersih = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', Str::lower($pesan));
        $pesanBersih = preg_replace('/\s+/', ' ', trim($pesanBersih));

        if (!empty($pesanBersih)) {
            $knowledge = Knowledge::whereRaw('LOWER(kata_kunci) = ?', [$pesanBersih])->first();

            if ($knowledge) {
                return [
                    $knowledge->jawaban,
                    'database',
                    $this->extractFile($knowledge),
                ];
            }

            $candidates = Knowledge::search($pesanBersih)->take(20)->get();
            $best = $this->pickBestMatch($candidates, $pesanBersih);

            if ($best) {
                return [
                    $best->jawaban,
                    'database',
                    $this->extractFile($best),
                ];
            }
        }
        $jawaban = $this->tanyaOllama($pesan);

        if (strlen($jawaban) >= 50 && !str_contains($jawaban, 'Maaf,')) {
            $learning->process($pesan, $jawaban, 'ai');
        }

        return [$jawaban, 'ai', null];
    }

    private function pickBestMatch($candidates, string $pesanBersih): ?Knowledge
    {
        if ($candidates->isEmpty())
            return null;

        $pesanWords = collect(explode(' ', $pesanBersih))
            ->filter(fn($w) => strlen($w) >= 3 && !in_array($w, self::STOPWORDS))
            ->values()
            ->all();

        if (empty($pesanWords))
            return null;

        $best = null;
        $bestScore = 0;

        foreach ($candidates as $item) {
            $keyLower = Str::lower($item->kata_kunci);
            $keyWords = collect(explode(' ', $keyLower))
                ->filter(fn($w) => strlen($w) >= 3 && !in_array($w, self::STOPWORDS))
                ->values()
                ->all();

            if (empty($keyWords))
                continue;

            $matched = 0;
            foreach ($pesanWords as $pw) {
                foreach ($keyWords as $kw) {
                    if (
                        $pw === $kw ||
                        levenshtein($pw, $kw) <= 1 ||
                        str_contains($kw, $pw) ||
                        str_contains($pw, $kw)
                    ) {
                        $matched++;
                        break;
                    }
                }
            }

            $score = $matched / count($pesanWords);

            $lengthRatio = min(count($keyWords), count($pesanWords)) /
                max(count($keyWords), count($pesanWords));
            $score = $score * (0.7 + 0.3 * $lengthRatio);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $item;
            }
        }

        return $bestScore >= 0.6 ? $best : null;
    }

    private function extractFile(Knowledge $knowledge): ?array
    {
        if (!$knowledge->file_path)
            return null;

        return [
            'path' => $knowledge->file_path,
            'name' => $knowledge->file_name,
            'type' => $knowledge->file_type,
            'size' => $knowledge->file_size,
        ];
    }

    private function matchAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($haystack, $n))
                return true;
        }
        return false;
    }

    private function tanyaOllama(string $pesan): string
    {
        $url = rtrim(config('services.ollama.url', 'http://127.0.0.1:11434'), '/');
        $model = trim(config('services.ollama.model', ''));
        $endpoint = $url . '/api/chat';

        Log::info('=== OLLAMA REQUEST ===', [
            'url' => $endpoint,
            'model' => $model,
            'pesan' => $pesan,
        ]);

        if (empty($model)) {
            Log::error('Ollama: model kosong. Cek .env OLLAMA_MODEL.');
            return 'Maaf, konfigurasi AI belum lengkap. Hubungi admin.';
        }

        try {
            $response = Http::timeout(config('services.ollama.timeout', 60))->post($endpoint, [
                'model' => $model,
                'stream' => false,
                'options' => [
                    'temperature' => 0.5,
                    'num_predict' => 256,
                ],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "Kamu adalah **SIS Assistant** — asisten virtual untuk karyawan **PLN UBP Suralaya**.\n\n"
                            . "**KONTEKS SISTEM:**\n"
                            . "• **SIS (Suralaya Information System)** adalah portal informasi aplikasi & layanan IT perusahaan.\n"
                            . "• **SIAM (Sistem Informasi Aset Manajemen)** adalah salah satu **modul di dalam SIS** untuk mengelola aset IT (laptop, PC, printer, monitor, konsumable).\n"
                            . "• SIS dan SIAM adalah **aplikasi web**, bukan aplikasi desktop. Tidak ada 'keyboard virtual' atau 'keyboard khusus' di dalamnya — user pakai keyboard perangkat sendiri.\n\n"
                            . "**KEAHLIAN KAMU:**\n"
                            . "1. **SIS & SIAM** — portal aplikasi, manajemen aset IT (hak milik/sewa, status, serah terima, BAST, peminjaman, perbaikan, vendor, garansi, kontrak sewa).\n"
                            . "2. **IT Umum** — hardware, software, jaringan, keamanan, database, Microsoft Office, email, printer, cloud, troubleshooting dasar.\n"
                            . "3. **Aplikasi internal** — helpdesk, ERP, Maximo, IAM, dll.\n\n"
                            . "**ATURAN JAWAB:**\n"
                            . "• SELALU jawab dalam Bahasa Indonesia.\n"
                            . "• Jawab SINGKAT — maksimal 3-4 kalimat, LANGSUNG ke inti.\n"
                            . "• JANGAN mulai dengan sapaan 'Halo' kecuali user menyapa duluan.\n"
                            . "• Kalau tidak tahu, katakan: *\"Maaf, saya belum punya info tentang itu. Coba tanya dengan cara lain atau hubungi IT Support.\"*\n"
                            . "• **JANGAN MENGARANG** fakta yang tidak kamu ketahui. Jangan pernah menganggap SIS/SIAM sebagai sistem lain.\n\n"
                            . "**KHUSUS — KODE ASET:**\n"
                            . "Kode seperti `NB-T14-005`, `AST-2026-0001`, `T14-SN-0005`, `PC-DESK-003` adalah **HOSTNAME / SN / ASSET CODE** dari aset IT perusahaan.\n"
                            . "JANGAN menganggapnya sebagai nomor meteran listrik, nomor rekening, atau nomor seri barang lain.\n"
                            . "Kalau tidak tahu detail asetnya, arahkan user ke menu **Aset di SIAM**.\n\n"
                            . "**GAYA:**\n"
                            . "Ramah, profesional, solutif. Gunakan emoji secukupnya. Jangan sebut dirimu AI atau language model.",
                    ],
                    [
                        'role' => 'user',
                        'content' => $pesan,
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $jawaban = trim($data['message']['content'] ?? '');

                if ($jawaban !== '') {
                    return $jawaban;
                }

                return 'Maaf, saya belum bisa menjawab pertanyaan itu.';
            }

            return 'Maaf, saya belum bisa menjawab pertanyaan itu.';

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Ollama connection error: ' . $e->getMessage());
            return 'Maaf, layanan AI tidak dapat dihubungi. Cek apakah Ollama berjalan.';
        } catch (\Exception $e) {
            Log::error('Ollama exception: ' . $e->getMessage());
            return 'Maaf, layanan AI sedang tidak tersedia. Coba lagi nanti.';
        }
    }
}
