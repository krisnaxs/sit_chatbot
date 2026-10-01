<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('knowledge_attachments', function (Blueprint $table) {
            $table->id(); // id attachment sendiri (bigint unsigned, tidak masalah)

            // ✅ knowledge_id HARUS int unsigned, cocok dengan knowledge.id
            $table->unsignedInteger('knowledge_id');
            $table->foreign('knowledge_id')
                ->references('id')
                ->on('knowledge')
                ->cascadeOnDelete();

            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('file_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();

            $table->index('knowledge_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_attachments');
    }
};
