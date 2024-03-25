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
        DB::transaction(function () {
            Schema::rename('duelchat', 'chatrooms');
            Schema::table('chats', function (Blueprint $table) {
                $table->renameColumn('dcID', 'roomID');
            });
            DB::table('permissions')
                ->where('name', 'like', '%Duel%')
                ->update([
                    'name' => DB::raw("REPLACE(name, 'Duel', 'Room')"),
                ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
