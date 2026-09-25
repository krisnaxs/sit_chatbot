<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->string('brand', 100);          // Lenovo, HP, Epson
            $table->string('model', 150);          // ThinkPad T14 Gen 4, dll
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index & unique
            $table->unique(['brand', 'model']);    // tidak boleh brand+model sama
            $table->index('brand');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_types');
    }
};
