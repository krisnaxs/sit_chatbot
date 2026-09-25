<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consumables', function (Blueprint $table) {
            $table->id();

            $table->string('name');                    // Mouse Logitech M170
            $table->foreignId('category_id')->nullable()
                ->constrained('asset_categories')->nullOnDelete();

            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('unit')->default('pcs');    // pcs, box, roll

            $table->integer('stock_total')->default(0);
            $table->integer('stock_available')->default(0);
            $table->integer('stock_minimum')->default(0); // alert restock

            $table->decimal('last_price', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumables');
    }
};
