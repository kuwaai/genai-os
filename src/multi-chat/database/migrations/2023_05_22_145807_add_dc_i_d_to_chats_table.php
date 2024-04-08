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
        try {
            Schema::table('chats', function (Blueprint $table) {
                $table->foreignId('dcID')->nullable()->references('id')->on('duelchat')->onDelete('cascade')->onUpdate('cascade');
            });

            DB::commit();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            Schema::table('chats', function (Blueprint $table) {
                $table->foreignId('`dcID`')->nullable()->references('id')->on('duelchat')->onDelete('cascade')->onUpdate('cascade');
            });

            DB::commit();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
