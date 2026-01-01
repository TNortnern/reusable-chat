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
        Schema::create('workspace_settings', function (Blueprint $table) {
            $table->uuid('workspace_id')->primary();
            $table->boolean('read_receipts_enabled')->default(true);
            $table->boolean('online_status_enabled')->default(true);
            $table->boolean('typing_indicators_enabled')->default(true);
            $table->integer('file_size_limit_mb')->default(10);
            $table->integer('rate_limit_per_minute')->default(60);
            $table->string('webhook_url', 500)->nullable();
            $table->string('webhook_secret', 100)->nullable();
            $table->timestamps();
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_settings');
    }
};
