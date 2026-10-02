<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('agent_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('name', 100);
            $table->foreignId('asset_id')
                ->nullable()
                ->constrained('assets')
                ->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->string('device_serial', 100)->nullable();
            $table->string('device_hostname', 100)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('last_used_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_tokens');
    }
};
