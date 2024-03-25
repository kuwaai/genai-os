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
        DB::table('histories')
            ->whereIn('chat_id', function ($query) {
                $query
                    ->select('id')
                    ->from('chats')
                    ->whereNotNull('deleted_at');
            })
            ->update(['deleted_at' => DB::raw('(SELECT deleted_at FROM chats WHERE histories.chat_id = chats.id)')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
