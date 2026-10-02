<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->boolean('is_agent_monitored')
                ->default(false)
                ->after('is_consumable');
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
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropColumn('is_agent_monitored');
        });
    }
};
