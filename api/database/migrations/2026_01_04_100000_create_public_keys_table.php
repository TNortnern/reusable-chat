<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('key')->unique(); // pk_xxxxx format
            $table->string('name');
            $table->json('allowed_origins')->nullable(); // CORS whitelist, null = allow all
            $table->json('settings')->nullable(); // Widget settings (colors, position, etc)
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['key', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_keys');
    }
};
