<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::beginTransaction(); // Start a database transaction
            Schema::table('llms', function (Blueprint $table) {
                $table->longText('config')->nullable()->change();
                $table->longText('description')->nullable()->change();
            });

            Schema::table('bots', function (Blueprint $table) {
                $table->longText('config')->nullable()->change();
                $table->longText('description')->nullable()->change();
            });
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of an exception
            throw $e; // Re-throw the exception to halt the migration
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
