<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SqlTextService
{
    private string $url;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->url = rtrim(config('services.ollama.url', 'http://127.0.0.1:11434'), '/');
        $this->model = config('services.ollama.sql_model', config('services.ollama.model', 'qwen2.5-coder:7b'));
        $this->timeout = (int) config('services.ollama.sql_timeout', 30);
    }

    // ============================================================
    // PUBLIC API
    // ============================================================

    /**
     * Entry point. Return [answer, 'database', context] atau null kalau gagal.
     *
     * @param string $pesan Pesan user
     * @param string|null $previousSql SQL sebelumnya (untuk konteks follow-up)
     */
    public function tryAnswer(string $pesan, ?string $previousSql = null, array $kbContext = []): ?array
    {
        $normalizedMessage = $this->normalizeMessage($pesan);

        Log::info('SqlTextService.tryAnswer', [
            'original' => $pesan,
            'normalized' => $normalizedMessage,
            'has_prev_sql' => $previousSql !== null,
            'kb_context_count' => count($kbContext),
        ]);

        $sql = $this->generateSql($normalizedMessage, $previousSql, $kbContext);
        if (!$sql) {
            Log::info('SqlTextService.generateSql_failed');
            return null;
        }

        $rows = $this->executeSql($sql);
        if ($rows === null) {
            Log::info('SqlTextService.executeSql_failed', ['sql' => $sql]);
            return null;
        }

        $answer = $this->formatAnswer($normalizedMessage, $sql, $rows);

        return [
            $answer,
            'database',
            [
                'type' => 'sql_text',
                'sql' => $sql,
                'row_count' => count($rows),
                'kb_context_count' => count($kbContext),
                'time' => now()->toDateTimeString(),
            ],
        ];
    }

    // ============================================================
    // NORMALISASI PESAN
    // ============================================================

    /**
     * Buang kata-kata tidak penting yang bikin LLM bingung.
     * Contoh: "laptop tahun 2024 type apa ya" → "laptop tahun 2024 type apa"
     */
    private function normalizeMessage(string $pesan): string
    {
        $pesan = trim($pesan);

        // Hapus kata partikel di akhir: ya, dong, sih, deh, kok, kah, lah, tuh, nih, gan
        $pesan = preg_replace(
            '/\s+(ya|dong|sih|deh|kok|kah|lah|tuh|nih|gan|bro|kak|min|pak|bu|mas|mbak)\s*$/i',
            '',
            $pesan
        );

        // Hapus tanda baca berlebih di akhir
        $pesan = preg_replace('/[?!.,;:]+$/', '', $pesan);

        // Collapse multiple spaces
        $pesan = preg_replace('/\s+/', ' ', $pesan);

        return trim($pesan);
    }

    // ============================================================
    // TAHAP 1: GENERATE SQL
    // ============================================================

    private function generateSql(string $pesan, ?string $previousSql = null, array $kbContext = []): ?string
    {
        $schema = $this->getSchema();
        $system = $this->buildSqlPrompt($schema);

        // Bangun blok KB kalau ada
        $kbBlock = '';
        if (!empty($kbContext)) {
            $kbBlock = "═══════════════════════════════════════\n"
                . "KONTEKS KNOWLEDGE BASE\n"
                . "═══════════════════════════════════════\n"
                . "Gunakan konteks ini untuk memahami istilah/definisi dalam pertanyaan user.\n"
                . "Konteks ini BUKAN data database — jangan dijadikan SELECT target.\n\n";

            foreach ($kbContext as $i => $kb) {
                $kbBlock .= "[" . ($i + 1) . "] Kata kunci: {$kb['kata_kunci']}\n";
                $kbBlock .= "    Jawaban: {$kb['jawaban']}\n\n";
            }

            $kbBlock .= "═══════════════════════════════════════\n\n";
        }

        $userMessage = $kbBlock . $pesan;

        if ($previousSql) {
            $userMessage = "═══════════════════════════════════════\n"
                . "KONTEKS SQL SEBELUMNYA\n"
                . "═══════════════════════════════════════\n"
                . "```sql\n{$previousSql}\n```\n"
                . "═══════════════════════════════════════\n\n"
                . $kbBlock
                . "Pertanyaan lanjutan user: {$pesan}\n\n"
                . "PENTING: Gunakan konteks SQL di atas untuk memahami kata 'itu', 'yang tadi', 'nya', dll.\n"
                . "Jika user tanya 'type apa itu' setelah SQL sebelumnya filter 'tahun 2024', maka generate SQL "
                . "yang juga memfilter tahun 2024.\n";
        }

        $response = $this->callOllama($system, $userMessage, temperature: 0.1, maxTokens: 500);
        if (!$response) {
            return null;
        }

        // Bersihkan: hapus markdown code block, backtick, komentar
        $sql = trim($response);
        $sql = preg_replace('/^```(?:sql)?\s*/i', '', $sql);
        $sql = preg_replace('/\s*```$/', '', $sql);
        $sql = trim($sql);

        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = trim($sql);

        if (preg_match('/^(.*?;)/s', $sql, $m)) {
            $sql = $m[1];
        }

        if (!$this->isSafeSql($sql)) {
            Log::warning('SqlTextService: unsafe SQL rejected', [
                'pesan' => $pesan,
                'sql' => mb_substr($sql, 0, 300),
                'kb_context_count' => count($kbContext),
            ]);
            return null;
        }

        return $sql;
    }
    /**
     * Prompt SQL generator — diperkuat dengan contoh kombinasi.
     */
    private function buildSqlPrompt(string $schema): string
    {
        $today = now()->format('Y-m-d');

        return <<<PROMPT
Kamu adalah SQL generator untuk database MySQL asset management (SIAM).

Tugas: ubah pertanyaan user Bahasa Indonesia menjadi SATU query SQL SELECT yang valid.

═══════════════════════════════════════
SKEMA DATABASE
═══════════════════════════════════════
{$schema}
═══════════════════════════════════════

ATURAN KETAT (WAJIB DIIKUTI):

1. Output HANYA SQL mentah. Tanpa penjelasan, tanpa markdown, tanpa ```.
2. HANYA boleh SELECT atau WITH. Dilarang INSERT/UPDATE/DELETE/DROP/ALTER.
3. Gunakan nama tabel & kolom PERSIS seperti di skema. Jangan mengarang.
4. Tanggal hari ini: {$today}. Gunakan untuk "hari ini", "bulan ini", "tahun ini".
5. "rusak"/"perbaikan" → status = 'maintenance'. "dipinjam" → 'loaned'. "tersedia"/"ready" → 'available'.
6. "hak milik" → ownership_type = 'owned'. "sewa" → 'leased'.
7. JOIN ke tabel terkait kalau perlu (users, asset_categories, locations).
8. Batasi hasil: LIMIT 50 (kecuali COUNT/agregat).
9. Untuk COUNT: SELECT COUNT(*) AS total FROM ...
10. Kalau butuh nama user: JOIN users ON users.id = assets.current_user_id.
11. Kalau pertanyaan tentang "type/tipe/model/brand/merek/spek/spesifikasi"
    TANPA menyebut "consumable/konsumable/ATK/habis pakai", maka query ke tabel `assets`.
12. Kalau pertanyaan tentang "stok/konsumable/habis pakai/ATK", baru query ke `consumables`.
13. Kalau pertanyaan tentang "hak milik/sewa/ownership" TANPA menyebut konsumable,
    maka query kolom `ownership_type` di tabel `assets`.
14. Kalau pertanyaan menggunakan kata "ini", "itu", "nya" (ambigu), tetap generate SQL yang
    paling masuk akal ke tabel `assets`, JANGAN output not_applicable.

═══════════════════════════════════════
PENTING — BEDAKAN "TYPE APA" vs "BERAPA":
═══════════════════════════════════════

- Kalau user tanya "type apa" / "tipe apa" / "model apa" / "brand apa" / "merek apa"
  → JAWAB dengan DAFTAR (SELECT DISTINCT), BUKAN COUNT.

- Kalau user tanya "berapa" / "jumlah" / "total"
  → JAWAB dengan COUNT atau SUM.

Contoh SALAH:
  User: "type apa saja"
  SQL SALAH: SELECT COUNT(*) FROM assets    ← ❌ ini untuk "berapa"

Contoh BENAR:
  User: "type apa saja"
  SQL BENAR: SELECT DISTINCT model AS tipe, brand FROM assets WHERE model IS NOT NULL ORDER BY brand, model LIMIT 50

═══════════════════════════════════════
PENTING — KOMBINASI FILTER:
═══════════════════════════════════════

Kalau pertanyaan punya LEBIH DARI SATU filter (tahun + kategori, status + kategori,
tahun + type, dll), GABUNGKAN filter dengan AND.

Contoh kombinasi:
1. Kategori + Tahun
2. Kategori + Status (ready/rusak/dll)
3. Tahun + Type/Brand/Model
4. Status + Type
5. Kategori + Tahun + Type

═══════════════════════════════════════
CONTOH LENGKAP:
═══════════════════════════════════════

Q: "berapa laptop rusak?"
A: SELECT COUNT(*) AS total FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE a.status = 'maintenance' AND c.name LIKE '%laptop%'

Q: "siapa yang pegang NB-T14-005?"
A: SELECT a.serial_number, a.hostname, u.name AS pemegang FROM assets a LEFT JOIN users u ON u.id = a.current_user_id WHERE a.serial_number LIKE '%NB-T14-005%' OR a.hostname LIKE '%NB-T14-005%' LIMIT 1

Q: "aset hak milik per kategori"
A: SELECT c.name AS kategori, COUNT(*) AS total FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE a.ownership_type = 'owned' GROUP BY c.name ORDER BY total DESC

Q: "siapa saja yang pakai mouse"
A: SELECT u.name AS pemegang, c.name AS item, SUM(ct.quantity) AS total FROM consumable_transactions ct JOIN consumables c ON c.id = ct.consumable_id JOIN users u ON u.id = ct.user_id WHERE ct.type = 'out' AND c.name LIKE '%mouse%' GROUP BY u.name, c.name LIMIT 50

Q: "ada aset berapa?"
A: SELECT COUNT(*) AS total FROM assets

Q: "berapa total aset?"
A: SELECT COUNT(*) AS total FROM assets

═══════════════════════════════════════
CONTOH — LIST DISTINCT (type/brand/model):
═══════════════════════════════════════

Q: "type apa saja?" ATAU "tipe apa saja?" ATAU "model apa saja?"
A: SELECT DISTINCT model AS tipe, brand FROM assets WHERE model IS NOT NULL AND model != '' ORDER BY brand, model LIMIT 50

Q: "brand apa saja?" ATAU "merek apa saja?"
A: SELECT DISTINCT brand FROM assets WHERE brand IS NOT NULL AND brand != '' ORDER BY brand LIMIT 50

Q: "spek apa saja?" ATAU "spesifikasi apa?"
A: SELECT serial_number, hostname, brand, model, specification FROM assets WHERE specification IS NOT NULL AND specification != '' LIMIT 20

Q: "type apa itu" ATAU "model apa itu"
A: SELECT DISTINCT model AS tipe, brand FROM assets WHERE model IS NOT NULL AND model != '' ORDER BY brand, model LIMIT 50

═══════════════════════════════════════
CONTOH — KOMBINASI (YANG PALING SERING SALAH):
═══════════════════════════════════════

Q: "laptop tahun 2024 type apa" ATAU "laptop 2024 type apa ya"
A: SELECT DISTINCT a.model AS tipe, a.brand FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE YEAR(a.purchase_date) = 2024 AND c.name LIKE '%laptop%' ORDER BY a.brand, a.model LIMIT 50

Q: "laptop tahun 2024 type apa saja" ATAU "laptop 2024 model apa"
A: SELECT a.model AS tipe, a.brand, COUNT(*) AS total FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE YEAR(a.purchase_date) = 2024 AND c.name LIKE '%laptop%' GROUP BY a.model, a.brand ORDER BY total DESC LIMIT 50

Q: "laptop yang ready apa saja" ATAU "laptop tersedia apa"
A: SELECT DISTINCT a.model AS tipe, a.brand FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE a.status = 'available' AND c.name LIKE '%laptop%' ORDER BY a.brand, a.model LIMIT 50

Q: "laptop ready type apa" ATAU "laptop available model apa"
A: SELECT DISTINCT a.model AS tipe, a.brand FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE a.status = 'available' AND c.name LIKE '%laptop%' ORDER BY a.brand, a.model LIMIT 50

Q: "berapa laptop tahun 2024"
A: SELECT COUNT(*) AS total FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE YEAR(a.purchase_date) = 2024 AND c.name LIKE '%laptop%'

Q: "aset tahun 2023 merek apa saja" ATAU "brand aset 2023"
A: SELECT brand, COUNT(*) AS total FROM assets WHERE YEAR(purchase_date) = 2023 GROUP BY brand ORDER BY total DESC LIMIT 50

Q: "laptop yang dibeli tahun 2022 ada berapa?"
A: SELECT COUNT(*) AS total FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE YEAR(a.purchase_date) = 2022 AND c.name LIKE '%laptop%'

Q: "laptop e14 gen 5"
A: SELECT serial_number, hostname, brand, model, status FROM assets WHERE model LIKE '%E14%' OR model LIKE '%Gen 5%' LIMIT 50

Q: "laptop rusak apa saja" ATAU "laptop maintenance type apa"
A: SELECT DISTINCT a.model AS tipe, a.brand FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE a.status = 'maintenance' AND c.name LIKE '%laptop%' ORDER BY a.brand, a.model LIMIT 50

Q: "laptop dipinjam apa saja"
A: SELECT DISTINCT a.model AS tipe, a.brand FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE a.status = 'loaned' AND c.name LIKE '%laptop%' ORDER BY a.brand, a.model LIMIT 50

Q: "laptop yang paling banyak" ATAU "model laptop terbanyak"
A: SELECT model AS tipe, COUNT(*) AS total FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE c.name LIKE '%laptop%' GROUP BY model ORDER BY total DESC LIMIT 10

═══════════════════════════════════════
CONTOH — STATUS & OWNERSHIP:
═══════════════════════════════════════

Q: "berapa aset tersedia?" ATAU "berapa aset ready?"
A: SELECT COUNT(*) AS total FROM assets WHERE status = 'available'

Q: "berapa aset rusak?"
A: SELECT COUNT(*) AS total FROM assets WHERE status = 'maintenance'

Q: "berapa laptop?"
A: SELECT COUNT(*) AS total FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE c.name LIKE '%laptop%'

Q: "ini sewa atau hak milik?" ATAU "milik atau sewa?"
A: SELECT DISTINCT ownership_type FROM assets WHERE status = 'available' LIMIT 10

Q: "berapa aset hak milik?" ATAU "berapa aset sewa?"
A: SELECT ownership_type, COUNT(*) AS total FROM assets GROUP BY ownership_type

Q: "berapa laptop yang hak milik?" ATAU "berapa laptop sewa?"
A: SELECT COUNT(*) AS total FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE a.ownership_type = 'owned' AND c.name LIKE '%laptop%'

═══════════════════════════════════════
CONTOH — CONSUMABLE:
═══════════════════════════════════════

Q: "berapa mouse yang habis pakai?" ATAU "berapa mouse yang dipinjam?"
A: SELECT COUNT(*) AS total FROM consumables c JOIN consumable_transactions ct ON ct.consumable_id = c.id WHERE ct.type = 'out' AND c.name LIKE '%mouse%'

Q: "berapa mouse yang tersedia?"
A: SELECT c.name, c.stock_available AS tersedia, c.unit FROM consumables c WHERE c.name LIKE '%mouse%'

Q: "stok mouse berapa" ATAU "berapa stok mouse"
A: SELECT name, stock_available AS stok, unit FROM consumables WHERE name LIKE '%mouse%'

═══════════════════════════════════════
CATATAN — KONTEKS KNOWLEDGE BASE
═══════════════════════════════════════

Kalau di atas ada blok "KONTEKS KNOWLEDGE BASE", itu adalah definisi/penjelasan
dari KB internal — BUKAN data dari database.

Gunakan KB hanya untuk MEMAHAMI istilah dalam pertanyaan user, misalnya:
- "apa itu SIAM" → SIAM = Sistem Informasi Aset Manajemen
- "apa itu IAM" → IAM = Identity and Access Management

JANGAN jadikan isi KB sebagai target SELECT. Tetap generate SQL ke tabel
database (assets, consumables, users, dll).

Kalau pertanyaan user bisa dijawab dari KB (misal "apa itu SIAM"),
output PERSIS:
SELECT NULL AS not_applicable LIMIT 1

═══════════════════════════════════════
FALLBACK:
═══════════════════════════════════════
Kalau pertanyaan TIDAK BISA dijawab dengan SQL (misal: "cara reset password", "apa itu SIS",
"siapa kamu", "resep nasi goreng"), output PERSIS:
SELECT NULL AS not_applicable LIMIT 1
PROMPT;
    }

    // ============================================================
    // TAHAP 2: EXECUTE
    // ============================================================

    private function executeSql(string $sql): ?array
    {
        $original = DB::getDefaultConnection();
        DB::setDefaultConnection('ai_readonly');

        try {
            $rows = DB::select($sql);

            // Deteksi "not_applicable" — pakai property_exists karena value-nya NULL
            if (count($rows) === 1 && property_exists($rows[0], 'not_applicable')) {
                Log::info('SqlTextService: not_applicable detected, skipping', [
                    'sql' => mb_substr($sql, 0, 200),
                ]);
                return null;
            }

            return array_map(fn($r) => (array) $r, $rows);
        } catch (\Throwable $e) {
            Log::warning('SqlTextService: query failed', [
                'sql' => $sql,
                'error' => $e->getMessage(),
            ]);
            return null;
        } finally {
            DB::setDefaultConnection($original);
        }
    }

    // ============================================================
    // TAHAP 3: FORMAT HASIL
    // ============================================================

    private function formatAnswer(string $pesan, string $sql, array $rows): string
    {
        // Kalau 0 baris, kembalikan pesan langsung (hemat 1x call ke LLM)
        if (empty($rows)) {
            return "Tidak ada data yang cocok untuk pertanyaan itu.";
        }

        // ============================================================
        // Fast path: 1 baris 1 kolom
        // ============================================================
        if (count($rows) === 1 && count($rows[0]) === 1) {
            $key = array_key_first($rows[0]);
            $val = reset($rows[0]);

            // Kalau kolomnya "total" dan angkanya → format natural
            if (in_array(strtolower($key), ['total', 'jumlah', 'count'], true) && is_numeric($val)) {
                $lower = strtolower($pesan);

                // Deteksi jenis pertanyaan
                if (preg_match('/\b(laptop|pc|komputer|printer|monitor|server)\b/i', $lower)) {
                    $kategori = '';
                    if (preg_match('/\b(laptop)\b/i', $lower))
                        $kategori = 'laptop';
                    elseif (preg_match('/\b(printer)\b/i', $lower))
                        $kategori = 'printer';
                    elseif (preg_match('/\b(monitor)\b/i', $lower))
                        $kategori = 'monitor';
                    elseif (preg_match('/\b(pc|komputer)\b/i', $lower))
                        $kategori = 'PC/komputer';
                    elseif (preg_match('/\b(server)\b/i', $lower))
                        $kategori = 'server';

                    return "Ada **{$val}** {$kategori} yang cocok dengan pertanyaan itu.";
                }

                return "Ada **{$val}** data yang cocok dengan pertanyaan itu.";
            }

            // Kalau kolomnya "tipe" atau "brand" → format list
            if (in_array(strtolower($key), ['tipe', 'type', 'model', 'brand', 'merek'], true)) {
                return "**{$key}**: {$val}";
            }
        }

        // ============================================================
        // Untuk hasil banyak → kirim ke LLM untuk format natural
        // ============================================================
        $dataJson = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if (strlen($dataJson) > 3000) {
            $dataJson = mb_substr($dataJson, 0, 3000) . "\n... (dipotong)";
        }

        $system = "Kamu adalah asisten SIAM. Ubah data berikut menjadi jawaban natural dalam Bahasa Indonesia. "
            . "SINGKAT (maks 5 baris). Pakai emoji seperlunya. "
            . "JANGAN mengarang data di luar yang diberikan. "
            . "Kalau user tanya 'type apa' atau 'brand apa', tampilkan DAFTAR-nya. "
            . "Kalau user tanya 'berapa', tampilkan JUMLAH-nya.";

        $user = "Pertanyaan user: {$pesan}\n\nData dari database:\n{$dataJson}\n\nJawab:";

        $formatted = $this->callOllama($system, $user, temperature: 0.3, maxTokens: 300);

        return $formatted ?: $this->fallbackFormat($rows);
    }

    private function fallbackFormat(array $rows): string
    {
        $out = "Hasil:\n";
        foreach (array_slice($rows, 0, 10) as $row) {
            $parts = [];
            foreach ($row as $k => $v) {
                $parts[] = "{$k}: {$v}";
            }
            $out .= "• " . implode(' | ', $parts) . "\n";
        }
        if (count($rows) > 10) {
            $out .= "... dan " . (count($rows) - 10) . " baris lainnya.";
        }
        return $out;
    }

    // ============================================================
    // HELPERS
    // ============================================================

    private function getSchema(): string
    {
        // Cache 6 jam — schema jarang berubah
        return Cache::remember('siam_schema_v2', 21600, function () {
            $excludedTables = [
                'migrations',
                'failed_jobs',
                'personal_access_tokens',
                'sessions',
                'cache',
                'cache_locks',
                'jobs',
                'job_batches',
                'password_reset_tokens',
                'settings',
            ];

            $sensitiveColumns = [
                'password',
                'remember_token',
                'api_token',
                'token',
                'secret',
                'api_key',
                'private_key',
            ];

            $out = "";
            $original = DB::getDefaultConnection();
            DB::setDefaultConnection('ai_readonly');

            try {
                $allTables = DB::select('SHOW TABLES');
                $dbName = DB::connection('ai_readonly')->getDatabaseName();
                $key = 'Tables_in_' . $dbName;

                foreach ($allTables as $t) {
                    $tableName = $t->$key;

                    if (in_array($tableName, $excludedTables, true)) {
                        continue;
                    }

                    try {
                        $columns = DB::select("SHOW COLUMNS FROM `{$tableName}`");
                        if (empty($columns)) {
                            continue;
                        }

                        $out .= "TABLE {$tableName}:\n";
                        foreach ($columns as $c) {
                            if (in_array(strtolower($c->Field), $sensitiveColumns, true)) {
                                continue;
                            }

                            $type = $c->Type;
                            $nullable = $c->Null === 'YES' ? ' (nullable)' : '';
                            $out .= "  - {$c->Field} ({$type}){$nullable}\n";
                        }
                        $out .= "\n";
                    } catch (\Throwable $e) {
                        Log::debug("SqlTextService: skip table {$tableName}", [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } finally {
                DB::setDefaultConnection($original);
            }

            return $out ?: "(schema kosong)";
        });
    }

    private function isSafeSql(string $sql): bool
    {
        $sql = trim($sql);
        if ($sql === '') {
            return false;
        }

        // Hanya SELECT / WITH di awal
        if (!preg_match('/^\s*(SELECT|WITH)\b/i', $sql)) {
            return false;
        }

        // Blacklist keyword berbahaya
        $blacklist = [
            '/\b(INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|CREATE|REPLACE|GRANT|REVOKE)\b/i',
            '/\bINTO\s+OUTFILE\b/i',
            '/\bLOAD_FILE\b/i',
            '/;\s*\S+/', // multiple statement
        ];
        foreach ($blacklist as $pattern) {
            if (preg_match($pattern, $sql)) {
                return false;
            }
        }

        return true;
    }

    private function callOllama(string $system, string $user, float $temperature, int $maxTokens): ?string
    {
        try {
            $response = Http::timeout($this->timeout)
                ->connectTimeout(3)
                ->post("{$this->url}/api/chat", [
                    'model' => $this->model,
                    'stream' => false,
                    'options' => [
                        'temperature' => $temperature,
                        'num_predict' => $maxTokens,
                    ],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('SqlTextService: HTTP fail', ['status' => $response->status()]);
                return null;
            }

            $content = trim($response->json('message.content', ''));
            return $content !== '' ? $content : null;
        } catch (\Throwable $e) {
            Log::error('SqlTextService: ' . $e->getMessage());
            return null;
        }
    }
}
