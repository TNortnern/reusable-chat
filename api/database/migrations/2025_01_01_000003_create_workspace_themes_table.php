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
        Schema::create('workspace_themes', function (Blueprint $table) {
            $table->uuid('workspace_id')->primary();
            $table->string('preset', 20)->default('professional');
            $table->string('primary_color', 7)->nullable();
            $table->string('background_color', 7)->nullable();
            $table->string('font_family', 100)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->string('position', 20)->default('bottom-right');
            $table->text('custom_css')->nullable();
            $table->boolean('dark_mode_enabled')->default(true);
            $table->timestamps();
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_themes');
    }
};
