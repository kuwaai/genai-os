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
        Schema::table('chats', function (Blueprint $table) {
			$table->integer('channel')->default(0);
        });
        Schema::table('llms', function (Blueprint $table) {
            $table->dropColumn('limit_per_day');
            $table->longText('config')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
