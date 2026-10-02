<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();        // AST-2025-0001
            $table->string('serial_number')->unique();     // SN 123
            $table->string('hostname')->nullable()->unique();  //  NB-IT-001 (unique)
            $table->string('brand')->nullable();           // Lenovo
            $table->string('model')->nullable();           // ThinkPad T14
            $table->foreignId('category_id')->constrained('asset_categories')->cascadeOnDelete();
            $table->json('specification')->nullable();     // {cpu, ram, storage}
            $table->string('os')->nullable();              // Windows 11 Pro
            $table->string('os_license')->nullable();      // Original / OEM
            $table->enum('ownership_type', ['owned', 'leased'])->default('owned');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->date('warranty_expire')->nullable();
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
            $table->foreignId('current_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('current_location_id')->nullable()
                ->constrained('locations')->nullOnDelete();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();

            // ==== Agent / Monitoring Fields ====
            $table->timestamp('last_seen_at')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->decimal('last_lat', 10, 7)->nullable();          // -90 s/d 90
            $table->decimal('last_lng', 10, 7)->nullable();          // -180 s/d 180
            $table->string('location_source', 20)->nullable();       // gps / wifi / ip / manual
            $table->string('last_mac', 17)->nullable();
            $table->string('last_wifi_ssid', 100)->nullable();
            $table->string('last_wifi_bssid', 17)->nullable();
            $table->string('last_logged_user', 100)->nullable();
            $table->integer('last_uptime_hours')->nullable();
            $table->decimal('last_cpu_temp', 4, 1)->nullable();
            $table->string('agent_version', 20)->nullable();
            $table->string('last_os', 200)->nullable();
            $table->string('agent_status', 20)->default('unknown');
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('ownership_type');
            $table->index(['brand', 'model']);
            $table->index('agent_status');
            $table->index('last_seen_at');
            $table->index(['last_lat', 'last_lng']);   // untuk query geo
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
