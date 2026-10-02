<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('notes');
            $table->string('last_ip', 45)->nullable()->after('last_seen_at');
            $table->string('last_mac', 17)->nullable()->after('last_ip');
            $table->string('last_wifi_ssid', 100)->nullable()->after('last_mac');
            $table->string('last_wifi_bssid', 17)->nullable()->after('last_wifi_ssid');
            $table->string('last_logged_user', 100)->nullable()->after('last_wifi_bssid');
            $table->integer('last_uptime_hours')->nullable()->after('last_logged_user');
            $table->decimal('last_cpu_temp', 4, 1)->nullable()->after('last_uptime_hours');
            $table->string('agent_version', 20)->nullable()->after('last_cpu_temp');
            $table->string('agent_status', 20)->default('unknown')->after('agent_version');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'last_seen_at',
                'last_ip',
                'last_mac',
                'last_wifi_ssid',
                'last_wifi_bssid',
                'last_logged_user',
                'last_uptime_hours',
                'last_cpu_temp',
                'agent_version',
                'agent_status',
            ]);
        });
    }
};
