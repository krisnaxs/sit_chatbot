<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            $table->dateTime('assigned_at');         // kapan diserahkan
            $table->dateTime('returned_at')->nullable(); // NULL = masih dipegang

            $table->foreignId('assigned_by')->nullable()
                ->constrained('users')->nullOnDelete();  // IT yang serah terima
            $table->foreignId('received_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->unsignedTinyInteger('condition_on_assign')->nullable(); // 0-100
            $table->unsignedTinyInteger('condition_on_return')->nullable();

            $table->string('handover_doc_path')->nullable(); // BAST
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['asset_id', 'returned_at']);
            $table->index(['user_id', 'returned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
