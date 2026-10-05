<?php

namespace App\Services\Ai;

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

    /**
     * Entry point. Return [answer, 'database', context] atau null kalau gagal.
     */
    public function tryAnswer(string $pesan): ?array
    {
        // 1. Generate SQL
        $sql = $this->generateSql($pesan);
        if (!$sql) {
            return null;
        }

        // 2. Execute
        $rows = $this->executeSql($sql);
        if ($rows === null) {
            return null;
        }

        // 3. Format hasil → jawaban natural
        $answer = $this->formatAnswer($pesan, $sql, $rows);

        return [
            $answer,
            'database',
            [
                'type' => 'sql_text',
                'sql' => $sql,
                'row_count' => count($rows),
                'time' => now()->toDateTimeString(),
            ],
        ];
    }

    // ============================================================
    // TAHAP 1: GENERATE SQL
    // ============================================================
    private function generateSql(string $pesan): ?string
    {
        $schema = $this->getSchema();
        $system = $this->buildSqlPrompt($schema);

        $response = $this->callOllama($system, $pesan, temperature: 0.1, maxTokens: 400);
        if (!$response) {
            return null;
        }

        // Bersihkan: hapus markdown code block, backtick, komentar
        $sql = trim($response);
        $sql = preg_replace('/^```(?:sql)?\s*/i', '', $sql);
        $sql = preg_replace('/\s*```$/', '', $sql);
        $sql = trim($sql);

        // Safety: hanya boleh SELECT / WITH
        if (!$this->isSafeSql($sql)) {
            Log::warning('SqlTextService: unsafe SQL rejected', [
                'pesan' => $pesan,
                'sql' => mb_substr($sql, 0, 200),
            ]);
            return null;
        }

        return $sql;
    }

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

ATURAN KETAT:
1. Output HANYA SQL mentah. Tanpa penjelasan, tanpa markdown, tanpa ```.
2. HANYA boleh SELECT atau WITH. Dilarang INSERT/UPDATE/DELETE/DROP/ALTER.
3. Gunakan nama tabel & kolom PERSIS seperti di skema. Jangan mengarang.
4. Tanggal hari ini: {$today}. Gunakan untuk "hari ini", "bulan ini", "tahun ini".
5. "rusak"/"perbaikan" → status = 'maintenance'. "dipinjam" → 'loaned'. "tersedia" → 'available'.
6. "hak milik" → ownership_type = 'owned'. "sewa" → 'leased'.
7. JOIN ke tabel terkait kalau perlu (users, asset_categories, locations).
8. Batasi hasil: LIMIT 50 (kecuali COUNT/agregat).
9. Untuk COUNT: SELECT COUNT(*) AS total FROM ...
10. Kalau butuh nama user: JOIN users ON users.id = assets.current_user_id.

CONTOH:
Q: "berapa laptop rusak?"
A: SELECT COUNT(*) AS total FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE a.status = 'maintenance' AND c.name LIKE '%laptop%'

Q: "siapa yang pegang NB-T14-005?"
A: SELECT a.serial_number, a.hostname, u.name AS pemegang FROM assets a LEFT JOIN users u ON u.id = a.current_user_id WHERE a.serial_number LIKE '%NB-T14-005%' OR a.hostname LIKE '%NB-T14-005%' LIMIT 1

Q: "aset hak milik per kategori"
A: SELECT c.name AS kategori, COUNT(*) AS total FROM assets a JOIN asset_categories c ON c.id = a.category_id WHERE a.ownership_type = 'owned' GROUP BY c.name ORDER BY total DESC

Q: "siapa saja yang pakai mouse"
A: SELECT u.name AS pemegang, c.name AS item, SUM(ct.quantity) AS total FROM consumable_transactions ct JOIN consumables c ON c.id = ct.consumable_id JOIN users u ON u.id = ct.user_id WHERE ct.type = 'out' AND c.name LIKE '%mouse%' GROUP BY u.name, c.name LIMIT 50

Kalau pertanyaan TIDAK BISA dijawab dengan SQL (misal: "cara reset password", "apa itu SIS"), output: SELECT NULL AS not_applicable LIMIT 1
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

        // Kalau cuma 1 baris 1 kolom angka → langsung format
        if (count($rows) === 1 && count($rows[0]) === 1) {
            $key = array_key_first($rows[0]);
            $val = reset($rows[0]);
            if ($key === 'total' && is_numeric($val)) {
                return "Ada **{$val}** data yang cocok.";
            }
        }

        // Untuk hasil besar, kirim ke LLM
        $dataJson = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        // Batasi biar prompt tidak terlalu panjang
        if (strlen($dataJson) > 3000) {
            $dataJson = mb_substr($dataJson, 0, 3000) . "\n... (dipotong)";
        }

        $system = "Kamu adalah asisten SIAM. Ubah data berikut menjadi jawaban natural dalam Bahasa Indonesia. "
            . "SINGKAT (maks 5 baris). Pakai emoji seperlunya. Jangan mengarang data di luar yang diberikan.";
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
        return \Illuminate\Support\Facades\Cache::remember('siam_schema_v2', 21600, function () {
            // Tabel sistem yang TIDAK perlu dibaca chatbot
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

            // Kolom sensitif yang TIDAK boleh muncul di schema (keamanan)
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
                // Ambil SEMUA tabel dari database
                $allTables = DB::select('SHOW TABLES');
                $dbName = DB::connection('ai_readonly')->getDatabaseName();
                $key = 'Tables_in_' . $dbName;

                foreach ($allTables as $t) {
                    $tableName = $t->$key;

                    // Skip tabel sistem
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
                            // Skip kolom sensitif
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
        if ($sql === '')
            return false;

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

        // Hapus trailing ;
        $sql = rtrim($sql, "; \t\n\r\0\x0B");

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
