<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pending_agents', function (Blueprint $table) {
            $table->id();
            $table->string('hostname', 100);
            $table->string('serial_number', 100)->nullable();
            $table->string('mac_address', 17)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('wifi_ssid', 100)->nullable();
            $table->string('wifi_bssid', 17)->nullable();
            $table->string('logged_user', 100)->nullable();
            $table->integer('attempt_count')->default(1);
            $table->timestamp('attempted_at');

            // ==== Approval Flow ====
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejected_reason')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('asset_id')
                ->nullable()
                ->constrained('assets')
                ->nullOnDelete();
            $table->timestamps();

            $table->index('hostname');
            $table->index('serial_number');
            $table->index('attempted_at');
            $table->index('approved_at');
            $table->index('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_agents');
    }
};
