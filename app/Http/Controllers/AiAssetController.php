<?php

namespace App\Http\Controllers;

use App\Services\Ai\OllamaIntentParser;
use App\Services\Ai\IntentExecutor;
use App\Services\Ai\OllamaResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiAssetController extends Controller
{
    public function __construct(
        protected OllamaIntentParser $parser,
        protected IntentExecutor $executor,
        protected OllamaResponseFormatter $formatter,
    ) {
    }

    public function ask(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);
        $pesan = trim($request->input('message'));

        // 🛡️ Guard 1: tolak intent tulis
        if ($this->isWriteIntent($pesan)) {
            return response()->json([
                'reply' => '🔒 Maaf, saya hanya bisa **membaca** data. '
                    . 'Untuk mengubah data, silakan gunakan menu **Aset Management** di SIAM.',
                'source' => 'readonly_guard',
            ]);
        }

        // 🧠 Step 1: Ollama pahami pertanyaan
        $intent = $this->parser->parse($pesan);

        if (!$intent) {
            // Ollama gagal parse → fallback ke query service langsung
            $direct = app(\App\Services\Query\QueryRouter::class)->tryAnswer($pesan);
            if ($direct) {
                return response()->json([
                    'reply' => $direct[0],
                    'source' => 'database_direct',
                ]);
            }

            return response()->json([
                'reply' => 'Maaf, saya belum bisa memahami pertanyaan itu. '
                    . 'Coba tanya dengan cara lain atau hubungi IT Support.',
                'source' => 'fallback',
            ]);
        }

        // ⚙️ Step 2: Laravel jalankan query
        $result = $this->executor->execute($intent);

        if (!$result) {
            return response()->json([
                'reply' => 'Maaf, saya tidak menemukan data untuk pertanyaan itu.',
                'source' => 'no_match',
                'intent' => $intent,
            ]);
        }

        // 💬 Step 3: Susun jawaban (opsional lewat Ollama untuk yang singkat)
        $reply = $this->formatter->format($pesan, $result['answer']);

        return response()->json([
            'reply' => $reply,
            'source' => 'database_ai',
            'intent' => $intent,
        ]);
    }

    /**
     * 🛡️ Deteksi intent tulis (hapus, ubah, tambah).
     */
    protected function isWriteIntent(string $pesan): bool
    {
        $lower = Str::lower($pesan);
        return (bool) preg_match(
            '/\b(hapus|delete|ubah|update|edit|ganti|tambah|create|insert|'
            . 'pindah|move|set|reset|hilangkan|buang|remove|drop|truncate)\b/i',
            $lower
        );
    }
}
