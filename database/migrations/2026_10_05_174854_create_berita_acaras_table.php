<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('berita_acaras', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_ba')->unique();
            $table->enum('jenis', ['serah_terima', 'pengembalian']);

            // Relasi
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('asset_loan_id')->nullable()->constrained()->nullOnDelete();

            // PIHAK PERTAMA
            $table->foreignId('pihak_pertama_id')->constrained('users');
            $table->string('pihak_pertama_jabatan')->nullable();
            $table->string('pihak_pertama_nip')->nullable();

            // PIHAK KEDUA
            $table->foreignId('pihak_kedua_id')->constrained('users');
            $table->string('pihak_kedua_jabatan')->nullable();
            $table->string('pihak_kedua_nip')->nullable();

            // 🆕 SNAPSHOT ASET (generic, bukan hardcoded laptop)
            $table->string('kategori_aset');        // "LAPTOP", "PRINTER", "MONITOR", dll
            $table->string('merk')->nullable();     // "LENOVO", "EPSON", dll
            $table->string('model')->nullable();    // "THINKPAD E14 GEN 6", dll
            $table->string('serial_number')->nullable();
            $table->string('hostname')->nullable(); // nullable, karena printer ga ada hostname
            $table->json('detail_tambahan')->nullable(); // 🆕 field fleksibel: {"lisensi_office":"Office 365","ip":"10.0.0.1"}
            $table->integer('jumlah')->default(1);

            // Kondisi & catatan
            $table->integer('condition_percent')->nullable();
            $table->text('notes')->nullable();

            // Tanggal & lokasi
            $table->date('tanggal_ba');
            $table->string('tempat_ba')->nullable();

            // PDF
            $table->string('pdf_path')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita_acaras');
    }
};
