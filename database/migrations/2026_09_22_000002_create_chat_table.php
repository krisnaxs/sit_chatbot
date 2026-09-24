<?php
// database/migrations/2026_09_22_000002_create_chat_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chat', function (Blueprint $table) {
            $table->increments('id');
            $table->string('session_id', 100)->nullable()->index();
            $table->text('pesan');
            $table->text('jawaban');

            // 🆕 Sumber jawaban: 'database' (KB) atau 'ai' (Ollama)
            $table->string('sumber', 20)->nullable()->index();

            $table->timestamp('waktu')->useCurrent();

            // File attachment (dari knowledge)
            $table->string('file_path', 500)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->string('file_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat');
    }
};
