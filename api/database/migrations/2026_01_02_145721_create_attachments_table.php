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
        Schema::table('attachments', function (Blueprint $table) {
            // Add new columns
            $table->foreignUuid('workspace_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('conversation_id')->nullable()->after('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('chat_user_id')->nullable()->after('message_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable()->after('chat_user_id');
            $table->string('type')->nullable()->after('name');
            $table->string('path')->nullable()->after('type');
            $table->unsignedBigInteger('size')->nullable()->after('path');
        });

        // Make message_id nullable (was required before)
        Schema::table('attachments', function (Blueprint $table) {
            $table->uuid('message_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign(['workspace_id']);
            $table->dropForeign(['conversation_id']);
            $table->dropForeign(['chat_user_id']);
            $table->dropColumn(['workspace_id', 'conversation_id', 'chat_user_id', 'name', 'type', 'path', 'size']);
        });
    }
};
