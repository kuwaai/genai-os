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
            Schema::table('chats', function (Blueprint $table) {
                // Drop foreign key constraint and rename column
                $table->dropForeign(['llm_id']);
                $table->renameColumn('llm_id', 'bot_id');
            });
    
            // Retrieve the corresponding bot_id for each existing llm_id
            foreach (DB::table('chats')->get() as $chat) {
                $botId = DB::table('bots')->where('model_id', $chat->bot_id)->value('id');
                DB::table('chats')->where('id', $chat->id)->update(['bot_id' => $botId]);
            }
            Schema::table('chats', function (Blueprint $table) {
                $table->foreign('bot_id')->references('id')->on('bots')->onDelete('cascade')->onUpdate('cascade');
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
        try {
            DB::beginTransaction(); // Start a database transaction
            Schema::table('chats', function (Blueprint $table) {
                // Drop foreign key constraint and rename column back to llm_id
                $table->dropForeign(['bot_id']);
                $table->renameColumn('bot_id', 'llm_id');
            });
    
            // Retrieve the corresponding llm_id for each existing bot_id
            foreach (DB::table('chats')->get() as $chat) {
                $llmId = DB::table('bots')->where('id', $chat->llm_id)->value('model_id');
                DB::table('chats')->where('id', $chat->id)->update(['llm_id' => $llmId]);
            }
            Schema::table('chats', function (Blueprint $table) {
                $table->foreign('llm_id')->references('id')->on('llms')->onDelete('cascade')->onUpdate('cascade');
            });
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of an exception
            throw $e; // Re-throw the exception to halt the migration
        }
    }
};
