<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('message_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('reporter_id')->constrained('chat_users')->cascadeOnDelete();
            $table->foreignUuid('reported_user_id')->nullable()->constrained('chat_users')->nullOnDelete();
            $table->string('reason'); // spam, harassment, inappropriate, other
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, resolved, dismissed
            $table->timestamp('resolved_at')->nullable();
            $table->foreignUuid('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
            $table->index(['reporter_id', 'message_id']); // For duplicate check
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
