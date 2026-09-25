<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_ownerships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();

            $table->enum('ownership_type', ['owned', 'leased']);

            // Sewa
            $table->string('contract_number')->nullable();
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();       // tanggal expire sewa
            $table->decimal('monthly_cost', 15, 2)->nullable();

            // Beli
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->string('invoice_number')->nullable();

            $table->string('pic_vendor')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('contract_end');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_ownerships');
    }
};
