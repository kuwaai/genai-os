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
            $table->bigInteger('order')->default(1000);
            $table->boolean('enabled')->default(True);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('llms', function (Blueprint $table) {
            //
        });
    }
};
