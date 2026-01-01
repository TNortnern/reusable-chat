<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->uuid('workspace_id');
            $table->uuid('blocker_id');
            $table->uuid('blocked_id');
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['workspace_id', 'blocker_id', 'blocked_id']);
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('blocker_id')->references('id')->on('chat_users')->onDelete('cascade');
            $table->foreign('blocked_id')->references('id')->on('chat_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
