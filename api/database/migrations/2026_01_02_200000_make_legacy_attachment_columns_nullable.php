<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Makes legacy attachment columns nullable so we can stop populating them.
     * The new columns (name, type, path, size) are already nullable and preferred.
     * The Attachment model has accessors that fallback to legacy columns if needed.
     *
     * After this migration runs:
     * - AttachmentController can stop populating legacy columns
     * - Existing data remains intact
     * - A future migration can remove legacy columns after data migration
     */
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('filename')->nullable()->change();
            $table->string('mime_type', 100)->nullable()->change();
            $table->integer('size_bytes')->nullable()->change();
            $table->string('url', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * WARNING: Rollback will fail if any rows have NULL values in these columns.
     * Run a data migration first to populate any NULL values before rolling back.
     */
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('filename')->nullable(false)->change();
            $table->string('mime_type', 100)->nullable(false)->change();
            $table->integer('size_bytes')->nullable(false)->change();
            $table->string('url', 500)->nullable(false)->change();
        });
    }
};
