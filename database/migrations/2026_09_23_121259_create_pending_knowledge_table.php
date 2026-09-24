<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pending_knowledge', function (Blueprint $table) {
            $table->id();
            $table->text('pesan_user');       // pertanyaan asli user
            $table->text('jawaban_ai');       // jawaban dari Ollama
            $table->integer('frequency')->default(1);  // berapa kali ditanya
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_knowledge');
    }
};
