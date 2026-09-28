<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('learned_faqs', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('question_normalized');
            $table->text('answer');
            $table->string('source', 20)->default('ai');   // 'ai', 'admin', 'auto'
            $table->integer('frequency')->default(1);
            $table->decimal('confidence', 3, 2)->default(0.3);
            $table->boolean('is_approved')->default(false)->index();
            $table->json('related_keywords')->nullable();
            $table->string('category', 50)->nullable()->index();
            $table->foreignId('approved_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['is_approved', 'frequency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learned_faqs');
    }
};
