<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('building')->nullable();   // Gedung A
            $table->string('floor')->nullable();      // Lt. 2
            $table->string('room')->nullable();       // Ruang IT
            $table->string('division')->nullable();   // Divisi yang menempati
            $table->string('full_name')->nullable();  // "Gedung A - Lt.2 - Ruang IT"
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['building', 'floor', 'room']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
