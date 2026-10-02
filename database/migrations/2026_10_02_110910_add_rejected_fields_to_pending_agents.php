<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pending_agents', function (Blueprint $table) {
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->string('rejected_reason')->nullable()->after('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('pending_agents', function (Blueprint $table) {
            $table->dropColumn(['rejected_at', 'rejected_reason']);
        });
    }
};
