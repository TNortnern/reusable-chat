<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('event_type'); // "missed_message_1on1", "new_participant", etc
            $table->string('subject_template'); // "You have {{unread_count}} unread messages"
            $table->text('body_html');
            $table->text('body_text');
            $table->json('variables'); // ["unread_count", "sender_name", "conversation_name"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['workspace_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
