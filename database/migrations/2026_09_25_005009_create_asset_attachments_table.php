<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_attachments', function (Blueprint $table) {
            $table->id();

            // Polymorphic → bisa attach ke asset, assignment, maintenance, dll
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');

            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();   // photo, invoice, bast, document
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();

            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_attachments');
    }
};
