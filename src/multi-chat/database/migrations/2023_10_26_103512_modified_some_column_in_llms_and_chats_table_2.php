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
        if (Schema::hasColumn('llms', 'limit_per_day')) {
            Schema::table('llms', function (Blueprint $table) {
                $table->dropColumn('limit_per_day');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
