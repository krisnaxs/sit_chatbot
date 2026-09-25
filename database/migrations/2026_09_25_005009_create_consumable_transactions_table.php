<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consumable_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consumable_id')->constrained('consumables')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();  // peminta/penerima

            $table->enum('type', ['in', 'out', 'return']);   // masuk / keluar / kembali
            $table->integer('quantity')->default(1);

            $table->dateTime('transaction_date');

            $table->foreignId('requested_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->foreignId('location_id')->nullable()
                ->constrained('locations')->nullOnDelete();

            $table->foreignId('asset_id')->nullable()
                ->constrained('assets')->nullOnDelete();  // kalau dipasang ke laptop SN xxx

            $table->string('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['consumable_id', 'type']);
            $table->index(['user_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumable_transactions');
    }
};
