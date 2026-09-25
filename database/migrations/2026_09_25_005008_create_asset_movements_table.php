<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();

            $table->string('movable_type')->nullable();  // App\Models\User / Location
            $table->unsignedBigInteger('movable_id')->nullable();

            $table->foreignId('from_location_id')->nullable()
                ->constrained('locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()
                ->constrained('locations')->nullOnDelete();

            $table->enum('type', [
                'assign',
                'return',
                'transfer',
                'loan',
                'maintenance'
            ]);

            $table->string('reference_table')->nullable(); // asset_assignments
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->dateTime('moved_at');
            $table->foreignId('moved_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'moved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
    }
};
