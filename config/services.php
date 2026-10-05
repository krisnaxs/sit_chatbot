<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ollama (Local AI Chatbot)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk menghubungkan Laravel dengan Ollama yang berjalan
    | secara lokal. Pastikan Ollama sudah dijalankan (`ollama serve`) dan
    | model sudah di-pull (`ollama pull qwen2.5-coder:7b`).
    |
    */

    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),

        // Model default (untuk QA umum & intent parser)
        'model' => env('OLLAMA_MODEL', 'qwen2.5-coder:7b'),

        // Model khusus SQL generation (bisa beda kalau mau pakai model lain,
        // misal 'sqlcoder:7b' atau 'duckdb-nsql:7b')
        'sql_model' => env('OLLAMA_SQL_MODEL', env('OLLAMA_MODEL', 'qwen2.5-coder:7b')),

        // Timeout untuk chat QA umum (detik)
        'timeout' => env('OLLAMA_TIMEOUT', 60),

        // Timeout untuk intent parsing (harus cepat, karena cache-able)
        'intent_timeout' => env('OLLAMA_INTENT_TIMEOUT', 8),

        // Timeout untuk SQL generation + formatting (lebih lama, 2x call)
        'sql_timeout' => env('OLLAMA_SQL_TIMEOUT', 30),

        // Fitur lama: format jawaban via LLM (opsional, sudah di-handle
        // internal oleh SqlTextService)
        'use_formatter' => env('OLLAMA_USE_FORMATTER', false),
    ],

];
