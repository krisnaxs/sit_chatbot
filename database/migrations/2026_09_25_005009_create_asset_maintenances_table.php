<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();

            $table->enum('type', ['preventive', 'corrective', 'upgrade'])->default('corrective');

            $table->string('issue')->nullable();       // "Keyboard rusak"
            $table->text('action')->nullable();        // "Ganti keyboard"
            $table->string('technician')->nullable();
            $table->decimal('cost', 15, 2)->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->enum('status', [
                'open',
                'in_progress',
                'done',
                'cancelled'
            ])->default('open');

            $table->unsignedTinyInteger('condition_before')->nullable();
            $table->unsignedTinyInteger('condition_after')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
    }
};
