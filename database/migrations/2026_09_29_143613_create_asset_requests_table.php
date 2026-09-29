<?php
// database/migrations/xxxx_create_asset_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // REQ-20250101-0001

            // Jenis pengajuan
            $table->enum('type', ['assignment', 'loan', 'consumable']);

            // Pemohon
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Target pengajuan
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('consumable_id')->nullable()->constrained('consumables')->nullOnDelete();

            // Detail
            $table->integer('quantity')->default(1); // untuk consumable
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->text('purpose'); // alasan pengajuan
            $table->date('needed_date')->nullable();
            $table->date('due_date')->nullable(); // untuk loan
            $table->string('hostname')->nullable(); // untuk assignment

            // Status approval
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();

            // Track hasil eksekusi
            $table->foreignId('assignment_id')->nullable()->constrained('asset_assignments')->nullOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained('asset_loans')->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('consumable_transactions')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_requests');
    }
};
