<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // Laptop, PC, Printer, Mouse
            $table->string('code')->unique();       // LPT, PC, PRN, MSE
            $table->boolean('is_consumable')->default(false); // true = mouse, keyboard
            $table->boolean('is_agent_monitored')->default(false); // true = laptop, pc, server
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_agent_monitored');
        });

        // Auto-set true untuk kategori laptop/pc/server
        DB::table('asset_categories')
            ->where(function ($q) {
                $q->where('name', 'like', '%laptop%')
                    ->orWhere('name', 'like', '%notebook%')
                    ->orWhere('name', 'like', '%pc%')
                    ->orWhere('name', 'like', '%komputer%')
                    ->orWhere('name', 'like', '%desktop%')
                    ->orWhere('name', 'like', '%workstation%')
                    ->orWhere('name', 'like', '%server%');
            })
            ->update(['is_agent_monitored' => true]);
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_categories');
    }
};
