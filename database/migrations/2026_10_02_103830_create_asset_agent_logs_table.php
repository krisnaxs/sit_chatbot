<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_agent_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')
                ->constrained('assets')
                ->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('mac', 17)->nullable();
            $table->string('wifi_ssid', 100)->nullable();
            $table->string('wifi_bssid', 17)->nullable();
            $table->string('hostname', 100)->nullable();
            $table->string('logged_user', 100)->nullable();
            $table->integer('uptime_hours')->nullable();
            $table->decimal('cpu_temp', 4, 1)->nullable();
            $table->timestamp('reported_at');
            $table->timestamps();

            $table->index(['asset_id', 'reported_at']);
            $table->index('reported_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_agent_logs');
    }
};
