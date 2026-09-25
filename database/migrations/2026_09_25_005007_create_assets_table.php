<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            // Identitas
            $table->string('asset_code')->unique();        // AST-2025-0001
            $table->string('serial_number')->unique();     // SN 123
            $table->string('hostname')->nullable();        // NB-IT-001
            $table->string('brand')->nullable();           // Lenovo
            $table->string('model')->nullable();           // ThinkPad T14

            // Kategori
            $table->foreignId('category_id')->constrained('asset_categories')->cascadeOnDelete();

            // Spesifikasi teknis
            $table->json('specification')->nullable();     // {cpu, ram, storage}
            $table->string('os')->nullable();              // Windows 11 Pro
            $table->string('os_license')->nullable();      // Original / OEM

            // Kepemilikan (CASE 1)
            $table->enum('ownership_type', ['owned', 'leased'])->default('owned');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->date('warranty_expire')->nullable();

            // Status & kondisi
            $table->enum('status', [
                'available',     // tersedia, belum dipakai
                'in_use',        // sedang dipakai user
                'loaned',        // sedang dipinjam
                'maintenance',   // sedang diperbaiki
                'retired',       // sudah tidak dipakai
                'lost'           // hilang
            ])->default('available');

            $table->unsignedTinyInteger('condition_percent')->nullable(); // 0-100
            $table->text('condition_notes')->nullable();

            // Denormalized current state (untuk query cepat)
            $table->foreignId('current_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('current_location_id')->nullable()
                ->constrained('locations')->nullOnDelete();

            // Media & catatan
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index untuk filter
            $table->index('status');
            $table->index('ownership_type');
            $table->index(['brand', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
