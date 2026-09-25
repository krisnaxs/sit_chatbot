<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // ============ IDENTITAS ============
            $table->string('nik')->nullable()->unique()->after('id');   // NIK pegawai
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();                        // no. HP
            $table->string('photo_path')->nullable();                   // foto profil

            // ============ ORGANISASI ============
            $table->foreignId('department_id')->nullable()
                ->constrained('departments')->nullOnDelete();
            $table->string('position')->nullable();                     // jabatan

            $table->foreignId('location_id')->nullable()
                ->constrained('locations')->nullOnDelete();           // lokasi kerja default

            // ============ ROLE & STATUS ============
            // admin   = super admin
            // support = IT support (assign aset, maintenance)
            // user    = pegawai biasa (pemakai aset)
            $table->enum('role', ['admin', 'support', 'user'])->default('user');
            $table->boolean('is_active')->default(true);

            // ============ AUTH ============
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();   // opsional: user jangan hard delete

            // ============ INDEX ============
            $table->index('role');
            $table->index('is_active');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
