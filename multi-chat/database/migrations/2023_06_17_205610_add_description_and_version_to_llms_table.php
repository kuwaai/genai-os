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
        Schema::table('llms', function (Blueprint $table) {
			$table->longText('description')->nullable();
			$table->string('version')->default("1.0.0");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
