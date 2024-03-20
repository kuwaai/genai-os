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
			$table->integer('limit_per_day')->change()->default(100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
