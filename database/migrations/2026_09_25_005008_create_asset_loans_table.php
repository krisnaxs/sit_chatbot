<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_loans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->dateTime('loan_date');                    // tanggal pinjam
            $table->dateTime('due_date');                     // rencana kembali
            $table->dateTime('returned_at')->nullable();      // realisasi kembali

            $table->string('purpose')->nullable();            // keperluan

            $table->foreignId('approved_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->enum('status', [
                'pending',
                'approved',
                'borrowed',
                'returned',
                'overdue'
            ])->default('pending');

            $table->unsignedTinyInteger('condition_on_loan')->nullable();
            $table->unsignedTinyInteger('condition_on_return')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_loans');
    }
};
