<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Chatroom;
use App\Models\Chats;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::beginTransaction(); // Start a database transaction
            $chats = Chats::withTrashed()->whereNull("roomID")->get();
            foreach ($chats as $chat){
                $room = new Chatroom();
                $room->fill(["name"=> $chat->name, "user_id"=>$chat->user_id]);
                if ($chat->deleted_at !== null) $room->deleted_at = $chat->deleted_at;
                $room->save();
                $chat->name = "Duel Chat";
                $chat->roomID = $room->id;
                $chat->save();
            }
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
