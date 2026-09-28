<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_contexts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->index();
            $table->string('type', 50)->index();
            $table->json('data');
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('label')->nullable();          // deskripsi singkat context
            $table->integer('hit_count')->default(0);     // berapa kali context dipakai

            $table->timestamps();
            $table->index(['session_id', 'type']);
            $table->index(['session_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_contexts');
    }
};
