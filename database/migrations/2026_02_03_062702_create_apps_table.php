<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('gambar', 500)->nullable();
            $table->string('url', 500);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('slide')->default('Index');
            $table->integer('urutan')->default(1);
            $table->timestamps();
            $table->index(['slide', 'urutan']);
            $table->index('is_active');
            // $table->unique(['slide', 'urutan']); // opsional
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apps');
    }
};
