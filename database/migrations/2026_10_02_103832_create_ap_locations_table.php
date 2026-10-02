<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ap_locations', function (Blueprint $table) {
            $table->id();
            $table->string('bssid', 17)->unique();
            $table->string('ssid', 100)->nullable();
            $table->foreignId('location_id')
                ->constrained('locations')
                ->cascadeOnDelete();
            $table->string('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('location_id');
            $table->index('ssid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ap_locations');
    }
};
