<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();

            // Relasi utama
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();

            // Hostname snapshot — urutan di sini = urutan kolom di tabel
            $table->string('hostname', 100)->nullable();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            // Tanggal serah terima
            $table->dateTime('assigned_at');                 // kapan diserahkan
            $table->dateTime('returned_at')->nullable();     // NULL = masih dipegang

            // Petugas yang serah terima
            $table->foreignId('assigned_by')->nullable()
                ->constrained('users')->nullOnDelete();      // IT yang serah
            $table->foreignId('received_by')->nullable()
                ->constrained('users')->nullOnDelete();      // User yang terima

            // Kondisi aset
            $table->unsignedTinyInteger('condition_on_assign')->nullable();  // 0-100
            $table->unsignedTinyInteger('condition_on_return')->nullable();  // 0-100

            // Dokumen & catatan
            $table->string('handover_doc_path')->nullable();  // BAST
            $table->text('notes')->nullable();

            $table->timestamps();

            // Index untuk performa query
            $table->index(['asset_id', 'returned_at']);
            $table->index(['user_id', 'returned_at']);
            $table->index('hostname');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
